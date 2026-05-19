<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Models;

use TestAssignmentBlog\Core\Database;

class Category
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function allWithPosts(): array
    {
        $sql = 'SELECT c.id, c.slug, c.name, c.description
                FROM categories c
                INNER JOIN post_categories pc ON pc.category_id = c.id
                GROUP BY c.id, c.slug, c.name, c.description
                ORDER BY c.name';

        return Database::connection()->query($sql)->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, slug, name, description FROM categories WHERE slug = :slug'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
