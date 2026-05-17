<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$title|default:'Blog'}</title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <header class="site-header">
        <a href="/" class="site-title">Blog</a>
    </header>

    <main class="site-main">
        {include file=$content_template}
    </main>

    <footer class="site-footer">
        <small>Test assignment</small>
    </footer>
</body>
</html>
