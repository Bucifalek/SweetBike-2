<?php

namespace App\WebModule\Presenters;

use Nette;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->blog = false;
	}

	public function renderBlogArticle($id)
	{

	}

}
