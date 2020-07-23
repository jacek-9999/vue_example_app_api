<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->group(['middleware' => 'cors'], function () use ($router) {
    $router->get('/', function () use ($router) {
        return $router->app->version();
    });

    $router->get(
        '/node/{id}',
        'ActionNodeController@read'
    );


    $router->post(
        '/node',
        'ActionNodeController@create'
    );

    $router->patch(
        '/node/{id}',
        'ActionNodeController@edit'
    );

    $router->patch(
        '/node/{id}',
        'ActionNodeController@delete'
    );
});