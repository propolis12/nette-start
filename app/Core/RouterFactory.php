<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        $router->addRoute('/api/findByStatus/<status>', 'Api:findByStatus');
        $router->addRoute('/api/delete/<petId>', 'Api:delete');
        $router->addRoute('/api/pet[/<petId>]', 'Api:pet');
        $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }
}
