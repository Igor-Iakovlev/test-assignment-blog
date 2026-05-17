CREATE TABLE categories (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(160)   NOT NULL,
    name        VARCHAR(160)   NOT NULL,
    description TEXT           NULL,
    created_at  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE posts (
    id           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    slug         VARCHAR(200)   NOT NULL,
    title        VARCHAR(255)   NOT NULL,
    description  VARCHAR(500)   NULL,
    body         MEDIUMTEXT     NOT NULL,
    image        VARCHAR(500)   NULL,
    views        INT UNSIGNED   NOT NULL DEFAULT 0,
    published_at DATETIME       NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_posts_slug (slug),
    KEY idx_posts_published_at (published_at),
    KEY idx_posts_views (views)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE post_categories (
    post_id     INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, category_id),
    KEY idx_pc_category (category_id),
    CONSTRAINT fk_pc_post     FOREIGN KEY (post_id)     REFERENCES posts(id)      ON DELETE CASCADE,
    CONSTRAINT fk_pc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
