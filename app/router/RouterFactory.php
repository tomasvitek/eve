<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{

    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter()
    {
        $router = new RouteList;
        $router[] = new Route('admin', 'Sign:in');
        $router[] = new Route('dai/seminars/events/attend/<id>', 'Events:attend', Route::ONE_WAY);
        $router[] = new Route('<presenter>/<action>[/<id>]', 'Events:default');
        return $router;
    }
}
