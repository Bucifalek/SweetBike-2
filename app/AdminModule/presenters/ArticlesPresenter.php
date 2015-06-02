<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 19:34, 31. 3. 2015
 */

namespace App\AdminModule\Presenters;

use App\AdminModule\Model\ImageAnalyse;
use App\AdminModule\Model\ImagesManager;
use App\AdminModule\Model\UsersManager;
use Nette;
use Nette\Application\UI\Form;
use App\AdminModule\Model\ArticleManager;
use Nette\Utils\Image;
use Nette\Utils\Random;
use Tracy\Debugger;

/**
 * Class NewsPresenter
 * @package App\AdminModule\Presenters
 */
class ArticlesPresenter extends BasePresenter
{

	/**
	 * @var ArticleManager
	 */
	private $articleManager;

	/**
	 * @var ImagesManager
	 */
	private $imagesManager;

	/**
	 * @var
	 */
	private $articleData;

	/**
	 * @param ArticleManager $articleManager
	 * @param ImagesManager $imagesManager
	 */
	function __construct(ArticleManager $articleManager, ImagesManager $imagesManager)
	{
		$this->articleManager = $articleManager;
		$this->imagesManager = $imagesManager;
	}

	/**
	 *
	 */
	public function renderDefault()
	{
		$this->template->articles = $this->articleManager->getAll();
	}


	/**
	 *
	 */
	public function beforeRender()
	{
		parent::beforeRender();
		if ($this->getParameter('article')) {
			try {
				$this->template->articleData = $this->articleData = $this->articleManager->get($this->getParameter('article'));
			} catch (\Exception $e) {
				$this->flashMessage('Tento článek neexistuje.', 'error');
				$this->redirect('Articles:default');
			}

			$cover = $this->imagesManager->cover($this->getParameter('article'));
			if (!$cover) {
				if (!$this->isLinkCurrent('Articles:editPhotos')) {
					$this->flashMessage("Tento článek nemá vybraný úvodní obrázek. Napravit to můžete <a href='" . $this->link('Articles:editPhotos', $this->getParameter('article')) . "'>zde</a>.", 'info');
				} else {
					$this->flashMessage('Tento článek nemá vybraný úvodní obrázek.', 'info');
				}
			}
		}


	}

	/**
	 * @return Form
	 */
	public function createComponentNewArticleForm()
	{
		$form = new Form();
		$form->addText('title')->setRequired('Musíte zadat titulek!');
		$form->addTextArea('text')->setRequired('Musíte zadat text!');

		$form->addCheckbox('scheduled');

		$days = [];
		for ($day = 1; $day <= 31; $day++) $days[$day] = $day;
		$form->addSelect('day', null, $days)->setValue(date('j'));

		$months = [];
		for ($month = 1; $month <= 12; $month++) $months[$month] = $month;
		$form->addSelect('month', null, $months)->setValue(date('n'));

		$years = [];
		for ($year = date('Y'); $year <= date('Y') + 2; $year++) $years[$year] = $year;
		$form->addSelect('year', null, $years)->setValue(date('Y'));

		$times = [];
		for ($time = 1; $time <= 23; $time++) {
			$times[$time] = ($time > 9) ? $time . ":00" : "0" . $time . ":00";
		}

		$form->addSelect('time', null, $times)->setValue(date('G') + 1);

		$form->addSubmit('add');
		$form->onSuccess[] = array($this, 'newArticleFormSuccess');

		return $form;
	}

	/**
	 * @param $form
	 */
	public function newArticleFormSuccess(Form $form)
	{
		$values = $form->getValues();

		$article = [
			'title'          => $values['title'],
			'text'           => htmlspecialchars($values['text']),
			'scheduled'      => ($values['scheduled']) ? 1 : 0,
			'scheduled_date' => ($values['scheduled']) ? mktime($values['time'], 0, 0, $values['month'], $values['day'], $values['year']) : time(),
		];

		try {
			$this->articleManager->add($article);
			$this->flashMessage("Článek přidán, nyní můžete <a href='" . $this->link('Articles:editPhotos', $this->articleManager->getLastId()) . "'>přidat fotografie</a>", 'success');
		} catch (\Exception $e) {
			$this->flashMessage($e->getMessage(), 'info');
		}
		$this->redirect('Articles:default');
	}

	/**
	 * @param $article
	 */
	public function renderEditPhotos($article)
	{

		$images = $this->imagesManager->select($article);
		$this->template->images = $images;
		$this->template->articleData = $this->articleData;

		$galleryIA = new ImageAnalyse();
		$thumbIA = new ImageAnalyse();
		$coverIA = new ImageAnalyse();

		foreach ($images as $image) {
			$galleryIA->addStorage('img/blog/gallery/' . $image['src']);
			$thumbIA->addStorage('img/blog/thumb/' . $image['src']);
			$coverIA->addStorage('img/blog/cover/' . $image['src']);
		}

		$this->template->imagesCount = count($this->template->images);

		$this->template->statusGallery = $galleryIA->resolve();
		$this->template->statusThumb = $thumbIA->resolve();
		$this->template->statusCover = $coverIA->resolve();
	}

	/**
	 * @return Form
	 */
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

	/**
	 * @param Form $form
	 * @throws Nette\Utils\UnknownImageFileException
	 */
	public function addImagesFormSucceeded(Form $form)
	{
		$this->articleData = $this->articleManager->get($this->getParameter('article'));

		$atLeast = false;
		$values = $form->getValues();
		for ($imageNum = 1; $imageNum <= 4; $imageNum++) {
			$image = $values['image_' . $imageNum];
			if ($image->isOk()) {

				$fileExtension = explode(".", $image->name);
				$fileExtension = $fileExtension[count($fileExtension) - 1];

				$newName = Nette\Utils\Strings::webalize('blog-' . $this->articleData['id']) . '-' . Random::generate(10);
				$newFileName = $newName . '.' . strtolower($fileExtension);

				$src = "img/blog/temp/" . $newFileName;

				// Upload
				$image->move($src);
				unset($image);

				// Create cover
				$orig = Image::fromFile($src);
				$orig->resize(1300, 371, Image::FILL);

				$cover = Image::fromBlank(1300, 371);
				$cover->place($orig, 0, '30%');
				$cover->save("img/blog/cover/" . $newFileName, 90);
				unset($cover);

				// Create thumb
				$orig = Image::fromFile($src);
				$orig->resize(530, 331, Image::FILL);

				$thumb = Image::fromBlank(530, 331);
				$thumb->place($orig);
				$thumb->save("img/blog/thumb/" . $newFileName, 100);
				unset($thumb);

				// Create
				$view = Image::fromFile($src);
				$view->resize(1400, null);
				$dimensions = $view->calculateSize($view->getWidth(), $view->getHeight(), 900, null);
				$view->resize($dimensions[0], $dimensions[1], Image::EXACT);
				$view->save("img/blog/gallery/" . $newFileName, 100);

				unset($view);

				// Drop temp
				unlink($src);

				$this->imagesManager->add([
					'article'  => $this->articleData['id'],
					'cover'    => 0,
					'src'      => $newFileName,
					'filesize' => filesize("img/blog/gallery/" . $newFileName),
				]);
				$atLeast = true;
			} else if ($image->name) {
				$this->flashMessage('Nahrávání obrázku <b>' . $image->name . '</b> selhalo!' . $image->error, 'error');
			}
		}
		if ($atLeast) {
			$this->flashMessage('Do galerie byly přidány obrázky.', 'success');
		}

		$this->redirect('Articles:editPhotos', $this->articleData['id']);
	}

	/**
	 * @param $id
	 */
	public function handleImageDelete($id)
	{
		@unlink('img/blog/gallery/' . $this->imagesManager->getSrc($id));
		@unlink('img/blog/thumb/' . $this->imagesManager->getSrc($id));
		@unlink('img/blog/cover/' . $this->imagesManager->getSrc($id));
		$this->imagesManager->delete($id);
		$this->flashMessage('Obrázek byl úspěšně smazán z galerie.', "success");
		$this->redirect('Articles:editPhotos', $this->getParameter('article'));
	}

	/**
	 * @param $photo
	 */
	public function handleSelectAsCover($photo)
	{
		$this->imagesManager->asCover($photo);
		$this->flashMessage('Obrázek zvolen jako úvodní pro tento článek.', 'success');
		$this->redirect('Articles:editPhotos', $this->getParameter('article'));
	}

	/**
	 * @param $article
	 */
	public function renderEdit($article)
	{
		$data = $this->articleManager->get($article);
		$this->template->scheduled = $data->scheduled;
		$this->template->article = $article;
	}

	/**
	 * @return Form
	 * @throws \Exception
	 */
	public function createComponentEditArticleForm()
	{
		$data = $this->articleManager->get($this->getParameter('article'));

		$form = new Form();
		$form->addText('title')->setRequired('Musíte zadat titulek!')->setValue($data->title);
		$form->addTextArea('text')->setRequired('Musíte zadat text!')->setValue(htmlspecialchars_decode($data->text));

		$form->addCheckbox('scheduled')->setValue($data->scheduled);

		$scheduled = [
			'day'   => date('j', $data->scheduled_date),
			'month' => date('n', $data->scheduled_date),
			'year'  => date('Y', $data->scheduled_date),
			'hour'  => date('H', $data->scheduled_date),
		];

		if (Nette\Utils\Strings::startsWith($scheduled['hour'], 0) AND strlen($scheduled['hour']) == 2) {
			$scheduled['hour'] = substr($scheduled['hour'], 1);
		}

		$days = [];
		for ($day = 1; $day <= 31; $day++) {
			$days[$day] = $day;
		}
		$form->addSelect('day', null, $days)->setDefaultValue($scheduled['day']);

		$months = [];
		for ($month = 1; $month <= 12; $month++) {
			$months[$month] = $month;
		}
		$form->addSelect('month', null, $months)->setValue($scheduled['month']);

		$years = [];
		for ($year = date('Y'); $year <= date('Y') + 2; $year++) {
			$years[$year] = $year;
		}
		$form->addSelect('year', null, $years)->setValue($scheduled['year']);

		$times = [];
		for ($time = 0; $time <= 23; $time++) {
			$times[$time] = ($time > 9) ? $time . ":00" : "0" . $time . ":00";
		}

		$form->addSelect('time', null, $times)->setValue($scheduled['hour']);

		$form->addSubmit('save');
		$form->onSuccess[] = array($this, 'saveArticleFormSuccess');

		return $form;
	}

	/**
	 * @param Form $form
	 */
	public function saveArticleFormSuccess(Form $form)
	{
		$values = $form->getValues();
		$article = [
			'title'          => $values['title'],
			'text'           => htmlspecialchars($values['text']),
			'scheduled'      => ($values['scheduled']) ? 1 : 0,
			'scheduled_date' => ($values['scheduled']) ? mktime($values['time'], 0, 0, $values['month'], $values['day'], $values['year']) : time(),
		];

		try {
			$this->articleManager->save($article, $this->getParameter('article'));
			$this->flashMessage("Článek uložen.", 'success');
		} catch (\Exception $e) {
			$this->flashMessage($e->getMessage(), 'info');
		}

		$this->redirect('Articles:default');
	}


	/**
	 * @param $article
	 */
	public function renderRemove($article)
	{
		$this->template->title = $this->articleData['title'];
		$this->template->article = $article;
	}

	/**
	 * @param $article
	 */
	public function handleRemoveArticle($article)
	{
		$this->articleManager->remove($article);

		$images = $this->imagesManager->select($article);
		foreach ($images as $image) {
			@unlink('img/blog/gallery/' . $image['src']);
			@unlink('img/blog/thumb/' . $image['src']);
			@unlink('img/blog/cover/' . $image['src']);
			$this->imagesManager->delete($image['id']);
		}

		$this->flashMessage('Článek byl smazán.', 'success');
		$this->redirect('Articles:default');
	}
}