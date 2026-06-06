<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Upsellio CRM — mapa informacji (IA) i metadane widoków.
 * Jedno źródło prawdy dla menu, tytułów i breadcrumbów.
 */

function upsellio_crm_base_url(): string
{
    return home_url("/crm-app/");
}

/**
 * @param array<string, scalar> $args
 */
function upsellio_crm_url(string $view, array $args = []): string
{
    return add_query_arg(array_merge(["view" => $view], $args), upsellio_crm_base_url());
}

/**
 * @return array<string, string>
 */
function upsellio_crm_view_redirects(): array
{
    return [
        "deals" => "offers",
    ];
}

/**
 * @return list<string>
 */
function upsellio_crm_allowed_views(): array
{
    return [
        "dashboard", "inbox", "tasks", "search", "alerts",
        "leads", "account-360", "contact-queue", "pipeline",
        "offers", "deals", "offer_analytics", "clients", "client-edit", "contacts", "services",
        "contracts", "contract-detail", "followups", "template-studio",
        "analytics", "insights", "suggestions", "research",
        "ca-clients", "ca-dashboard", "ca-command-center", "ca-reports", "ca-plan", "ca-library", "ca-accounts", "ca-meta-accounts",
        "settings", "engine", "calendar", "prospecting",
    ];
}

/**
 * @return array<string, array{label:string,icon:string,section:string,title?:string,hidden?:bool}>
 */
function upsellio_crm_view_registry(): array
{
    return [
        "dashboard" => ["label" => "Pulpit", "icon" => "ti-layout-dashboard", "section" => "work", "title" => "Pulpit operacyjny"],
        "inbox" => ["label" => "Skrzynka", "icon" => "ti-mail", "section" => "work", "title" => "Skrzynka ofert"],
        "tasks" => ["label" => "Zadania", "icon" => "ti-checkbox", "section" => "work", "title" => "Zadania"],
        "search" => ["label" => "Szukaj", "icon" => "ti-search", "section" => "work", "title" => "Wyniki wyszukiwania", "hidden" => true],
        "alerts" => ["label" => "Alerty", "icon" => "ti-bell", "section" => "work", "title" => "Alerty", "hidden" => true],
        "leads" => ["label" => "Leady", "icon" => "ti-user-plus", "section" => "sales", "title" => "Leady"],
        "contact-queue" => ["label" => "Do kontaktu", "icon" => "ti-phone", "section" => "sales", "title" => "Kolejka kontaktu", "hidden" => true],
        "pipeline" => ["label" => "Pipeline", "icon" => "ti-git-merge", "section" => "sales", "title" => "Pipeline sprzedaży"],
        "offers" => ["label" => "Oferty", "icon" => "ti-file-description", "section" => "sales", "title" => "Oferty i deale"],
        "deals" => ["label" => "Oferty", "icon" => "ti-file-description", "section" => "sales", "title" => "Oferty", "hidden" => true],
        "offer_analytics" => ["label" => "Lejek ofert", "icon" => "ti-chart-line", "section" => "marketing", "title" => "Lejek ofert", "hidden" => true],
        "clients" => ["label" => "Klienci", "icon" => "ti-building", "section" => "sales", "title" => "Klienci CRM"],
        "client-edit" => ["label" => "Edycja klienta", "icon" => "ti-building", "section" => "sales", "title" => "Edycja klienta", "hidden" => true],
        "contacts" => ["label" => "Kontakty", "icon" => "ti-address-book", "section" => "sales", "title" => "Kontakty", "hidden" => true],
        "services" => ["label" => "Usługi", "icon" => "ti-list", "section" => "sales", "title" => "Katalog usług", "hidden" => true],
        "contracts" => ["label" => "Umowy", "icon" => "ti-writing", "section" => "sales", "title" => "Umowy"],
        "contract-detail" => ["label" => "Umowa", "icon" => "ti-writing", "section" => "sales", "title" => "Szczegóły umowy", "hidden" => true],
        "account-360" => ["label" => "Karta 360°", "icon" => "ti-id", "section" => "sales", "title" => "Karta klienta 360°", "hidden" => true],
        "analytics" => ["label" => "Raporty", "icon" => "ti-chart-bar", "section" => "marketing", "title" => "Raporty marketingowe"],
        "insights" => ["label" => "Insights AI", "icon" => "ti-sparkles", "section" => "ai", "title" => "Insights AI"],
        "suggestions" => ["label" => "Sugestie", "icon" => "ti-bulb", "section" => "ai", "title" => "Sugestie AI"],
        "research" => ["label" => "Research", "icon" => "ti-telescope", "section" => "ai", "title" => "Research słów kluczowych"],
        "ca-clients" => ["label" => "Profile klientów", "icon" => "ti-users", "section" => "audit", "title" => "Audyt — profile klientów"],
        "ca-command-center" => ["label" => "Command Center", "icon" => "ti-layout-grid", "section" => "audit", "title" => "Agency Command Center"],
        "ca-dashboard" => ["label" => "Dashboard", "icon" => "ti-chart-arcs", "section" => "audit", "title" => "Audyt — dashboard klienta"],
        "ca-accounts" => ["label" => "Połączenia Google", "icon" => "ti-key", "section" => "audit", "title" => "Połączenia Google"],
        "ca-meta-accounts" => ["label" => "Połączenia Meta", "icon" => "ti-brand-facebook", "section" => "audit", "title" => "Połączenia Meta Ads"],
        "ca-reports" => ["label" => "Raporty AI", "icon" => "ti-file-text", "section" => "audit", "title" => "Raporty klienta", "hidden" => true],
        "ca-plan" => ["label" => "Plan działań", "icon" => "ti-target-arrow", "section" => "audit", "title" => "Plan AI", "hidden" => true],
        "ca-library" => ["label" => "Biblioteka", "icon" => "ti-archive", "section" => "audit", "title" => "Biblioteka szablonów", "hidden" => true],
        "settings" => ["label" => "Ustawienia", "icon" => "ti-settings", "section" => "system", "title" => "Ustawienia"],
        "engine" => ["label" => "Silnik sprzedaży", "icon" => "ti-engine", "section" => "system", "title" => "Silnik sprzedaży", "hidden" => true],
        "template-studio" => ["label" => "Szablony", "icon" => "ti-template", "section" => "system", "title" => "Generator szablonów", "hidden" => true],
        "followups" => ["label" => "Follow-upy", "icon" => "ti-mail-forward", "section" => "system", "title" => "Szablony follow-up", "hidden" => true],
        "calendar" => ["label" => "Kalendarz", "icon" => "ti-calendar", "section" => "work", "title" => "Kalendarz", "hidden" => true],
        "prospecting" => ["label" => "Prospecting", "icon" => "ti-radar", "section" => "sales", "title" => "Prospecting", "hidden" => true],
    ];
}

/**
 * @return array<string, string>
 */
function upsellio_crm_section_labels(): array
{
    return [
        "work" => __("Praca", "upsellio"),
        "sales" => __("Sprzedaż", "upsellio"),
        "marketing" => __("Marketing", "upsellio"),
        "audit" => __("Audyt agencji", "upsellio"),
        "ai" => __("AI", "upsellio"),
        "system" => __("System", "upsellio"),
    ];
}

/**
 * @return array<string, array{label:string,icon:string,desc?:string}>
 */
function upsellio_crm_analytics_tabs(): array
{
    return [
        "today" => ["label" => __("Dziś", "upsellio"), "icon" => "ti-sun", "desc" => __("Podsumowanie dnia", "upsellio")],
        "marketing" => ["label" => __("Marketing 360°", "upsellio"), "icon" => "ti-chart-dots", "desc" => __("KPI cross-channel", "upsellio")],
        "traffic" => ["label" => __("Ruch", "upsellio"), "icon" => "ti-world", "desc" => __("GA4 i kanały", "upsellio")],
        "seo" => ["label" => __("SEO", "upsellio"), "icon" => "ti-search", "desc" => __("GSC i widoczność", "upsellio")],
        "paid" => ["label" => __("Reklamy", "upsellio"), "icon" => "ti-currency-dollar", "desc" => __("Google / Meta Ads", "upsellio")],
        "sales" => ["label" => __("Lejek CRM", "upsellio"), "icon" => "ti-chart-arrows", "desc" => __("Oferty i leady", "upsellio")],
        "roas" => ["label" => __("ROAS", "upsellio"), "icon" => "ti-trending-up", "desc" => __("Zwrot z reklam", "upsellio")],
    ];
}

/**
 * Pozycje menu bocznego (kolejność i widoczność).
 *
 * @return list<array{type:string,view?:string,args?:array<string,scalar>,badge?:string}>
 */
function upsellio_crm_sidebar_items(): array
{
    return [
        ["type" => "section", "id" => "work"],
        ["type" => "link", "view" => "dashboard"],
        ["type" => "link", "view" => "inbox", "badge" => "inbox"],
        ["type" => "link", "view" => "tasks", "badge" => "tasks"],
        ["type" => "section", "id" => "sales"],
        ["type" => "link", "view" => "leads"],
        ["type" => "link", "view" => "pipeline"],
        ["type" => "link", "view" => "offers"],
        ["type" => "link", "view" => "clients"],
        ["type" => "link", "view" => "contracts"],
        ["type" => "section", "id" => "marketing"],
        ["type" => "link", "view" => "analytics", "args" => ["atab" => "today"]],
        ["type" => "section", "id" => "audit"],
        ["type" => "link", "view" => "ca-clients", "badge" => "audit_clients"],
        ["type" => "link", "view" => "ca-command-center"],
        ["type" => "link", "view" => "ca-dashboard"],
        ["type" => "link", "view" => "ca-accounts"],
        ["type" => "link", "view" => "ca-meta-accounts"],
        ["type" => "section", "id" => "ai"],
        ["type" => "link", "view" => "insights"],
        ["type" => "link", "view" => "suggestions", "args" => ["suggestions_tab" => "seo"]],
        ["type" => "link", "view" => "research", "args" => ["research_tab" => "keywords"]],
    ];
}

/**
 * Czy link menu jest aktywny.
 */
function upsellio_crm_nav_is_active(string $nav_view, string $current_view, array $nav_args = []): bool
{
    if ($nav_view === "offers" && in_array($current_view, ["offers", "deals", "offer_analytics"], true)) {
        return true;
    }
    if ($nav_view === "clients" && in_array($current_view, ["clients", "client-edit", "contacts", "account-360"], true)) {
        return true;
    }
    if ($nav_view === "analytics" && $current_view === "analytics") {
        return true;
    }
    if ($nav_view === "ca-clients" && in_array($current_view, ["ca-clients", "ca-dashboard", "ca-command-center", "ca-reports", "ca-plan", "ca-library"], true)) {
        return true;
    }
    if ($nav_view === "ca-command-center" && $current_view === "ca-command-center") {
        return true;
    }
    if ($nav_view === "suggestions" && $current_view === "suggestions") {
        return true;
    }
    if ($nav_view === "research" && $current_view === "research") {
        return true;
    }

    return $nav_view === $current_view;
}

/**
 * @return list<array{label:string,url?:string}>
 */
function upsellio_crm_breadcrumbs(string $view, array $ctx = []): array
{
    $registry = upsellio_crm_view_registry();
    $sections = upsellio_crm_section_labels();
    $meta = $registry[$view] ?? null;
    $crumbs = [
        ["label" => "CRM", "url" => upsellio_crm_url("dashboard")],
    ];

    if ($meta && !empty($meta["section"]) && isset($sections[$meta["section"]])) {
        $crumbs[] = ["label" => $sections[$meta["section"]]];
    }

    if ($view === "analytics") {
        $atab = sanitize_key((string) ($ctx["atab"] ?? "today"));
        $tabs = upsellio_crm_analytics_tabs();
        $crumbs[] = ["label" => __("Raporty", "upsellio"), "url" => upsellio_crm_url("analytics", ["atab" => "today"])];
        if (isset($tabs[$atab])) {
            $crumbs[] = ["label" => $tabs[$atab]["label"]];
        }

        return $crumbs;
    }

    if ($view === "settings") {
        $tab = sanitize_key((string) ($ctx["settings_tab"] ?? "general"));
        $labels = [
            "general" => __("Główne", "upsellio"),
            "email" => __("Poczta", "upsellio"),
            "ai" => __("AI", "upsellio"),
            "pipeline" => __("Pipeline", "upsellio"),
            "templates" => __("Szablony", "upsellio"),
        ];
        $crumbs[] = ["label" => __("Ustawienia", "upsellio"), "url" => upsellio_crm_url("settings", ["settings_tab" => "general"])];
        if (isset($labels[$tab])) {
            $crumbs[] = ["label" => $labels[$tab]];
        }

        return $crumbs;
    }

    if ($view === "suggestions") {
        $tab = sanitize_key((string) ($ctx["suggestions_tab"] ?? "seo"));
        $labels = ["seo" => "SEO", "blog" => "Blog", "ads" => "Ads", "keywords" => __("Słowa kluczowe", "upsellio")];
        $crumbs[] = ["label" => __("Sugestie AI", "upsellio"), "url" => upsellio_crm_url("suggestions", ["suggestions_tab" => "seo"])];
        if (isset($labels[$tab])) {
            $crumbs[] = ["label" => $labels[$tab]];
        }

        return $crumbs;
    }

    if (in_array($view, ["ca-dashboard", "ca-reports", "ca-plan"], true)) {
        $crumbs[] = ["label" => __("Audyt agencji", "upsellio")];
        $crumbs[] = ["label" => __("Profile klientów", "upsellio"), "url" => upsellio_crm_url("ca-clients")];
        $cid = (int) ($ctx["cid"] ?? 0);
        if ($cid > 0) {
            $name = get_the_title($cid);
            if ($name !== "") {
                $crumbs[] = [
                    "label" => $name,
                    "url" => upsellio_crm_url("ca-dashboard", ["cid" => $cid]),
                ];
            }
        }
        $crumbs[] = ["label" => (string) ($meta["title"] ?? $meta["label"] ?? $view)];

        return $crumbs;
    }

    $title = (string) ($meta["title"] ?? $meta["label"] ?? "CRM");
    $crumbs[] = ["label" => $title];

    return $crumbs;
}

/**
 * Tytuł paska górnego.
 */
function upsellio_crm_page_title(string $view, array $ctx = []): string
{
    $registry = upsellio_crm_view_registry();

    if ($view === "analytics") {
        $atab = sanitize_key((string) ($ctx["atab"] ?? "today"));
        $tabs = upsellio_crm_analytics_tabs();

        return isset($tabs[$atab])
            ? __("Raporty", "upsellio") . " · " . $tabs[$atab]["label"]
            : __("Raporty", "upsellio");
    }

    if ($view === "settings") {
        $tab = sanitize_key((string) ($ctx["settings_tab"] ?? "general"));
        $labels = [
            "general" => __("Główne", "upsellio"),
            "email" => __("Poczta", "upsellio"),
            "ai" => __("AI", "upsellio"),
            "pipeline" => __("Pipeline", "upsellio"),
            "templates" => __("Szablony", "upsellio"),
        ];

        return isset($labels[$tab])
            ? __("Ustawienia", "upsellio") . " · " . $labels[$tab]
            : __("Ustawienia", "upsellio");
    }

    if ($view === "suggestions") {
        $tab = sanitize_key((string) ($ctx["suggestions_tab"] ?? "seo"));
        $labels = ["seo" => "SEO", "blog" => "Blog", "ads" => "Google Ads", "keywords" => __("Słowa kluczowe", "upsellio")];

        return isset($labels[$tab])
            ? __("Sugestie AI", "upsellio") . " · " . $labels[$tab]
            : __("Sugestie AI", "upsellio");
    }

    if ($view === "ca-dashboard" && !empty($ctx["cid"])) {
        $name = get_the_title((int) $ctx["cid"]);

        return $name !== ""
            ? __("Audyt", "upsellio") . " · " . $name
            : __("Panel klienta", "upsellio");
    }

    $meta = $registry[$view] ?? null;

    return $meta ? (string) ($meta["title"] ?? $meta["label"]) : "CRM";
}
