<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Faker\Factory;
use Faker\Generator;
use TestAssignmentBlog\Core\Database;

const DEFAULT_CATEGORIES = 5;
const DEFAULT_POSTS = 50;
const SEED = 2026;

const PREDEFINED_CATEGORIES = [
    ['php', 'PHP', 'Articles about modern PHP and its ecosystem.'],
    ['docker', 'Docker', 'Containerization, orchestration and dev environments.'],
    ['databases', 'Databases', 'SQL, schema design and query performance.'],
    ['frontend', 'Frontend', 'HTML, CSS, JavaScript and templating engines.'],
    ['devops', 'DevOps', 'CI/CD, infrastructure and operations.'],
];

$usage = <<<USAGE
    Usage: php db/seed.php [options]

    Without arguments, adds 5 categories and 50 posts to the existing data.
    With --categories or --posts, adds only what you ask for.
    Use --reset to wipe the tables first.

    Options:
      -c N, --categories=N    Number of categories to add
      -p N, --posts=N         Number of posts to add
      -r,   --reset           Truncate tables before seeding
      -h,   --help            Show this help and exit

    USAGE;

$options = getopt('hc:p:r', ['help', 'categories:', 'posts:', 'reset']);

if (isset($options['h']) || isset($options['help'])) {
    echo $usage;
    exit(0);
}

$knownShort = ['-h', '-c', '-p', '-r'];
$knownLong = ['--help', '--categories', '--posts', '--reset'];
$expectsValue = false;
foreach (array_slice($argv, 1) as $arg) {
    if ($expectsValue) {
        $expectsValue = false;
        continue;
    }
    if (str_starts_with($arg, '--')) {
        $name = explode('=', $arg, 2)[0];
        if (!in_array($name, $knownLong, true)) {
            fwrite(STDERR, "Unknown argument: {$arg}\n\n{$usage}");
            exit(1);
        }
    } elseif (str_starts_with($arg, '-')) {
        if (!in_array($arg, $knownShort, true)) {
            fwrite(STDERR, "Unknown argument: {$arg}\n\n{$usage}");
            exit(1);
        }
        if (in_array($arg, ['-c', '-p'], true)) {
            $expectsValue = true;
        }
    } else {
        fwrite(STDERR, "Unexpected positional argument: {$arg}\n\n{$usage}");
        exit(1);
    }
}

$categoriesValue = $options['categories'] ?? $options['c'] ?? null;
$postsValue = $options['posts'] ?? $options['p'] ?? null;
$reset = isset($options['reset']) || isset($options['r']);

foreach (['--categories' => $categoriesValue, '--posts' => $postsValue] as $name => $value) {
    if ($value !== null && !ctype_digit((string) $value)) {
        fwrite(STDERR, "Invalid value for {$name}: '{$value}' (expected a non-negative integer)\n\n{$usage}");
        exit(1);
    }
}

$nothingSpecified = $categoriesValue === null && $postsValue === null;
$applyDefaults = $reset || $nothingSpecified;
$categoryCount = $categoriesValue !== null ? (int) $categoriesValue : ($applyDefaults ? DEFAULT_CATEGORIES : 0);
$postCount = $postsValue !== null ? (int) $postsValue : ($applyDefaults ? DEFAULT_POSTS : 0);

$pdo = Database::connection();
$faker = Factory::create();
$faker->seed(SEED);

if ($reset) {
    echo "Clearing existing data...\n";
    $pdo->exec('DELETE FROM post_categories');
    $pdo->exec('DELETE FROM posts');
    $pdo->exec('DELETE FROM categories');
    $pdo->exec('ALTER TABLE categories AUTO_INCREMENT = 1');
    $pdo->exec('ALTER TABLE posts AUTO_INCREMENT = 1');
}

seedCategories($pdo, $faker, $categoryCount);
$categoryIds = array_map('intval', $pdo->query('SELECT id FROM categories')->fetchAll(PDO::FETCH_COLUMN));
seedPosts($pdo, $faker, $categoryIds, $postCount);

printf("Done. Total in DB: %d categories, %d posts.\n", countRows($pdo, 'categories'), countRows($pdo, 'posts'));

function seedCategories(PDO $pdo, Generator $faker, int $count): void
{
    if ($count === 0) {
        return;
    }
    echo "Seeding categories...\n";

    $existing = $pdo->query('SELECT slug FROM categories')->fetchAll(PDO::FETCH_COLUMN);
    $usedSlugs = array_flip($existing);

    $candidates = [];
    foreach (PREDEFINED_CATEGORIES as [$slug, $name, $description]) {
        if (!isset($usedSlugs[$slug])) {
            $candidates[] = [$slug, $name, $description];
            $usedSlugs[$slug] = true;
        }
    }

    while (count($candidates) < $count) {
        $name = ucfirst($faker->unique()->word());
        $slug = strtolower($name);
        if (isset($usedSlugs[$slug])) {
            continue;
        }
        $candidates[] = [$slug, $name, $faker->sentence(8)];
        $usedSlugs[$slug] = true;
    }

    $insert = $pdo->prepare(
        'INSERT INTO categories (slug, name, description) VALUES (:slug, :name, :description)',
    );
    foreach (array_slice($candidates, 0, $count) as [$slug, $name, $description]) {
        $insert->execute([
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
        ]);
    }
}

/**
 * @param list<int> $categoryIds
 */
function seedPosts(PDO $pdo, Generator $faker, array $categoryIds, int $count): void
{
    if ($count === 0) {
        return;
    }
    if ($categoryIds === []) {
        fwrite(STDERR, "Cannot seed posts: no categories in the database.\n");
        exit(1);
    }
    echo "Seeding posts...\n";

    $insertPost = $pdo->prepare(
        'INSERT INTO posts (slug, title, description, body, image, views, published_at)
         VALUES (:slug, :title, :description, :body, :image, :views, :published_at)',
    );
    $insertPostCategory = $pdo->prepare(
        'INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)',
    );

    $offset = countRows($pdo, 'posts');
    for ($i = 1; $i <= $count; $i++) {
        $serial = $offset + $i;
        $title = rtrim($faker->sentence($faker->numberBetween(4, 8)), '.');
        $slug = sprintf('%s-%d', $faker->slug(3), $serial);

        $insertPost->execute([
            'slug' => $slug,
            'title' => $title,
            'description' => $faker->sentence($faker->numberBetween(10, 16)),
            'body' => implode("\n\n", $faker->paragraphs($faker->numberBetween(4, 8))),
            'image' => sprintf('https://picsum.photos/seed/%d/800/400', $serial),
            'views' => $faker->numberBetween(0, 5000),
            'published_at' => $faker->dateTimeBetween('-6 months')->format('Y-m-d H:i:s'),
        ]);
        $postId = (int) $pdo->lastInsertId();

        $assigned = $faker->randomElements(
            $categoryIds,
            $faker->numberBetween(1, min(3, count($categoryIds))),
        );
        foreach ($assigned as $categoryId) {
            $insertPostCategory->execute([
                'post_id' => $postId,
                'category_id' => $categoryId,
            ]);
        }
    }
}

function countRows(PDO $pdo, string $table): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
}
