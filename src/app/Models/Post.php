<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Models;

use PDO;
use TestAssignmentBlog\Core\Database;

class Post
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function latestForCategory(int $categoryId, int $limit): array
    {
        $sql = 'SELECT p.id, p.slug, p.title, p.description, p.image, p.published_at
                FROM posts p
                INNER JOIN post_categories pc ON pc.post_id = p.id
                WHERE pc.category_id = :category_id
                ORDER BY p.published_at DESC
                LIMIT :limit';

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
