<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpZero\Core\Router\Route;


$route = new Route();

$route->addRoute('GET', '/{param}', 'HomeController', 'index');

$result = $route->match('GET', '/123');

var_dump($result);


$serialized = serialize($route);
var_dump($serialized);

$unserialized = unserialize($serialized);
var_dump($unserialized);
