<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 23:17, 13. 3. 2015
 * @copyright 2015 Jan Kotrba
 */

namespace App\AdminModule\Model;

use Nette;

/**
 * Class ImagesManager
 * @package App\AdminModule\Model
 */
class ImagesManager extends Nette\Object
{

	/**
	 * @var Nette\Database\Context
	 */
	private $database;

	/**
	 * @param Nette\Database\Context $database
	 */
	function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * @param $id
	 * @return array
	 */
	public function select($id)
	{

		return $this->database->table('article_photo')->where('article', $id)->order('id DESC')->fetchAll();
	}

	/**
	 * @param $id
	 * @return int
	 */
	public function count($id)
	{
		return $this->database->table('article_photo')->where('article', $id)->order('id DESC')->count();
	}

	/**
	 * @param $id
	 * @return mixed
	 */
	public function getSrc($id)
	{
		$image = $this->database->table('article_photo')->where('id', $id)->order('id DESC')->fetch();

		return $image['src'];
	}

	/**
	 * @param $id
	 */
	public function delete($id)
	{
		$this->database->table('article_photo')->where('id', $id)->delete();
	}

	/**
	 * @param $values
	 */
	public function add($values)
	{
		$this->database->table('article_photo')->insert($values);
	}

	/**
	 * @param $photo
	 * @return int
	 */
	public function asCover($photo)
	{
		$details = $this->database->table('article_photo')->where('id', $photo)->fetch();

		// Cover 0 to all
		$this->database->table('article_photo')->where('article', $details['article'])->update(['cover' => 0]);

		// Cover 1 to specify row
		$this->database->table('article_photo')->where('id', $photo)->update(['cover' => 1]);
	}

	public function cover($article)
	{
		$cover = $this->database->table('article_photo')->where('article', $article)->where('cover', '1')->fetch();

		return ($cover) ? $cover->src : false;
	}
}
