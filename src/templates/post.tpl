<nav class="breadcrumbs">
    <a href="/">Home</a>
    {foreach $post.categories as $cat}
        / <a href="/category/{$cat.slug}">{$cat.name|escape}</a>
    {/foreach}
</nav>

<article class="post">
    {if !empty($post.image)}
        <img class="post__image" src="{$post.image|escape}" alt="{$post.title|escape}">
    {/if}
    <h1 class="post__title">{$post.title|escape}</h1>
    <div class="post__meta">
        <time datetime="{$post.published_at}">{$post.published_at|date_format:'%Y-%m-%d'}</time>
        · {$post.views} views
    </div>
    {if !empty($post.description)}
        <p class="post__description">{$post.description|escape}</p>
    {/if}
    <div class="post__body">{$post.body|escape|nl2br}</div>
</article>

{if !empty($related)}
    <aside class="related">
        <h2>Related posts</h2>
        <ul class="post-list">
            {foreach $related as $r}
                <li class="post-list__item">
                    <h3 class="post-list__title">
                        <a href="/post/{$r.slug}">{$r.title|escape}</a>
                    </h3>
                    {if !empty($r.description)}
                        <p class="post-list__description">{$r.description|escape}</p>
                    {/if}
                </li>
            {/foreach}
        </ul>
    </aside>
{/if}
