<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 13:51, 10. 5. 2015
 */

namespace App\AdminModule\Presenters;

use Nette;
use Nette\Application\UI\Form;

class SignPresenter extends BasePresenter
{

	public function renderOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Účet úspěšně odhlášen.', 'success');
		$this->redirect('Sign:in');
	}

	/**
	 *
	 */
	public function renderSignIn()
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->redirect('Articles:default');
		}
	}

	protected function createComponentLoginForm()
	{
		$form = new Form();
		$form->addProtection();
		$form->addText('email')->setRequired('Musíte zadat email');
		$form->addPassword('password');
		$form->addSubmit('login');
		$form->onSuccess[] = array($this, 'loginFormSucceeded');

		return $form;
	}


	public function loginFormSucceeded(Form $form)
	{
		$values = $form->values;
		try {
			$this->getUser()->login($values->email, $values->password);
			$this->flashMessage('Nyní jste úspěšně přihlášen.', 'success');
			$this->redirect('Articles:default');
		} catch (Nette\Security\AuthenticationException $e) {
			$this->flashMessage($e->getMessage(), 'error');
			$this->redirect('Sign:in');
		}
	}
}