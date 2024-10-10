<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

//	public static function createRouter(): RouteList
//	{
//		$router = new RouteList;
//		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
//		return $router;
//	}

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        // Definícia rout pre API výstupy
        $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
        $router->addRoute('/api/pet', 'Api\PetPresenter:add');
        $router->addRoute('/api/pet/findByStatus', 'Api\PetPresenter:findByStatus');
        $router->addRoute('/api/pet/findByTags', 'Api\PetPresenter:findByTags');
        $router->addRoute('/api/pet/<petId>', 'Api\PetPresenter:findById');
        $router->addRoute('/api/pet/<petId>/uploadImage', 'Api\PetPresenter:uploadImage');

        return $router;
    }
}
