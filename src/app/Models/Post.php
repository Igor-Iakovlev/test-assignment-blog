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
}
