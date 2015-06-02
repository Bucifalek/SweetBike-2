<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 9:44, 9. 5. 2015
 */

namespace App\WebModule\Presenters;

use Nette;

class BlogPresenter extends BasePresenter
{

	public function renderDefault() {
		$this->template->blog = false;
	}

	public function actionView($articleId)
	{

	}
}