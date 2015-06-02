<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 9:18, 1. 4. 2015
 */

namespace App\AdminModule\Model;

use Nette;
use Nette\Security as NS;

/**
 * Class UserManager
 * @package App\AdminModule\Model
 */
class UsersManager extends Nette\Object
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
	 * @param $details
	 * @throws \Exception
	 */
	public function add($details)
	{
		if (!$this->database->table('admin')->insert($details)) {
			throw new \Exception('Nepodařilo se uložit do databáze');
		}
	}

}