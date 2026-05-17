<section class="error">
    <h1>{$code} — {$message|escape}</h1>
    {if !empty($detail)}
        <pre class="error__detail">{$detail|escape}</pre>
    {/if}
    <p><a href="/">Back to home</a></p>
</section>
