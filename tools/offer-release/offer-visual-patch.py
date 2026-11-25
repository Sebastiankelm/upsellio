"""Scoped visual and usability patch for the exact installed 2.0.5 offer release.
Does not change tracking or acceptance contracts or stored commercial terms.
"""
import re

def replace(s, old, new):
    if s.count(old) != 1:
        raise ValueError("Expected exactly one anchor: " + old[:130])
    return s.replace(old, new, 1)

def landing(s):
    s=replace(s,'<body>\n<?php if ($is_preview_mode) : ?>','<body>\n<header class="offer-sticky-header" id="offer-sticky-header">\n<?php if ($is_preview_mode) : ?>')
    s=replace(s,'<div style="position:fixed;top:0;left:0;right:0;background:#f97316;color:#fff;padding:8px;text-align:center;z-index:9999;font-weight:700;">','<div class="offer-preview-banner">')
    s=replace(s,'<style>body{padding-top:40px}</style>','')
    s=replace(s,'</nav>\n\n<div class="w">\n<div id="offer-state-notice"','</nav>\n</header>\n\n<div class="w">\n<div id="offer-state-notice"')
    s=replace(s,'html{scroll-behavior:smooth;scroll-padding-top:132px}\n[id^="sec-"]{scroll-margin-top:132px}','html{scroll-behavior:smooth;scroll-padding-top:var(--offer-header-offset,124px)}')
    s=replace(s,'  var nav=document.querySelector(\'.nav\');\n  var snav=document.querySelector(\'.snav\');\n  return (nav?nav.offsetHeight:0)+(snav?snav.offsetHeight:0)+20;','  var header=document.getElementById("offer-sticky-header");\n  return (header?Math.ceil(header.getBoundingClientRect().height):0)+20;')
    s=replace(s,"window.addEventListener('scroll',updateSnav,{passive:true});\nupdateSnav();", """function syncHeaderOffset(){document.documentElement.style.setProperty('--offer-header-offset',stickyOffset()+'px');updateSnav();}
window.addEventListener('scroll',updateSnav,{passive:true});
window.addEventListener('resize',syncHeaderOffset,{passive:true});
if(window.ResizeObserver){var hdr=document.getElementById('offer-sticky-header');if(hdr)new ResizeObserver(syncHeaderOffset).observe(hdr);}
syncHeaderOffset();""")
    s=replace(s,"snavLinks.forEach(function(l){l.classList.toggle('active',active&&l.dataset.target===active.id);});","snavLinks.forEach(function(l){var on=!!(active&&l.dataset.target===active.id);l.classList.toggle('active',on);if(on)l.setAttribute('aria-current','location');else l.removeAttribute('aria-current');});")
    s=replace(s,"window.scrollTo({top:target,behavior:'smooth'});","window.scrollTo({top:Math.max(0,target),behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'});")
    s,n=re.subn(r'<div class="(snav-link(?: active)?)" data-target="(sec-[a-z]+)" onclick="jumpTo\(\'\2\'\)"><div class="snav-num">(.*?)</div>(.*?)</div>',lambda m: f'<a class="{m[1]}" href="#{m[2]}" data-target="{m[2]}" onclick="jumpTo(\'{m[2]}\');return false;"><span class="snav-num">{m[3]}</span>{m[4]}</a>',s)
    assert n==6,n
    s=replace(s,'<p class="sub">Pełna lista elementów dopasowana do Twojej sytuacji — wybrane pozycje ustaliliśmy podczas rozmowy diagnostycznej.</p>','<p class="sub">Zakres usług i elementy współpracy przedstawione w tej propozycji.</p>')
    s=replace(s,'  <?php $any_scope = !empty($payload["has_google"]) || !empty($payload["has_meta"]) || !empty($payload["has_web"]); ?>','  <?php $any_scope = count(array_filter($scope_grouped)) > 0; $scope_explicit = !empty($scope_items_meta) && is_array($scope_items_meta); ?>')
    a=s.index('    <div class="scope-head"><span>Element zakresu')
    b=s.index('    <?php\n    $extra_scope =',a)
    s=s[:a]+'''    <div class="offer-scope-grid">
    <?php foreach (["google", "meta", "web", "common"] as $group_key) : ?>
      <?php if (empty($scope_grouped[$group_key])) { continue; } ?>
      <section class="offer-scope-card" aria-label="<?php echo esc_attr($scope_group_labels[$group_key]); ?>">
        <h3><span class="offer-scope-dot" aria-hidden="true"></span><?php echo esc_html($scope_group_labels[$group_key]); ?></h3>
        <ul class="offer-scope-list">
          <?php foreach ($scope_grouped[$group_key] as $item) : ?>
          <li><span class="offer-scope-check" aria-hidden="true"><?php echo !empty($item["optional"]) ? "+" : "✓"; ?></span>
            <div><strong><?php echo esc_html((string) ($item["title"] ?? "")); ?></strong>
              <p><?php echo esc_html((string) ($item["desc"] ?? "")); ?></p>
              <div class="offer-scope-meta"><?php echo esc_html((string) ($item["when"] ?? "")); ?>
                <?php if (!empty($item["optional"])) : ?><span>Opcjonalnie</span><?php elseif ($scope_explicit) : ?><span>W cenie</span><?php endif; ?></div>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>
    </div>
    <?php if (!$scope_explicit) : ?><p class="offer-scope-note">Zakres wynika z wybranego szablonu usług. Szczegóły potwierdź z opiekunem.</p><?php endif; ?>
'''+s[b:]
    s=replace(s,'<strong>30-min rozmowa</strong>\n        <p>Zaplanujmy diagnozę zanim ustalimy cenę. Bez zobowiązań.</p>\n        <a href="#sec-cennik" class="btn btn-p" style="width:100%;justify-content:center;padding:13px;" onclick="cta(\'sum_card_minimal\')">Umów rozmowę →</a>','<div class="sc-for">Podsumowanie</div><strong>Wycena do uzgodnienia</strong>\n        <p>Nie zapisano jeszcze ceny. Skontaktuj się z opiekunem, aby ustalić warunki.</p>\n        <button type="button" class="btn btn-p" id="btn-offer-question-summary" style="width:100%;justify-content:center;padding:13px;">Skontaktuj się z opiekunem →</button>')
    # Use one element ID per branch; both branches cannot render together.
    s=replace(s,'<div class="sc-row"><span class="sc-l">Czas trwania</span><span class="sc-r"><?php echo esc_html((string) ($payload["duration"] ?? "")); ?></span></div>','<?php if (trim((string) ($payload["duration"] ?? "")) !== "") : ?><div class="sc-row"><span class="sc-l">Czas trwania</span><span class="sc-r"><?php echo esc_html((string) $payload["duration"]); ?></span></div><?php endif; ?>')
    s=replace(s,'<div class="sc-row"><span class="sc-l">Model rozliczenia</span><span class="sc-r"><?php echo esc_html((string) ($payload["billing"] ?? "")); ?></span></div>','<?php if (trim((string) ($payload["billing"] ?? "")) !== "") : ?><div class="sc-row"><span class="sc-l">Model rozliczenia</span><span class="sc-r"><?php echo esc_html((string) $payload["billing"]); ?></span></div><?php endif; ?>')
    s=replace(s,'href="#pricing-accept-anchor" onclick="cta(\'sum_card\')"','href="<?php echo $has_variants ? "#sec-cennik" : "#pricing-accept-anchor"; ?>" onclick="cta(\'sum_card\')"')
    s=replace(s,"if(price&&target&&price.textContent.trim())target.textContent=price.textContent.trim();","if(target)target.textContent=price&&price.textContent.trim()?price.textContent.trim():'Do uzgodnienia';")
    s=replace(s,'Odpowiadam w ciągu 24h roboczych.','Skontaktuj się z opiekunem, aby ustalić dalsze kroki.')
    s=replace(s,'<span>Odpowiedź w 24h robocze</span>','<span>Warunki potwierdzamy przed rozpoczęciem współpracy</span>')
    # Native keyboard-operable FAQ buttons; preserve original class and toggle.
    s,n=re.subn(r'<div class="fi( open)?"><div class="fq" onclick="faqToggle\(this.parentElement\)">(.*?)</div></div><div class="fa">',lambda m:'<div class="fi'+(m[1] or '')+'"><button type="button" class="fq" aria-expanded="'+('true' if m[1] else 'false')+'" onclick="faqToggle(this.parentElement)">'+m[2]+'</div></button><div class="fa">',s)
    assert n==3,n
    s=replace(s,"window.faqToggle=function(i){var o=i.classList.contains('open');document.querySelectorAll('.fi.open').forEach(function(el){el.classList.remove('open');});if(!o)i.classList.add('open');};","window.faqToggle=function(i){var o=i.classList.contains('open');document.querySelectorAll('.fi').forEach(function(el){var open=el===i&&!o;el.classList.toggle('open',open);var b=el.querySelector('.fq');if(b)b.setAttribute('aria-expanded',String(open));});};")
    css="""
.offer-sticky-header{position:sticky;top:0;z-index:60;background:var(--bg)}
.offer-sticky-header .nav,.offer-sticky-header .snav{position:relative;top:auto}
.offer-preview-banner{background:#b45309;color:#fff;text-align:center;padding:7px 12px;font-size:13px;font-weight:700;line-height:1.4}
.offer-preview-banner a{display:inline-block}
.snav-link:focus-visible,.fq:focus-visible{outline:2px solid var(--teal);outline-offset:-3px}
button.fq{width:100%;font:inherit;font-weight:700;color:inherit;text-align:left;background:transparent;border:0;cursor:pointer}
.brand-logo-nav{min-width:88px;max-width:min(170px,35vw);max-height:38px}
.scope{border:0;background:transparent;overflow:visible;border-radius:0}
.offer-scope-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;align-items:start}
.offer-scope-card{background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:22px}
.offer-scope-card h3{display:flex;align-items:center;gap:10px;font:700 19px var(--font-d);margin:0 0 15px}
.offer-scope-dot{width:9px;height:9px;background:var(--teal);border-radius:50%;flex:none}
.offer-scope-list{list-style:none;padding:0;margin:0}
.offer-scope-list li{display:flex;align-items:flex-start;gap:10px;padding:11px 0;border-top:1px solid var(--border)}
.offer-scope-check{color:var(--teal);font-weight:800;flex:none}
.offer-scope-list strong{font-size:14px;line-height:1.4}
.offer-scope-list p{font-size:13px;line-height:1.45;color:var(--muted);margin:3px 0}
.offer-scope-meta{font-size:11px;color:var(--muted);display:flex;gap:8px;flex-wrap:wrap}
.offer-scope-meta span{color:#8b4a13}
.offer-scope-note{font-size:13px;color:var(--muted);margin:12px 0}
@media(max-width:720px){.offer-scope-grid{grid-template-columns:minmax(0,1fr)}.offer-scope-card{padding:16px}.offer-preview-banner{font-size:11px}.nav-in{gap:8px}.nav-in .btn{padding:9px 13px;font-size:12px}}
"""
    s=replace(s,'</style>\n</head>\n<body>',css+'</style>\n</head>\n<body>')
    return s

def renderer(s):
    s=replace(s,'      $crm_topbar_title = upsellio_crm_page_title($view, $crm_nav_ctx);','''      $crm_topbar_title = upsellio_crm_page_title($view, $crm_nav_ctx);
      if ($view === "offer_analytics") {
          $crm_oa_id = isset($_GET["offer_id"]) && is_scalar($_GET["offer_id"]) ? (int) wp_unslash($_GET["offer_id"]) : 0;
          $crm_topbar_title = $crm_oa_id > 0 && get_post_type($crm_oa_id) === "crm_offer" && current_user_can("edit_post", $crm_oa_id)
              ? "Oferta #" . $crm_oa_id . " — " . get_the_title($crm_oa_id) : "Analityka oferty";
          $crm_breadcrumbs = [["label"=>"CRM","url"=>home_url("/crm-app/")],["label"=>"Sprzedaż","url"=>""],["label"=>"Oferty","url"=>add_query_arg("view","offers",home_url("/crm-app/"))],["label"=>"Analityka oferty","url"=>""]];
      }''')
    a=s.index('            <?php if ($view === "offer_analytics") : ?>')
    b=s.index('            <?php if ($view === "offers" || $view === "deals") : ?>',a)
    t=s[a:b]
    t=replace(t,'<div class="crm-view-tabs" role="navigation"','<nav class="crm-view-tabs offer-analytics-tabs" role="navigation"')
    t=replace(t,'                </div>\n                <?php\n                $oa_action_recommendation','                </nav>\n                <?php\n                $oa_action_recommendation')
    t=replace(t,'<section class="card" style="padding:0;overflow:hidden">','<section class="card offer-analytics-report" style="padding:0;overflow:hidden">')
    t=replace(t,'<h2 style="margin:0;font-size:18px"><?php echo esc_html((string) $oa_offer->post_title); ?></h2>','<h2 style="margin:0;font-size:18px"><?php echo esc_html("Oferta #" . $oa_offer_id . " — " . (string) $oa_offer->post_title); ?></h2>')
    t=replace(t,'$oa_client_name = $oa_client_id > 0 ? get_the_title($oa_client_id) : "—";','''$oa_client_name = $oa_client_id > 0 && get_post_type($oa_client_id) === "crm_client" && current_user_can("edit_post", $oa_client_id) ? get_the_title($oa_client_id) : "—";
                $oa_phone = $oa_client_name !== "—" ? preg_replace('/[^0-9+]/', '', (string) get_post_meta($oa_client_id, "_ups_client_phone", true)) : "";
                $oa_slug = (string) get_post_meta($oa_offer_id, "_ups_offer_public_slug", true);
                $oa_url = $oa_slug !== "" && $oa_offer->post_status === "publish" ? home_url("/oferta/" . rawurlencode($oa_slug) . "/") : "";
                $oa_status = (string) get_post_meta($oa_offer_id, "_ups_offer_status", true);
                $oa_expiry = (int) get_post_meta($oa_offer_id, "_ups_offer_expires_at", true);
                $oa_closed = !in_array($oa_status, ["open","sent"], true) || ($oa_expiry > 0 && $oa_expiry <= time());''')
    t=replace(t,'<?php if (function_exists("upsellio_offer_get_public_url") && $oa_offer->post_status === "publish") : ?>\n                        <a class="btn alt" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url(add_query_arg("preview_mode", "1", upsellio_offer_get_public_url($oa_offer_id))); ?>">Podgląd strony</a>\n                      <?php endif; ?>','''<?php if ($oa_url !== "") : ?>
                        <a class="btn alt" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url(add_query_arg("preview_mode", "1", $oa_url)); ?>">Podgląd strony</a>
                        <button type="button" class="btn alt" data-oa-url="<?php echo esc_attr($oa_url); ?>" onclick="var b=this,u=b.dataset.oaUrl;if(navigator.clipboard){navigator.clipboard.writeText(u).then(function(){b.textContent='Skopiowano';}).catch(function(){window.prompt('Skopiuj adres oferty',u);});}else{window.prompt('Skopiuj adres oferty',u);}">Kopiuj link</button>
                      <?php endif; ?>
                      <?php if ($oa_phone !== "") : ?><a class="btn" href="tel:<?php echo esc_attr($oa_phone); ?>">Zadzwoń</a><?php endif; ?>''')
    t=replace(t,'<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(135px,1fr));border-bottom:1px solid var(--border)">','<div class="offer-analytics-kpis">')
    t=replace(t,'<div style="padding:14px 18px;border-right:1px solid var(--border)">','<div class="offer-analytics-kpi">')
    t=replace(t,'["val" => (string) (int) ($oa["score"] ?? 0) . "/100", "lbl" => __("Score intencji", "upsellio"), "color" => ($oa["score"] ?? 0) >= 70 ? "#0f766e" : (($oa["score"] ?? 0) >= 40 ? "#d97706" : "")],','["val" => !empty($oa["is_hot"]) ? "Gorąca" : "—", "lbl" => __("Sygnał HOT", "upsellio"), "color" => !empty($oa["is_hot"]) ? "#0f766e" : ""],')
    t=replace(t,'$oa_funnel_total = (int) ($oa["session_count"] ?: 1);','$oa_funnel_total = max(0, (int) ($oa["session_count"] ?? 0));')
    t=replace(t,'<?php echo (int) $reached; ?>/<?php echo (int) $oa_funnel_total; ?> <?php esc_html_e("sesji", "upsellio"); ?>','<?php echo $oa_funnel_total > 0 ? esc_html((string) $reached . "/" . (string) $oa_funnel_total . " sesji") : "Brak danych"; ?>')
    t=replace(t,'<?php echo (int) $fpct; ?>%</div>','<?php echo $oa_funnel_total > 0 ? esc_html((string) $fpct . "%") : "—"; ?></div>')
    x=t.index('                      <?php if (!empty($oa["is_hot"])) : ?>')
    y=t.index('                      <div style="background:#f2f2ec;border:1px solid var(--border);border-radius:12px;padding:14px">\n                        <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;',x)
    t=t[:x]+t[y:]
    t=replace(t,'if ($oa_cta_count > 0 && !empty($oa["last_commit"])) {','''if ($oa_closed) {
                          $rec_action = __("Sprawdź status oferty", "upsellio");
                          $rec_reason = __("Oferta jest zamknięta lub wygasła. Nie uruchamiaj automatycznych przypomnień sprzedażowych.", "upsellio");
                      } elseif ((int) ($oa["accept_started_sessions"] ?? 0) > 0 && !empty($oa["last_commit"])) {''')
    t=replace(t,'Kliknął CTA i wybrał pakiet — intencja zakupu bardzo wysoka.','Rozpoczęto akceptację i wybrano pakiet. Potwierdź dalsze kroki z klientem.')
    t=replace(t,'Kliknął CTA ale nie wybrał pakietu — potrzebuje impulsu decyzyjnego.','Zarejestrowano kliknięcie CTA. Samo kliknięcie nie potwierdza decyzji zakupowej.')
    t=replace(t,'Brak aktywności na ofercie — klient może nie dostał linka.','Brak zarejestrowanej aktywności. Sprawdź dostarczenie linku; brak zgody na pomiar również może oznaczać zero danych.')
    t=replace(t,'<?php echo esc_html($rec_reason); ?></div>\n                      </div>','''<?php echo esc_html($rec_reason); ?></div>
                        <div class="offer-analytics-actions" style="margin-top:12px">
                          <?php if ($oa_phone !== "") : ?><a class="btn" href="tel:<?php echo esc_attr($oa_phone); ?>">Zadzwoń</a><?php endif; ?>
                          <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view"=>"tasks","offer_task_id"=>$oa_offer_id],home_url("/crm-app/"))); ?>">Utwórz zadanie</a>
                        </div>
                      </div>''')
    s=s[:a]+t+s[b:]
    # On the standard tasks screen, preselect a permitted offer, without creating a record.
    s=replace(s,'<input class="crm-inp" type="text" name="task_title" placeholder=','<input class="crm-inp" type="text" name="task_title" value="<?php echo esc_attr(isset($_GET["offer_task_id"]) && is_scalar($_GET["offer_task_id"]) && get_post_type((int) $_GET["offer_task_id"]) === "crm_offer" && current_user_can("edit_post", (int) $_GET["offer_task_id"]) ? "Kontakt w sprawie oferty #" . (int) $_GET["offer_task_id"] : ""); ?>" placeholder=')
    s=replace(s,'<option value="0"><?php esc_html_e("Bez deala", "upsellio"); ?></option><?php foreach ($offers as $offer) : ?><option value="<?php echo esc_attr((string) $offer->ID); ?>">','<option value="0"><?php esc_html_e("Bez deala", "upsellio"); ?></option><?php foreach ($offers as $offer) : ?><option value="<?php echo esc_attr((string) $offer->ID); ?>" <?php selected(isset($_GET["offer_task_id"]) && is_scalar($_GET["offer_task_id"]) && current_user_can("edit_post", (int) $_GET["offer_task_id"]) ? (int) $_GET["offer_task_id"] : 0, (int) $offer->ID); ?>>')
    s=replace(s,'    <body class="<?php echo is_readable($redesign_css) ? "ups-crm-redesign" : ""; ?>" data-crm-view="<?php echo esc_attr($view); ?>">','''    <style>
    body[data-crm-view="offer_analytics"] .offer-analytics-tabs{grid-column:1/-1;display:flex!important;flex-direction:row;align-items:center;flex-wrap:wrap;gap:8px;margin:0}
    body[data-crm-view="offer_analytics"] .offer-analytics-tabs a{white-space:nowrap;width:auto;flex:none}
    body[data-crm-view="offer_analytics"] .offer-analytics-report{grid-column:1/-1;min-width:0}
    .offer-analytics-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,150px),1fr));gap:12px;padding:18px 24px;background:#f8faf9;border-bottom:1px solid var(--border)}
    .offer-analytics-kpi{padding:16px;background:var(--surface,#fff);border:1px solid var(--border);border-radius:12px;min-width:0;overflow-wrap:anywhere}
    .offer-analytics-actions{display:flex;flex-wrap:wrap;gap:8px}
    body[data-crm-view="offer_analytics"] .crm-view-heading{grid-column:1/-1}
    @media(max-width:700px){.offer-analytics-kpis{padding:12px;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.offer-analytics-kpi{padding:12px}.offer-analytics-tabs{overflow-x:auto}.offer-analytics-report{overflow-wrap:anywhere}}
    </style>
    <body class="<?php echo is_readable($redesign_css) ? "ups-crm-redesign" : ""; ?>" data-crm-view="<?php echo esc_attr($view); ?>">''')
    # Desktop sidebar is already rendered and permission-aware; restore layout if global CSS hides it.
    s=replace(s,'    .offer-analytics-actions{display:flex;flex-wrap:wrap;gap:8px}', '''    .offer-analytics-actions{display:flex;flex-wrap:wrap;gap:8px}
    @media(min-width:1101px){body[data-crm-view="offer_analytics"] .layout.crm-app-shell{display:flex;flex-direction:row}body[data-crm-view="offer_analytics"] .layout.crm-app-shell>#ups-crm-sidebar{display:flex;flex:0 0 248px;width:248px;min-width:248px;position:relative;transform:none;visibility:visible}body[data-crm-view="offer_analytics"] .layout.crm-app-shell>.crm-main{flex:1;min-width:0}}''')
    # Open the existing task form with the authorized offer preselected, without saving anything.
    s=replace(s,'    </style>\n    <body class=', '''    </style>
    <?php if ($view === "tasks" && isset($_GET["offer_task_id"]) && is_scalar($_GET["offer_task_id"]) && get_post_type((int) $_GET["offer_task_id"]) === "crm_offer" && current_user_can("edit_post", (int) $_GET["offer_task_id"])) : ?>
    <script>window.addEventListener("load",function(){var btn=document.getElementById("ups-open-add-task");if(btn)btn.click();});</script>
    <?php endif; ?>
    <body class=''')
    # Move styles inside the head instead of after it.
    s=replace(s,'    </head>\n    <style>','    <style>')
    s=replace(s,'    <?php endif; ?>\n    <body class=','    <?php endif; ?>\n    </head>\n    <body class=')
    return s

def apply(rel,data):
    s=data.decode("utf-8")
    if rel=="inc/offer-landing.php": return landing(s).encode("utf-8")
    if rel=="inc/crm-app/render.php": return renderer(s).encode("utf-8")
    return data
