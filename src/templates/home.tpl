<h1>Latest posts</h1>

{if empty($categories)}
    <p class="empty">No posts yet.</p>
{else}
    {foreach $categories as $category}
        <section class="category-block">
            <header class="category-block__header">
                <h2><a href="/category/{$category.slug}">{$category.name|escape}</a></h2>
                {if !empty($category.description)}
                    <p class="category-block__description">{$category.description|escape}</p>
                {/if}
            </header>

            <div class="category-block__posts">
                {foreach $category.posts as $post}
                    <article class="post-card">
                        {if !empty($post.image)}
                            <a href="/post/{$post.slug}">
                                <img class="post-card__image" src="{$post.image|escape}" alt="{$post.title|escape}">
                            </a>
                        {/if}
                        <h3 class="post-card__title">
                            <a href="/post/{$post.slug}">{$post.title|escape}</a>
                        </h3>
                        {if !empty($post.description)}
                            <p class="post-card__description">{$post.description|escape}</p>
                        {/if}
                        <a class="post-card__more" href="/post/{$post.slug}">Read more</a>
                    </article>
                {/foreach}
            </div>

            <a class="btn btn--all" href="/category/{$category.slug}">All articles</a>
        </section>
    {/foreach}
{/if}
