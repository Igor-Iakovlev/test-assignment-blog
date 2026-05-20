<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Controllers;

use Smarty\Exception;
use TestAssignmentBlog\Core\Controller;
use TestAssignmentBlog\Exceptions\NotFoundException;
use TestAssignmentBlog\Models\Post;

class PostController extends Controller
{
    private const int RELATED_LIMIT = 3;

    /**
     * @throws Exception
     */
    public function show(string $slug): void
    {
        $post = Post::findBySlug($slug);
        if ($post === null) {
            throw new NotFoundException("Post {$slug} not found");
        }

        Post::incrementViews((int) $post['id']);
        $post['views'] = (int) $post['views'] + 1;

        $related = Post::relatedTo((int) $post['id'], self::RELATED_LIMIT);

        $this->view->display('post.tpl', [
            'title' => $post['title'],
            'post' => $post,
            'related' => $related,
        ]);
    }
}
