<?php
/** Render shared and page-specific admin CSS without relying on an HTTP asset request. */
function render_admin_styles(string $baseStylesheet, string $pageStylesheet, bool $includeSidebarControls = true): void
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

    $themeCss = '.sidebar{background:#2b1b17;color:#fff}.sidebar .text-muted{color:#f5d9ad!important}.sidebar h4 .fa-burger{display:none}.sidebar h4:before{content:"";display:inline-block;width:38px;height:38px;margin-right:9px;vertical-align:middle;background:url("/assets/images/crave-wave-logo.png") center/cover no-repeat;border-radius:50%}.sidebar .nav-link{color:#fff!important}.sidebar .nav-link.active,.sidebar .nav-link:hover{background:rgba(247,183,51,.2);color:#fff!important}.sidebar .sub-menu a{color:#ffe8c1!important}.sidebar .sub-menu a:hover{background:rgba(247,183,51,.12);color:#fff!important}.sidebar .sub-menu a.active{background:#f7b733;color:#2b1b17!important;font-weight:700}.sidebar-heading{color:#f7b733}.navbar-top{border-bottom-color:#f7b733}.btn-primary{background:#d94841;color:#fff}.btn-primary:hover{background:#b52f2a}.text-primary{color:#d94841!important}body{background:#fff8ed}.badge.bg-primary{background:#fff0d5!important;color:#b52f2a!important}.bg-primary.bg-opacity-10{background:#fce4e1!important}.sidebar .sub-menu{display:none}.sidebar .sub-menu.menu-open{display:block}.sidebar-dropdown-trigger .fa-chevron-down{margin-left:auto;transition:transform .2s}.sidebar-dropdown-trigger.is-open .fa-chevron-down{transform:rotate(180deg)}.main-content{min-width:0}.table-responsive{display:block;width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}';
    echo "<style>\n" . $fallbackCss . "\n" . $baseCss . "\n" . $pageCss . "\n" . $themeCss . "\n</style>";
    if ($includeSidebarControls) {
        render_admin_sidebar_controls();
    }
}

/** Adds a responsive sidebar toggle once per page. */
function render_admin_sidebar_controls(): void
{
    static $rendered = false;
    if ($rendered) {
        return;
    }
    $rendered = true;
    echo <<<'HTML'
<style>
/* Keep the navigation drawer above every card, table and Bootstrap component. */
.sidebar { z-index: 1200 !important; }
.sidebar-toggle { position: fixed; z-index: 1301; top: 15px; left: 272px; width: 42px; height: 42px; border: 2px solid #f7b733; border-radius: 12px; background: #2b1b17; color: #fff; box-shadow: 0 4px 12px rgba(43,27,23,.18); cursor: pointer; font-size: 20px; line-height: 1; }
.sidebar-scrim { display: none; }
.sidebar .sub-menu { display: none; }.sidebar .sub-menu.menu-open { display: block; }.sidebar-dropdown-trigger .fa-chevron-down { margin-left: auto; transition: transform .2s; }.sidebar-dropdown-trigger.is-open .fa-chevron-down { transform: rotate(180deg); }
body.sidebar-collapsed .sidebar { transform: translateX(-100%); } body.sidebar-collapsed .navbar-top { left: 0 !important; width: 100% !important; } body.sidebar-collapsed .main-content { margin-left: 0 !important; } body.sidebar-collapsed .sidebar-toggle { left: 16px; }.navbar-top .container-fluid { padding-left: 78px !important; }
.sidebar { transition: transform .25s ease; }.navbar-top, .main-content { transition: margin-left .25s ease, left .25s ease, width .25s ease; }
@media (max-width: 900px) {
  .sidebar-toggle { top: 10px; left: 12px; }
  .sidebar { transform: translateX(-100%); position: fixed !important; width: min(82vw, 280px) !important; height: 100vh !important; }
  .navbar-top { padding-left: 62px !important; }
  .navbar-top .container-fluid { padding-left: 62px !important; }
  .navbar-top .navbar-brand { max-width: calc(100vw - 150px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .navbar-top .navbar-nav .fw-medium { display: none; }
  .main-content { margin-left: 0 !important; }
  body:not(.sidebar-collapsed) .sidebar { transform: translateX(0); }
  body:not(.sidebar-collapsed) .sidebar-toggle { left: min(calc(82vw - 52px), 228px); }
  body:not(.sidebar-collapsed) .sidebar-scrim { display: block; position: fixed; z-index: 1190; inset: 0; background: rgba(20, 12, 9, .45); }
}
@media (max-width: 600px) {
  .main-content { padding: 92px 14px 24px !important; }
  .main-content > .d-flex.justify-content-between { flex-direction: column; align-items: flex-start !important; gap: 14px; }
  .main-content h1, .main-content h2 { font-size: clamp(1.65rem, 8vw, 2rem); }
  .card-body { padding: 16px; }
  .btn { max-width: 100%; }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var mobileQuery = window.matchMedia('(max-width: 900px)');
  var button = document.createElement('button');
  button.type = 'button'; button.className = 'sidebar-toggle'; button.setAttribute('aria-label', 'Toggle navigation'); button.innerHTML = '&#9776;';
  var scrim = document.createElement('button');
  scrim.type = 'button'; scrim.className = 'sidebar-scrim'; scrim.setAttribute('aria-label', 'Close navigation');
  function closeSidebar() { document.body.classList.add('sidebar-collapsed'); }
  button.addEventListener('click', function () { document.body.classList.toggle('sidebar-collapsed'); });
  scrim.addEventListener('click', closeSidebar);
  document.body.appendChild(button);
  document.body.appendChild(scrim);
  if (mobileQuery.matches) closeSidebar();
  mobileQuery.addEventListener('change', function (event) { if (event.matches) closeSidebar(); });

  document.querySelectorAll('a[href="#categoriesCollapse"], a[href="#itemsCollapse"]').forEach(function (trigger) {
    var menu = document.querySelector(trigger.getAttribute('href'));
    if (!menu) return;
    trigger.classList.add('sidebar-dropdown-trigger');
    if (menu.querySelector('.active')) { menu.classList.add('menu-open'); trigger.classList.add('is-open'); }
    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      menu.classList.toggle('menu-open');
      trigger.classList.toggle('is-open', menu.classList.contains('menu-open'));
    });
  });

  document.querySelectorAll('.sidebar a[href]:not([href^="#"])').forEach(function (link) {
    link.addEventListener('click', function () { if (mobileQuery.matches) closeSidebar(); });
  });
});
</script>
HTML;
}
