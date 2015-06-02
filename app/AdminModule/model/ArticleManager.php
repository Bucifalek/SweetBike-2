<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 19:55, 12. 3. 2015
 * @copyright 2015 Jan Kotrba
 */

namespace App\AdminModule\Model;

use Nette;
use Tracy\Debugger;

/**
 * Class ArticleManager
 * @package App\AdminModule\Model
 */
class ArticleManager extends Nette\Object
{

	/**
	 * @var Nette\Database\Context
	 */
	private $database;

	/**
	 * @var
	 */
	private $lastId;

	/**
	 * @return mixed
	 */
	public function getLastId()
	{
		return $this->lastId;
	}

	/**
	 * @param Nette\Database\Context $database
	 */
	function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/**
	 * @param $id
	 * @return mixed
	 * @throws \Exception
	 */
	public function get($id)
	{
		$data = $this->database->table('article')->select('*')->where('id', $id)->fetch();
		if (!$data) {
			throw new \Exception('Not found');
		}

		return $data;
	}

	/**
	 * @return array|Nette\Database\Table\IRow[]
	 */
	public function getAll()
	{
		return $this->database->table('article')->order('id DESC')->fetchAll();
	}


	/**
	 * @param $article
	 * @throws \Exception
	 */
	public function add($article)
	{
		if (!$this->database->table('article')->insert($article)) {
			throw new \Exception('Nepodařilo se uložit článek!');
		}
		$data = $this->database->table('article')->where($article)->fetch();
		$this->lastId = $data['id'];
	}

	/**
	 * @param $article
	 * @return bool|mixed|Nette\Database\Table\IRow
	 */
	public function remove($article)
	{
		return $this->database->table('article')->where('id', $article)->delete();
	}

	/**
	 * @param $data
	 * @param $id
	 */
	public function save($data, $id)
	{
		$this->database->table('article')->where('id', $id)->update($data);
	}

}