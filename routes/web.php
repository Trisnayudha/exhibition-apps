<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//Login
$router->post('auth/loginOtp', 'AuthController@loginOtp');
$router->post('auth/verifyOtp', 'AuthController@verifyOtp');
$router->post('auth/loginEmailPassword', 'AuthController@loginEmailPassword');


//Forgot
$router->post('forgot', 'ForgotController@forgot');
$router->post('forgot/verifyOtp', 'ForgotController@verifyOtp');
$router->post('forgot/resetPassword', 'ForgotController@resetPassword');

//Scanner
$router->post('scan', 'ScannerController@scan');
$router->post('scan/add', 'ScannerController@store');

//Home
$router->post('home/countVisitor', 'HomeController@countVisitor');
$router->post('home/banner', 'HomeController@homeBanner');
