<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route;

/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList();

		$router[] = new Route('admin', 'Admin:Articles:default', ROUTE::ONE_WAY);
		$router[] = new Route('admin/clanky', 'Admin:Articles:default');
		$router[] = new Route('admin/clanky/pridat', 'Admin:Articles:add');
		$router[] = new Route('admin/clanky/<article>/upravit-fotogalerii', 'Admin:Articles:editPhotos');
		$router[] = new Route('admin/clanky/<article>/upravit', 'Admin:Articles:edit');
		$router[] = new Route('admin/clanky/<article>/odstranit', 'Admin:Articles:remove');
		$router[] = new Route('admin/prihlasit-se', 'Admin:Sign:in');
		$router[] = new Route('admin/odhlasit-se', 'Admin:Sign:out');


		$router[] = new Route('nase-sluzby', 'Web:Services:default');
		$router[] = new Route('galerie', 'Web:Gallery:default');
		//$router[] = new Route('zajimave-akce', 'Web:Blog:default');
		//$router[] = new Route('zajimave-akce/clanek[/<articleId>]', 'Web:Blog:view');
		$router[] = new Route('kontakt', 'Web:Contact:default');

		//$router[] = new Route('/', 'Web:Homepage:default');
		//$router[] = new Route('www/', 'Web:Homepage:default');
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Web:Homepage:default');

		return $router;
	}
}