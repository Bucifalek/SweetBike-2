<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 20:39, 31. 3. 2015
 */

namespace App\AdminModule\Presenters;

use App\AdminModule\Model\ImagesManager;
use App\AdminModule\Model;
use Nette;
use Tracy\Debugger;

class GalleryPresenter extends BasePresenter
{

	private $imagesManager;

	private $cache;

	private $path;

	function __construct(ImagesManager $imagesManager, Nette\Caching\Cache $cache)
	{
		$this->cache = $cache;
		$this->imagesManager = $imagesManager;
	}

	public function renderDefault()
	{
		$this->template->blackAndWhiteNum = $this->imagesManager->count('black-and-white');
		$this->template->graphicNum = $this->imagesManager->count('graphic');
		$this->template->colorNum = $this->imagesManager->count('color');
		$this->template->piercingNum = $this->imagesManager->count('piercing');
		$this->template->tattooNum = $this->imagesManager->count('tetovani');
	}

	protected function createComponentAddImagesForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->addProtection();
		$form->addUpload('image_1');
		$form->addUpload('image_2');
		$form->addUpload('image_3');
		$form->addUpload('image_4');
		$form->addSubmit('add');
		$form->onSuccess[] = array($this, 'addImagesFormSucceeded');

		return $form;
	}

	public function addImagesFormSucceeded($form)
	{
		$values = $form->getValues();

		for ($imageNum = 1; $imageNum <= 4; $imageNum++) {
			$image = $values['image_' . $imageNum];
			if ($image->isOk()) {
				$fileExt = explode(".", $image->name);
				$fileExt = $fileExt[count($fileExt) - 1];
				$newName = sha1(Nette\Utils\Random::generate(30)) . "." . $fileExt;

				$author = $this->presenter->getParameter('author');
				$work = $this->presenter->getParameter('work');
				$key = $this->presenter->getParameter('key', $this->presenter->getParameter('work'));

				$path = 'img/gallery/' . $author . '/';
				$path .= ($author == 'viktor') ? $key : $work;

				$image->move($path . '/' . $newName);


				$this->imagesManager->add(array(
					'author'  => $this->presenter->getParameter('author'),
					'work'    => $this->presenter->getParameter('work'),
					'cat'     => $this->presenter->getParameter('key', $this->presenter->getParameter('work')),
					'img_src' => $newName
				));
			}
		}
		$this->flashMessage('Do galerie byly přidány obrázky', 'success');
		$this->redirect('Gallery:manage', array(
			$this->presenter->getParameter('author'),
			$this->presenter->getParameter('work'),
			$this->presenter->getParameter('key', null)
		));
	}

	public function renderManage($author, $work, $key = null)
	{
		$this->cache->clean();
		$this->template->images = $this->imagesManager->select($author, $work, $key, null);
		$this->template->imagesCount = count($this->template->images);

		$imagesSize = 0;
		$path = 'img/gallery/' . $author . '/';
		$path .= $path . ($author == 'viktor') ? $key : $work . '/';
		foreach (Nette\Utils\Finder::findFiles('*.jpeg', '*.jpg')->in($path) as $file) {
			$imagesSize += $file->getSize();
		}

		if ($imagesSize <= 5242880) {
			$status = array('ok', 'Dobrý, galerie má ideální velikost.');
		} else if ($imagesSize > 5242880 AND $imagesSize <= 8388608) {
			$status = array('warning', 'Galerie je poměrně velká, může se načítat pomaleji.');
		} else {
			$status = array('danger', 'Galerie je příliš velka! Zmenšete její velikost!');
		}
		$this->template->status = $status;
		$this->template->imagesSize = $imagesSize;
		$this->template->author = ucfirst($author);
		$this->template->catTitle = ($work == "tetovani") ? "Tetování" : "Piercing";
		$this->template->key = ($key == "black-and-white") ? "- Black & White" : "- " . ucfirst($key);
		if (empty($key)) $this->template->key = false;
	}

	public function handleImageDelete($id)
	{
		@unlink($this->imagesManager->getSrc($id));
		$this->imagesManager->delete($id);
		$this->flashMessage('Obrázek byl úspěšně smazán z galerie.', "success");
		$this->redirect('Gallery:manage', array(
			$this->presenter->getParameter('author'),
			$this->presenter->getParameter('work'),
			$this->presenter->getParameter('key', null)
		)); // Dodelat routu
	}
}