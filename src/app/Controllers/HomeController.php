<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Controllers;

use Smarty\Exception;
use TestAssignmentBlog\Core\Controller;
use TestAssignmentBlog\Models\Category;
use TestAssignmentBlog\Models\Post;

class HomeController extends Controller
{
    private const int POSTS_PER_CATEGORY = 3;

    /**
     * @throws Exception
     */
    public function index(): void
    {
        $categories = Category::allWithPosts();
        foreach ($categories as &$category) {
            $category['posts'] = Post::latestForCategory(
                (int) $category['id'],
                self::POSTS_PER_CATEGORY,
            );
        }
        unset($category);

        $this->view->display('home.tpl', [
            'title' => 'Blog',
            'categories' => $categories,
        ]);
    }
}
