<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Models;

use PDO;
use TestAssignmentBlog\Core\Database;

class Post
{
    public const string SORT_DATE = 'date';
    public const string SORT_VIEWS = 'views';

    private const array SORT_COLUMNS = [
        self::SORT_DATE => 'p.published_at',
        self::SORT_VIEWS => 'p.views',
    ];

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

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public static function listForCategory(int $categoryId, string $sort, int $page, int $perPage): array
    {
        $orderColumn = self::SORT_COLUMNS[$sort] ?? self::SORT_COLUMNS[self::SORT_DATE];
        $offset = max(0, ($page - 1) * $perPage);

        $pdo = Database::connection();

        $countStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM posts p
             INNER JOIN post_categories pc ON pc.post_id = p.id
             WHERE pc.category_id = :category_id'
        );
        $countStmt->execute(['category_id' => $categoryId]);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT p.id, p.slug, p.title, p.description, p.image, p.views, p.published_at
                FROM posts p
                INNER JOIN post_categories pc ON pc.post_id = p.id
                WHERE pc.category_id = :category_id
                ORDER BY {$orderColumn} DESC, p.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT id, slug, title, description, body, image, views, published_at
             FROM posts WHERE slug = :slug'
        );
        $stmt->execute(['slug' => $slug]);
        $post = $stmt->fetch();
        if ($post === false) {
            return null;
        }

        $categoriesStmt = $pdo->prepare(
            'SELECT c.id, c.slug, c.name
             FROM categories c
             INNER JOIN post_categories pc ON pc.category_id = c.id
             WHERE pc.post_id = :post_id
             ORDER BY c.name'
        );
        $categoriesStmt->execute(['post_id' => $post['id']]);
        $post['categories'] = $categoriesStmt->fetchAll();

        return $post;
    }

    public static function incrementViews(int $postId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE posts SET views = views + 1 WHERE id = :id'
        );
        $stmt->execute(['id' => $postId]);
    }

    /**
     * Posts sharing at least one category with the given post, excluding it.
     * Ordered by number of shared categories (DESC), then by date.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function relatedTo(int $postId, int $limit): array
    {
        $sql = 'SELECT p.id, p.slug, p.title, p.description, p.image, p.published_at,
                       COUNT(*) AS shared
                FROM posts p
                INNER JOIN post_categories pc ON pc.post_id = p.id
                WHERE pc.category_id IN (
                    SELECT category_id FROM post_categories WHERE post_id = :source_id
                )
                AND p.id != :exclude_id
                GROUP BY p.id, p.slug, p.title, p.description, p.image, p.published_at
                ORDER BY shared DESC, p.published_at DESC
                LIMIT :limit';

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':source_id', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':exclude_id', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
