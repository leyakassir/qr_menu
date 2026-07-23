<?php
/** Render shared and page-specific admin CSS without relying on an HTTP asset request. */
function render_admin_styles(string $baseStylesheet, string $pageStylesheet): void
{
    $fallbackCss = <<<'CSS'
* { box-sizing: border-box; }
body { margin: 0; min-height: 100vh; background: #f6f7fb; color: #243042; font-family: Arial, sans-serif; }
.sidebar { position: fixed; z-index: 20; top: 0; left: 0; width: 250px; height: 100vh; overflow-y: auto; background: #212529; color: #fff; padding: 20px 0; }
.sidebar .nav { display: block; padding: 0; margin: 0; list-style: none; }
.sidebar .nav-item { display: block; }
.sidebar .nav-link, .sidebar .sub-menu a { display: flex; gap: 10px; align-items: center; padding: 11px 22px; color: #d7dde5; text-decoration: none; }
.sidebar .nav-link:hover, .sidebar .nav-link.active, .sidebar .sub-menu a:hover, .sidebar .sub-menu a.active { color: #212529; background: #ffc107; }
.sidebar .sub-menu { display: block; list-style: none; margin: 0; padding: 0 0 6px 18px; }
.sidebar-heading { margin: 20px 22px 8px; color: #ffc107; font-size: 11px; font-weight: bold; letter-spacing: .1em; text-transform: uppercase; }
.navbar-top { position: fixed; z-index: 10; top: 0; right: 0; left: 250px; min-height: 72px; display: flex; align-items: center; background: #fff; border-bottom: 3px solid #ffc107; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
.navbar-top .container-fluid { width: 100%; display: flex; align-items: center; justify-content: space-between; }
.main-content { margin-left: 250px; padding: 104px 28px 32px; }
.card { background: #fff; border: 0; border-radius: 14px; box-shadow: 0 3px 16px rgba(31,41,55,.08); overflow: hidden; }
.card-header { padding: 18px 22px; background: #fff; border-bottom: 1px solid #edf0f3; }
.card-body { padding: 22px; }
.btn { display: inline-block; border: 0; border-radius: 8px; padding: 10px 16px; text-decoration: none; cursor: pointer; }
.btn-primary { background: #f97316; color: #fff; }
.btn-outline-primary { border: 1px solid #f97316; color: #f97316; background: #fff; }
.form-control, .form-select { display: block; width: 100%; min-height: 42px; padding: 9px 12px; border: 1px solid #d8dee6; border-radius: 8px; background: #fff; }
.table { width: 100%; border-collapse: collapse; background: #fff; }
.table th, .table td { padding: 14px; border-bottom: 1px solid #edf0f3; text-align: left; }
.table-light th { background: #fff7dd; color: #5f4a00; }
.badge { display: inline-block; padding: 5px 9px; border-radius: 99px; }
.alert { padding: 13px 16px; border-radius: 8px; margin-bottom: 18px; }
.alert-success { color: #0f5132; background: #d1e7dd; }.alert-danger { color: #842029; background: #f8d7da; }
@media (max-width: 800px) { .sidebar { position: static; width: 100%; height: auto; }.navbar-top { position: static; left: 0; }.main-content { margin-left: 0; padding: 24px 16px; } }
CSS;
    $baseCss = is_file($baseStylesheet) ? file_get_contents($baseStylesheet) : '';
    $pageCss = is_file($pageStylesheet) ? file_get_contents($pageStylesheet) : '';
    $pageCss = preg_replace('/@import\s+url\(["\'](?:dashboard_style|login_style)\.css["\']\);\s*/', '', $pageCss);

    echo "<style>\n" . $fallbackCss . "\n" . $baseCss . "\n" . $pageCss . "\n</style>";
}
