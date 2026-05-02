<?php

if (!defined("ABSPATH")) {
    exit;
}
function upsellio_admin_hub_slug()
{
    return "upsellio-admin";
}

function upsellio_admin_url($page)
{
    $page = (string) $page;
    $routes = [
        "upsellio-content-tools" => "themes.php?page=upsellio-content-tools",
        "upsellio-theme-config" => "themes.php?page=upsellio-theme-config",
        "upsellio-trust-seo" => "themes.php?page=upsellio-theme-config#trust-seo",
        "upsellio-home-media" => "themes.php?page=upsellio-home-media",
        "upsellio-template-assets" => "themes.php?page=upsellio-template-assets",
        "upsellio-portfolio-seed" => "edit.php?post_type=portfolio&page=upsellio-portfolio-seed",
        "upsellio-marketing-portfolio-seed" => "edit.php?post_type=marketing_portfolio&page=upsellio-marketing-portfolio-seed",
        "upsellio-lead-magnet-seed" => "edit.php?post_type=lead_magnet&page=upsellio-lead-magnet-seed",
        "upsellio-definitions-generator" => "edit.php?post_type=definicja&page=upsellio-definitions-generator",
        "upsellio-seo-generator" => "edit.php?post_type=miasto&page=upsellio-seo-generator",
        "upsellio-seo-blog-tool" => "edit.php?page=upsellio-seo-blog-tool",
        "upsellio-seo-brief" => "edit.php?page=upsellio-seo-brief",
        "upsellio-content-refresh" => "edit.php?page=upsellio-content-refresh",
        "upsellio-site-analytics" => "edit.php?page=upsellio-site-analytics",
        "upsellio-crm-app" => "admin.php?page=upsellio-crm-app-entry",
        "upsellio-logo-tool" => "themes.php?page=upsellio-logo-tool",
        "upsellio-server-files" => "themes.php?page=upsellio-server-files",
        "upsellio-advanced-tests" => "tools.php?page=upsellio-advanced-tests",
        "upsellio-error-logs" => "tools.php?page=upsellio-error-logs",
    ];

    if (isset($routes[$page])) {
        return admin_url($routes[$page]);
    }

    return admin_url("admin.php?page=" . rawurlencode($page));
}

function upsellio_register_admin_hub_menu()
{
    add_menu_page(
        "Upsellio",
        "Upsellio",
        "edit_posts",
        upsellio_admin_hub_slug(),
        "upsellio_render_admin_hub_screen",
        "dashicons-chart-area",
        3
    );

    add_submenu_page(
        upsellio_admin_hub_slug(),
        "Panel Upsellio",
        "Panel",
        "edit_posts",
        upsellio_admin_hub_slug(),
        "upsellio_render_admin_hub_screen"
    );
}
add_action("admin_menu", "upsellio_register_admin_hub_menu", 1);

function upsellio_admin_hub_card($title, $description, $url, $capability = "edit_posts")
{
    if (!current_user_can((string) $capability)) {
        return;
    }
    ?>
    <a href="<?php echo esc_url((string) $url); ?>" style="display:block;text-decoration:none;color:inherit;background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:18px;box-shadow:0 1px 2px rgba(0,0,0,.04);">
      <strong style="display:block;font-size:16px;margin-bottom:6px;"><?php echo esc_html((string) $title); ?></strong>
      <span style="display:block;color:#50575e;line-height:1.55;"><?php echo esc_html((string) $description); ?></span>
    </a>
    <?php
}

function upsellio_admin_hub_section($title, $cards)
{
    ?>
    <section style="margin-top:26px;">
      <h2 style="margin:0 0 12px;"><?php echo esc_html((string) $title); ?></h2>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;max-width:1200px;">
        <?php foreach ((array) $cards as $card) : ?>
          <?php upsellio_admin_hub_card((string) $card[0], (string) $card[1], (string) $card[2], (string) ($card[3] ?? "edit_posts")); ?>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
}

function upsellio_render_admin_hub_screen()
{
    if (!current_user_can("edit_posts")) {
        return;
    }

    ?>
    <div class="wrap">
      <h1>Panel Upsellio</h1>
      <p style="max-width:820px;">Jedno miejsce do narzędzi projektu: treści, generatory, SEO, pliki techniczne, analityka. Operacje sprzedażowe (CRM) są w aplikacji <strong>CRM App</strong> na froncie — wejście: pozycja <strong>CRM App</strong> w podmenu <strong>Upsellio</strong> (lewym pasku).</p>

      <?php
      upsellio_admin_hub_section("Treści i konfiguracja", [
          ["Dodaj treści", "Szybkie linki do tworzenia portfolio, lead magnetów i szablonów stron.", upsellio_admin_url("upsellio-content-tools")],
          ["Konfiguracja dynamiczna", "Eksport/import tekstów motywu oraz treści zarządzanych w panelu.", upsellio_admin_url("upsellio-theme-config"), "manage_options"],
          ["Dane zaufania i schema", "Organizacja, opinie, ceny, social proof, obraz OG i dane do JSON-LD.", upsellio_admin_url("upsellio-trust-seo"), "manage_options"],
          ["Media strony głównej", "Przypisz zdjęcia, screenshoty, avatary, alty i podpisy do sekcji landing page.", upsellio_admin_url("upsellio-home-media"), "manage_options"],
          ["Assety stron", "Centralne zdjęcie założyciela, screenshoty paneli, mockupy i fallbacki OG dla podstron i template'ów.", upsellio_admin_url("upsellio-template-assets"), "manage_options"],
      ]);

      upsellio_admin_hub_section("Bazy treści", [
          ["Portfolio stron", "Lista realizacji stron WWW i landing pages.", admin_url("edit.php?post_type=portfolio")],
          ["Portfolio marketingowe", "Lista case studies kampanii i działań marketingowych.", admin_url("edit.php?post_type=marketing_portfolio")],
          ["Materiały do pobrania", "Lista lead magnetów widocznych na stronie materiałów.", admin_url("edit.php?post_type=lead_magnet")],
          ["Miasta", "Lokalne podstrony usługowe.", admin_url("edit.php?post_type=miasto"), "manage_options"],
          ["Definicje", "Baza definicji marketingowych i SEO.", admin_url("edit.php?post_type=definicja"), "manage_options"],
      ]);

      upsellio_admin_hub_section("Generatory treści", [
          ["Generator portfolio", "Wygeneruj lub odśwież przykładowe wpisy portfolio stron.", upsellio_admin_url("upsellio-portfolio-seed"), "manage_options"],
          ["Generator portfolio marketingowego", "Wygeneruj zestaw case studies marketingowych z KPI.", upsellio_admin_url("upsellio-marketing-portfolio-seed"), "manage_options"],
          ["Generator lead magnetów", "Wgraj przykładowe materiały do pobrania do bazy danych.", upsellio_admin_url("upsellio-lead-magnet-seed"), "manage_options"],
          ["Generator definicji", "Wygeneruj bazę definicji marketingowych i SEO.", upsellio_admin_url("upsellio-definitions-generator"), "manage_options"],
          ["Generator miast", "Wygeneruj lokalne podstrony miast.", upsellio_admin_url("upsellio-seo-generator"), "manage_options"],
      ]);

      upsellio_admin_hub_section("SEO i blog", [
          ["SEO Blog Tool", "Generator i scoring wpisów blogowych.", upsellio_admin_url("upsellio-seo-blog-tool")],
          ["Generator briefu SEO", "Tworzenie briefów pod wpisy i klastry treści.", upsellio_admin_url("upsellio-seo-brief")],
          ["Content Refresh", "Lista wpisów wymagających odświeżenia.", upsellio_admin_url("upsellio-content-refresh")],
          ["Analityka SEO", "Widoki, trendy, pozycje i rekomendacje per URL.", upsellio_admin_url("upsellio-site-analytics")],
      ]);

      upsellio_admin_hub_section("Techniczne", [
          ["Generator logo", "Wgraj logo i wygeneruj warianty używane w motywie.", upsellio_admin_url("upsellio-logo-tool"), "manage_options"],
          ["Pliki serwerowe", "Wygeneruj .htaccess i sprawdź dynamiczne endpointy SEO.", upsellio_admin_url("upsellio-server-files"), "manage_options"],
          ["Zaawansowane testy", "Testy techniczne i eksport wyników.", upsellio_admin_url("upsellio-advanced-tests"), "manage_options"],
          ["Dziennik błędów", "Podgląd błędów frontendowych zapisanych przez motyw.", upsellio_admin_url("upsellio-error-logs"), "manage_options"],
      ]);
      ?>
    </div>
    <?php
}

function upsellio_hide_scattered_admin_tool_submenus()
{
    $items = [
        ["themes.php", "upsellio-content-tools"],
        ["themes.php", "upsellio-theme-config"],
        ["themes.php", "upsellio-home-media"],
        ["themes.php", "upsellio-template-assets"],
        ["themes.php", "upsellio-logo-tool"],
        ["themes.php", "upsellio-server-files"],
        ["themes.php", "upsellio-navigation-sync"],
        ["tools.php", "upsellio-advanced-tests"],
        ["tools.php", "upsellio-error-logs"],
        ["edit.php", "upsellio-seo-blog-tool"],
        ["edit.php", "upsellio-seo-brief"],
        ["edit.php", "upsellio-content-refresh"],
        ["edit.php", "upsellio-site-analytics"],
        ["edit.php?post_type=lead", "upsellio-crm-pipeline"],
        ["edit.php?post_type=lead", "upsellio-crm-sla"],
        ["edit.php?post_type=lead", "upsellio-crm-tasks"],
        ["edit.php?post_type=lead", "upsellio-crm-reports"],
        ["edit.php?post_type=portfolio", "upsellio-portfolio-seed"],
        ["edit.php?post_type=marketing_portfolio", "upsellio-marketing-portfolio-seed"],
        ["edit.php?post_type=lead_magnet", "upsellio-lead-magnet-seed"],
        ["edit.php?post_type=definicja", "upsellio-definitions-generator"],
        ["edit.php?post_type=miasto", "upsellio-seo-generator"],
    ];

    foreach ($items as $item) {
        remove_submenu_page((string) $item[0], (string) $item[1]);
    }
}
add_action("admin_menu", "upsellio_hide_scattered_admin_tool_submenus", 999);

function upsellio_get_server_htaccess_content()
{
    return implode("\n", [
        "<IfModule mod_rewrite.c>",
        "RewriteEngine On",
        "",
        "# Force HTTPS for all requests.",
        "RewriteCond %{HTTPS} !=on",
        "RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]",
        "",
        "# Canonicalize WWW to non-WWW.",
        "RewriteCond %{HTTP_HOST} ^www\\.(.+)$ [NC]",
        "RewriteRule ^ https://%1%{REQUEST_URI} [R=301,L]",
        "",
        "# Route technical SEO endpoints through WordPress.",
        "RewriteRule ^robots\\.txt$ /index.php [L]",
        "RewriteRule ^(sitemap\\.xml|sitemap_index\\.xml|llms\\.txt)$ /index.php [L]",
        "",
        "# WordPress front controller.",
        "RewriteBase /",
        "RewriteRule ^index\\.php$ - [L]",
        "RewriteCond %{REQUEST_FILENAME} !-f",
        "RewriteCond %{REQUEST_FILENAME} !-d",
        "RewriteRule . /index.php [L]",
        "</IfModule>",
        "",
    ]);
}

function upsellio_server_files_tool_menu()
{
    add_submenu_page(
        "themes.php",
        "Pliki serwerowe Upsellio",
        "Pliki serwerowe",
        "manage_options",
        "upsellio-server-files",
        "upsellio_render_server_files_tool_screen",
        90
    );
}
add_action("admin_menu", "upsellio_server_files_tool_menu");

function upsellio_server_file_status($path)
{
    if (!file_exists($path)) {
        return "brak";
    }

    return is_writable($path) ? "istnieje, zapisywalny" : "istnieje, niezapisywalny";
}

function upsellio_render_server_files_tool_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }

    $status = isset($_GET["upsellio_server_files_status"]) ? sanitize_key(wp_unslash($_GET["upsellio_server_files_status"])) : "";
    $root_dir = trailingslashit(ABSPATH);
    $htaccess_path = $root_dir . ".htaccess";
    $static_files = [
        "robots.txt" => $root_dir . "robots.txt",
        "sitemap.xml" => $root_dir . "sitemap.xml",
        "llms.txt" => $root_dir . "llms.txt",
    ];
    ?>
    <div class="wrap">
      <h1>Pliki serwerowe Upsellio</h1>
      <p>Te pliki powinny działać na serwerze, ale nie muszą być ręcznie trzymane w projekcie. Motyw generuje <code>robots.txt</code>, <code>sitemap.xml</code> i <code>llms.txt</code> dynamicznie przez WordPress, a poniższe narzędzie może zapisać wymagany plik <code>.htaccess</code>.</p>

      <?php if ($status === "saved") : ?>
        <div class="notice notice-success"><p>Plik <code>.htaccess</code> został zapisany.</p></div>
      <?php elseif ($status === "removed_static") : ?>
        <div class="notice notice-success"><p>Statyczne pliki SEO zostały usunięte. Endpointy będą obsługiwane dynamicznie przez WordPress.</p></div>
      <?php elseif ($status !== "") : ?>
        <div class="notice notice-error"><p><?php echo esc_html(upsellio_server_files_status_message($status)); ?></p></div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(320px,460px);gap:20px;max-width:1200px;align-items:start;">
        <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:18px;">
          <h2 style="margin-top:0;">Generowany .htaccess</h2>
          <p style="color:#646970;">Zapisz ten plik na serwerze, jeśli środowisko korzysta z Apache/LiteSpeed i obsługuje <code>.htaccess</code>.</p>
          <textarea class="large-text code" rows="18" readonly><?php echo esc_textarea(upsellio_get_server_htaccess_content()); ?></textarea>
          <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" style="margin-top:12px;">
            <input type="hidden" name="action" value="upsellio_save_server_htaccess" />
            <?php wp_nonce_field("upsellio_save_server_htaccess", "upsellio_server_files_nonce"); ?>
            <button type="submit" class="button button-primary">Zapisz .htaccess na serwerze</button>
          </form>
        </div>

        <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:18px;">
          <h2 style="margin-top:0;">Status endpointów</h2>
          <table class="widefat striped">
            <tbody>
              <tr><td><strong>.htaccess</strong></td><td><?php echo esc_html(upsellio_server_file_status($htaccess_path)); ?></td></tr>
              <tr><td><a href="<?php echo esc_url(home_url("/robots.txt")); ?>" target="_blank" rel="noopener noreferrer">robots.txt</a></td><td>generowany dynamicznie</td></tr>
              <tr><td><a href="<?php echo esc_url(home_url("/sitemap.xml")); ?>" target="_blank" rel="noopener noreferrer">sitemap.xml</a></td><td>generowany dynamicznie</td></tr>
              <tr><td><a href="<?php echo esc_url(home_url("/llms.txt")); ?>" target="_blank" rel="noopener noreferrer">llms.txt</a></td><td>generowany dynamicznie</td></tr>
            </tbody>
          </table>

          <h3>Statyczne kopie w katalogu głównym</h3>
          <table class="widefat striped">
            <tbody>
              <?php foreach ($static_files as $filename => $path) : ?>
                <tr><td><code><?php echo esc_html($filename); ?></code></td><td><?php echo file_exists($path) ? "istnieje" : "brak"; ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" style="margin-top:12px;">
            <input type="hidden" name="action" value="upsellio_remove_static_seo_files" />
            <?php wp_nonce_field("upsellio_remove_static_seo_files", "upsellio_server_files_nonce"); ?>
            <button type="submit" class="button">Usuń statyczne robots/sitemap/llms</button>
          </form>
        </div>
      </div>
    </div>
    <?php
}

function upsellio_server_files_redirect($status)
{
    wp_safe_redirect(add_query_arg("upsellio_server_files_status", sanitize_key((string) $status), admin_url("themes.php?page=upsellio-server-files")));
    exit;
}

function upsellio_server_files_status_message($status)
{
    $messages = [
        "bad_nonce" => "Sesja wygasła. Odśwież stronę i spróbuj ponownie.",
        "no_permission" => "Brak uprawnień do zapisu plików serwerowych.",
        "root_not_writable" => "Katalog główny WordPressa nie jest zapisywalny.",
        "htaccess_not_writable" => "Istniejący plik .htaccess nie jest zapisywalny.",
        "save_error" => "Nie udało się zapisać pliku .htaccess.",
        "remove_error" => "Nie udało się usunąć co najmniej jednego statycznego pliku SEO.",
    ];

    return (string) ($messages[$status] ?? "Operacja nie powiodła się.");
}

function upsellio_handle_save_server_htaccess()
{
    if (!current_user_can("manage_options")) {
        upsellio_server_files_redirect("no_permission");
    }
    if (!isset($_POST["upsellio_server_files_nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["upsellio_server_files_nonce"])), "upsellio_save_server_htaccess")) {
        upsellio_server_files_redirect("bad_nonce");
    }

    $root_dir = trailingslashit(ABSPATH);
    $path = $root_dir . ".htaccess";
    if (!is_writable($root_dir)) {
        upsellio_server_files_redirect("root_not_writable");
    }
    if (file_exists($path) && !is_writable($path)) {
        upsellio_server_files_redirect("htaccess_not_writable");
    }

    $saved = file_put_contents($path, upsellio_get_server_htaccess_content(), LOCK_EX);
    upsellio_server_files_redirect($saved === false ? "save_error" : "saved");
}
add_action("admin_post_upsellio_save_server_htaccess", "upsellio_handle_save_server_htaccess");

function upsellio_handle_remove_static_seo_files()
{
    if (!current_user_can("manage_options")) {
        upsellio_server_files_redirect("no_permission");
    }
    if (!isset($_POST["upsellio_server_files_nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["upsellio_server_files_nonce"])), "upsellio_remove_static_seo_files")) {
        upsellio_server_files_redirect("bad_nonce");
    }

    $root_dir = trailingslashit(ABSPATH);
    $filenames = ["robots.txt", "sitemap.xml", "llms.txt"];
    foreach ($filenames as $filename) {
        $path = $root_dir . $filename;
        if (file_exists($path) && (!is_writable($path) || !unlink($path))) {
            upsellio_server_files_redirect("remove_error");
        }
    }

    upsellio_server_files_redirect("removed_static");
}
add_action("admin_post_upsellio_remove_static_seo_files", "upsellio_handle_remove_static_seo_files");
