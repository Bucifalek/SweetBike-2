<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 8:58, 1. 4. 2015
 */

namespace App\AdminModule\Model;

use Nette;
use Nette\Security as NS;

/**
 * Class userAuth
 * @package App\AdminModule\Model
 */
class UserAuth extends Nette\Object implements NS\IAuthenticator
{

	/**
	 * @var Nette\Database\Context
	 */
	public $database;

	/**
	 * @param Nette\Database\Context $database
	 */
	function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * @param array $credentials
	 * @return NS\Identity
	 * @throws NS\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;
		$row = $this->database->table('admin')->where('email', $email)->fetch();

		if (!$row || !NS\Passwords::verify($password, $row->password)) {
			throw new NS\AuthenticationException('Nesprávný email nebo heslo.');
		}

		return new NS\Identity($row->id, array(
			'email' => $row->email,
		));
	}
}