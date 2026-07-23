<?php
/** Render shared and page-specific admin CSS without relying on an HTTP asset request. */
function render_admin_styles(string $baseStylesheet, string $pageStylesheet): void
{
    $baseCss = is_file($baseStylesheet) ? file_get_contents($baseStylesheet) : '';
    $pageCss = is_file($pageStylesheet) ? file_get_contents($pageStylesheet) : '';
    $pageCss = preg_replace('/@import\s+url\(["\'](?:dashboard_style|login_style)\.css["\']\);\s*/', '', $pageCss);

    echo "<style>\n" . $baseCss . "\n" . $pageCss . "\n</style>";
}
