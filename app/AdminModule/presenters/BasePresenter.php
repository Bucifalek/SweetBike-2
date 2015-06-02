<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Model\UsersManager;
use Nette;
use Tracy\Debugger;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/**
	 *
	 */
	public function beforeRender()
	{
		if (!$this->getUser()->isLoggedIn() AND !$this->isLinkCurrent('Sign:in')) {
			$this->redirect('Sign:in');
		} else {
			$roles = $this->getUser()->getRoles();

			if (isset($roles['email'])) {
				$this->template->userEmail = $roles['email'];
			}
		}

		$this->template->user = $this->getUser()->isLoggedIn();
	}
}
