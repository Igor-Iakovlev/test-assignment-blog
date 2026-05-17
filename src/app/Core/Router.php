<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Core;

use TestAssignmentBlog\Exceptions\NotFoundException;

class Router
{
    /** @var array<int, array{string, string, array{class-string, string}}> */
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->routes[] = ['GET', $pattern, $handler];
    }

    /**
     * @return array{class-string, string, array<string, string>}
     */
    public function dispatch(string $method, string $uri): array
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $regex = '#^' . preg_replace('#\{(\w+)}#', '(?P<$1>[^/]+)', $pattern) . '$#';
            if (preg_match($regex, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return [$handler[0], $handler[1], $params];
            }
        }

        throw new NotFoundException("No route for {$method} {$path}");
    }
}
