# Upsellio Core Plugin Boundaries

Decision after the first `functions.php` extraction: keep presentation and homepage rendering in the theme, but move business logic only after the current conversion changes are stable.

## Stays In Theme
- Homepage templates and `template-parts/home/*`.
- Theme CSS, visual assets, navigation rendering and footer rendering.
- Small view helpers that are only needed by templates.

## Plugin Or MU-Plugin Candidates
- CRM leads, tasks, statuses and form handlers from `inc/crm.php`.
- Technical SEO routes such as sitemap, robots and `llms.txt`.
- Admin tools, server tools and automation screens.
- Custom post types and seeders that must survive a theme change.

## Migration Rule
Extract to `inc/` first, verify hook order and regressions, then move stable domains into an `upsellio-core` plugin or mu-plugin.
