<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Controllers;

use TestAssignmentBlog\Core\Controller;
use TestAssignmentBlog\Exceptions\NotFoundException;
use TestAssignmentBlog\Models\Category;
use TestAssignmentBlog\Models\Post;
use TestAssignmentBlog\Support\Paginator;

class CategoryController extends Controller
{
    private const int PER_PAGE = 10;

    public function show(string $slug): void
    {
        $category = Category::findBySlug($slug);
        if ($category === null) {
            throw new NotFoundException("Category {$slug} not found");
        }

        $sort = $_GET['sort'] ?? Post::SORT_DATE;
        if (!in_array($sort, [Post::SORT_DATE, Post::SORT_VIEWS], true)) {
            $sort = Post::SORT_DATE;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $result = Post::listForCategory((int) $category['id'], $sort, $page, self::PER_PAGE);

        $paginator = new Paginator(
            $page,
            self::PER_PAGE,
            $result['total'],
            "/category/{$slug}",
            ['sort' => $sort],
        );

        $this->view->display('category.tpl', [
            'title' => $category['name'],
            'category' => $category,
            'posts' => $result['items'],
            'sort' => $sort,
            'paginator' => $paginator,
        ]);
    }
}
