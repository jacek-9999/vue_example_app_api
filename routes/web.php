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
    $router->group(['middleware' => 'auth'], function ($router)
    {
        $router->get('me', 'AuthController@me');
    });

    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    $router->get(
        '/node/{id}',
        'ActionNodeController@read'
    );

    $router->get(
        '/node_options/{id}',
        'ActionNodeController@readOptions'
    );
    $router->options(
        '/node',
        'ActionNodeController@create'
    );
    $router->put(
        '/node',
        'ActionNodeController@create'
    );

    $router->options(
        '/option',
        'ActionNodeController@addOption'
    );
    $router->put(
        '/option',
        'ActionNodeController@addOption'
    );

    $router->post(
        '/target',
        'ActionNodeController@setTarget'
    );

    $router->get(
        '/target/{optionId}',
        'ActionNodeController@getOptionTarget'
    );

    $router->patch(
        '/node/{id}',
        'ActionNodeController@edit'
    );
    $router->options(
        '/node/{id}',
        'ActionNodeController@delete'
    );
    $router->delete(
        '/node/{id}',
        'ActionNodeController@delete'
    );
    $router->options(
        '/unlink_node',
        'ActionNodeController@unlinkNode'
    );
    $router->patch(
        '/unlink_node',
        'ActionNodeController@unlinkNode'
    );
    $router->options(
        '/stories',
        'ActionNodeController@getStoriesList'
    );
    $router->get(
        '/stories',
        'ActionNodeController@getStoriesList'
    );

    $router->get(
        '/story/{id}',
        'ActionNodeController@getStory'
    );
    $router->delete(
        '/story/{id}',
        'ActionNodeController@storyDelete'
    );
    $router->options(
        '/story/{id}',
        'ActionNodeController@storyDelete'
    );
    $router->patch(
        '/node/{id}',
        'ActionNodeController@edit'
    );
});