<nav class="breadcrumbs">
    <a href="/">Home</a> / <span>{$category.name|escape}</span>
</nav>

<header class="category-header">
    <h1>{$category.name|escape}</h1>
    {if !empty($category.description)}
        <p class="category-header__description">{$category.description|escape}</p>
    {/if}
</header>

<form method="get" class="sort-form">
    <label for="sort">Sort by:</label>
    <select name="sort" id="sort" onchange="this.form.submit()">
        <option value="date"  {if $sort === 'date'}selected{/if}>Date</option>
        <option value="views" {if $sort === 'views'}selected{/if}>Views</option>
    </select>
    <noscript><button type="submit">Apply</button></noscript>
</form>

{if empty($posts)}
    <p class="empty">No posts in this category.</p>
{else}
    <ul class="post-list">
        {foreach $posts as $post}
            <li class="post-list__item">
                <h2 class="post-list__title">
                    <a href="/post/{$post.slug}">{$post.title|escape}</a>
                </h2>
                <div class="post-list__meta">
                    <time datetime="{$post.published_at}">{$post.published_at|date_format:'%Y-%m-%d'}</time>
                    · {$post.views} views
                </div>
                {if !empty($post.description)}
                    <p class="post-list__description">{$post.description|escape}</p>
                {/if}
            </li>
        {/foreach}
    </ul>

    {include file='_pagination.tpl' paginator=$paginator}
{/if}
