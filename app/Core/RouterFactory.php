<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\Route;
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
//        $router->addRoute('/api/pet/<petId>', [
//            'presenter' => 'Api',
//            'action' => [
//                Route:: => 'getPet',
//                Route::POST => 'updatePet',
//                Route::DELETE => 'deletePet'
//            ]
//        ]);
        $router->addRoute('/api/findByStatus/<status>', 'Api:findByStatus');
//        $router->addRoute('api/delete/<petId>', 'Api:delete');
        $router->addRoute('/api/pet[/<petId>]', 'Api:pet');
        $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
//        $router->addRoute('/api/pet/findByTags', 'ApiPresenter:findByTags');
//        $router->addRoute('/api/pet/<petId>', 'ApiPresenter:findById');
//        $router->addRoute('/api/pet/<petId>/uploadImage', 'ApiPresenter:uploadImage');

        return $router;
    }
}
