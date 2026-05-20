<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use TestAssignmentBlog\Controllers\CategoryController;
use TestAssignmentBlog\Controllers\HomeController;
use TestAssignmentBlog\Controllers\PostController;
use TestAssignmentBlog\Core\Router;
use TestAssignmentBlog\Core\View;
use TestAssignmentBlog\Exceptions\NotFoundException;

$view = new View();

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/category/{slug}', [CategoryController::class, 'show']);
$router->get('/post/{slug}', [PostController::class, 'show']);

try {
    [$class, $action, $params] = $router->dispatch(
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        $_SERVER['REQUEST_URI'] ?? '/',
    );

    $controller = new $class($view);
    $controller->$action(...array_values($params));
} catch (NotFoundException $e) {
    http_response_code(404);
    try {
        $view->display('error.tpl', ['code' => 404, 'message' => 'Not Found']);
    } catch (Throwable) {
        echo 'Not Found';
    }
} catch (Throwable $e) {
    http_response_code(500);
    try {
        $view->display('error.tpl', [
            'code' => 500,
            'message' => 'Internal Server Error',
            'detail' => $e->getMessage(),
        ]);
    } catch (Throwable) {
        echo 'Internal Server Error';
    }
}
