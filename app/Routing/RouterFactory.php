<?php
declare(strict_types=1);

namespace App\Routing;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{
    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        // Definícia rout pre API výstupy s rôznymi HTTP metódami
        $router->addRoute('/api/pet', 'Api\Pet:add'); // Pridá nového maznáčika (POST)
        $router->addRoute('/api/pet', 'Api\PetApi:update'); // Aktualizuje maznáčika (PUT)
        $router->addRoute('/api/pet/findByStatus', 'Api\PetApi:findByStatus'); // Nájde maznáčikov podľa stavu (GET)
        $router->addRoute('/api/pet/findByTags', 'Api\PetApi:findByTags'); // Nájde maznáčikov podľa tagov (GET)
        $router->addRoute('/api/pet/<petId>', 'Api\PetApi:findById'); // Nájde maznáčika podľa ID (GET)
        $router->addRoute('/api/pet/<petId>', 'Api\PetApi:updateWithForm'); // Aktualizuje maznáčika pomocou formulára (POST)
        $router->addRoute('/api/pet/<petId>', 'Api\PetApi:delete'); // Vymaže maznáčika podľa ID (DELETE)
        $router->addRoute('/api/pet/<petId>/uploadImage', 'Api\PetApi:uploadImage'); // Nahraje obrázok maznáčika (POST)

        return $router;
    }
}