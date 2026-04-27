<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_portfolio_seed_build_content($project)
{
    $scope_items = "";
    foreach ((array) ($project["scope_list"] ?? []) as $scope_item) {
        $scope_items .= "<li>" . esc_html((string) $scope_item) . "</li>";
    }

    $outcome_items = "";
    foreach ((array) ($project["outcomes"] ?? []) as $outcome_item) {
        $outcome_items .= "<li>" . esc_html((string) $outcome_item) . "</li>";
    }

    $title = esc_html((string) ($project["title"] ?? ""));
    $industry = esc_html((string) ($project["industry"] ?? ""));
    $offer = esc_html((string) ($project["offer"] ?? ""));
    $problem = esc_html((string) ($project["problem"] ?? ""));
    $scope = esc_html((string) ($project["scope"] ?? ""));
    $result = esc_html((string) ($project["result"] ?? ""));

    return <<<HTML
<h2>Cel projektu: {$title}</h2>
<p>Projekt został przygotowany dla branży <strong>{$industry}</strong> z naciskiem na prostszą ścieżkę do kontaktu, lepszą prezentację oferty i wygodniejsze korzystanie ze strony. Głównym celem było skrócenie drogi użytkownika od wejścia na stronę do wysłania zapytania.</p>

<h2>Wyzwanie biznesowe</h2>
<p>{$problem}</p>

<h2>Zakres wdrożenia</h2>
<p>{$scope}</p>
<ul>
{$scope_items}
</ul>

<h2>Efekt i wpływ na sprzedaż</h2>
<p>{$result}</p>
<ul>
{$outcome_items}
</ul>

<h2>Co warto przenieść do podobnego projektu</h2>
<p>Najważniejsze było uporządkowanie komunikacji, pokazanie przewag firmy i doprowadzenie użytkownika do jasnego następnego kroku. Podobne podejście sprawdza się wtedy, gdy oferta wymaga zaufania, konkretnych argumentów i dobrze poprowadzonego kontaktu.</p>

<h2>Dla kogo podobne wdrożenie</h2>
<p>To rozwiązanie jest dobrym kierunkiem dla firm, które oferują <strong>{$offer}</strong> i chcą, aby strona lepiej tłumaczyła wartość usługi oraz szybciej prowadziła użytkownika do rozmowy.</p>
HTML;
}

function upsellio_get_seeded_portfolio_projects()
{
    $shot_1_html = <<<'HTML'
<div class="ups-shot" id="shot-p1">
  <div class="ups-shot-head">
    <strong>Order Controller — Live Preview</strong>
    <div class="ups-shot-tabs">
      <button data-tab="pipeline" class="is-active">Pipeline</button>
      <button data-tab="faktury">Faktury</button>
      <button data-tab="crm">CRM</button>
    </div>
  </div>
  <div class="ups-shot-body">
    <div class="ups-shot-panel" data-panel="pipeline">Lejek: 142 leady • 38 ofert • 19 wygranych (50% close rate)</div>
    <div class="ups-shot-panel is-hidden" data-panel="faktury">Faktury: 97% opłaconych terminowo • 420k PLN MRR</div>
    <div class="ups-shot-panel is-hidden" data-panel="crm">CRM: 23 zadania po follow-upie • 12 okazji > 40k PLN</div>
  </div>
</div>
HTML;
    $shot_1_css = <<<'CSS'
#shot-p1{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p1 .ups-shot-head{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
#shot-p1 .ups-shot-tabs button{border:1px solid #cde5da;background:#fff;padding:6px 10px;border-radius:999px;cursor:pointer}
#shot-p1 .ups-shot-tabs .is-active{background:#0d9488;color:#fff;border-color:#0d9488}
#shot-p1 .ups-shot-body{margin-top:10px;padding:10px;background:#fff;border:1px solid #e5ede8;border-radius:10px}
#shot-p1 .is-hidden{display:none}
CSS;
    $shot_1_js = <<<'JS'
(() => {
  const root = document.getElementById("shot-p1");
  if (!root) return;
  const buttons = root.querySelectorAll("[data-tab]");
  const panels = root.querySelectorAll("[data-panel]");
  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      const tab = button.getAttribute("data-tab");
      buttons.forEach((item) => item.classList.remove("is-active"));
      button.classList.add("is-active");
      panels.forEach((panel) => panel.classList.toggle("is-hidden", panel.getAttribute("data-panel") !== tab));
    });
  });
})();
JS;

    $shot_2_html = <<<'HTML'
<div class="ups-shot" id="shot-p2">
  <strong>Landing Meta Ads — symulator CPL</strong>
  <label>Budżet dzienny: <input type="range" min="100" max="1500" value="450" step="50" id="p2-budget"></label>
  <p>Szacowany CPL: <b id="p2-cpl">47 PLN</b> • Leady / msc: <b id="p2-leads">286</b></p>
</div>
HTML;
    $shot_2_css = <<<'CSS'
#shot-p2{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p2 input{width:100%;margin-top:8px}
CSS;
    $shot_2_js = <<<'JS'
(() => {
  const budget = document.getElementById("p2-budget");
  const cpl = document.getElementById("p2-cpl");
  const leads = document.getElementById("p2-leads");
  if (!budget || !cpl || !leads) return;
  const update = () => {
    const value = Number(budget.value);
    const estimatedCpl = Math.max(28, Math.round(65 - value / 40));
    const estimatedLeads = Math.round((value * 30) / estimatedCpl);
    cpl.textContent = `${estimatedCpl} PLN`;
    leads.textContent = String(estimatedLeads);
  };
  budget.addEventListener("input", update);
  update();
})();
JS;

    $shot_3_html = <<<'HTML'
<div class="ups-shot" id="shot-p3">
  <strong>E-commerce Growth Board</strong>
  <div class="p3-grid">
    <button data-kpi="conv" class="is-active">Konwersja</button>
    <button data-kpi="aov">AOV</button>
    <button data-kpi="roas">ROAS</button>
  </div>
  <div class="p3-value" id="p3-value">2.9% (+0.8 pp)</div>
</div>
HTML;
    $shot_3_css = <<<'CSS'
#shot-p3{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p3 .p3-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:10px 0}
#shot-p3 button{border:1px solid #cde5da;background:#fff;padding:8px;border-radius:10px;cursor:pointer}
#shot-p3 .is-active{background:#0d9488;color:#fff}
#shot-p3 .p3-value{padding:12px;background:#fff;border:1px solid #e5ede8;border-radius:10px;font-weight:700}
CSS;
    $shot_3_js = <<<'JS'
(() => {
  const root = document.getElementById("shot-p3");
  if (!root) return;
  const value = root.querySelector("#p3-value");
  const map = { conv: "2.9% (+0.8 pp)", aov: "314 PLN (+22%)", roas: "6.4 (+1.7)" };
  root.querySelectorAll("button[data-kpi]").forEach((button) => {
    button.addEventListener("click", () => {
      root.querySelectorAll("button[data-kpi]").forEach((item) => item.classList.remove("is-active"));
      button.classList.add("is-active");
      value.textContent = map[button.getAttribute("data-kpi")] || "-";
    });
  });
})();
JS;

    $shot_4_html = <<<'HTML'
<div class="ups-shot" id="shot-p4">
  <strong>Kalkulator wyceny B2B</strong>
  <div class="p4-row">
    <label>Liczba użytkowników <input type="number" id="p4-users" value="12" min="1"></label>
    <label>Moduły <input type="number" id="p4-modules" value="4" min="1"></label>
  </div>
  <p>Szacowany koszt wdrożenia: <b id="p4-total">28 800 PLN</b></p>
</div>
HTML;
    $shot_4_css = <<<'CSS'
#shot-p4{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p4 .p4-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:10px 0}
#shot-p4 input{width:100%;margin-top:6px;padding:8px;border:1px solid #d5e2db;border-radius:8px}
CSS;
    $shot_4_js = <<<'JS'
(() => {
  const users = document.getElementById("p4-users");
  const modules = document.getElementById("p4-modules");
  const total = document.getElementById("p4-total");
  if (!users || !modules || !total) return;
  const update = () => {
    const sum = (Number(users.value) * 900) + (Number(modules.value) * 4500);
    total.textContent = `${sum.toLocaleString("pl-PL")} PLN`;
  };
  users.addEventListener("input", update);
  modules.addEventListener("input", update);
  update();
})();
JS;

    $shot_5_html = <<<'HTML'
<div class="ups-shot" id="shot-p5">
  <strong>Funnel rekrutacyjny — statusy kandydatów</strong>
  <select id="p5-stage">
    <option value="120,74,33,12">Cold → Hire</option>
    <option value="90,63,38,19">Warm → Hire</option>
    <option value="150,82,40,10">Paid Ads → Hire</option>
  </select>
  <div class="p5-bars" id="p5-bars"></div>
</div>
HTML;
    $shot_5_css = <<<'CSS'
#shot-p5{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p5 select{width:100%;margin:10px 0;padding:8px;border:1px solid #d5e2db;border-radius:8px}
#shot-p5 .p5-bars{display:grid;gap:8px}
#shot-p5 .p5-bar{height:28px;background:#e7f5ef;border-radius:8px;overflow:hidden;position:relative}
#shot-p5 .p5-bar > span{display:block;height:100%;background:#0d9488}
#shot-p5 .p5-bar label{position:absolute;left:8px;top:5px;font-size:12px;color:#0f4f40;font-weight:700}
CSS;
    $shot_5_js = <<<'JS'
(() => {
  const select = document.getElementById("p5-stage");
  const bars = document.getElementById("p5-bars");
  if (!select || !bars) return;
  const labels = ["Nowi", "Screening", "Rozmowy", "Hire"];
  const render = () => {
    const values = (select.value || "").split(",").map((item) => Number(item));
    const max = Math.max(...values, 1);
    bars.innerHTML = values.map((value, index) => (
      `<div class="p5-bar"><span style="width:${Math.round((value / max) * 100)}%"></span><label>${labels[index]}: ${value}</label></div>`
    )).join("");
  };
  select.addEventListener("change", render);
  render();
})();
JS;

    $shot_6_html = <<<'HTML'
<div class="ups-shot" id="shot-p6">
  <strong>Lead Flow — krok po kroku</strong>
  <div class="p6-steps">
    <button data-step="1" class="is-active">1. Wejście</button>
    <button data-step="2">2. Oferta</button>
    <button data-step="3">3. Formularz</button>
    <button data-step="4">4. Call</button>
  </div>
  <p id="p6-copy">Użytkownik trafia na sekcję problem-solution i widzi jasną obietnicę.</p>
</div>
HTML;
    $shot_6_css = <<<'CSS'
#shot-p6{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p6 .p6-steps{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0}
#shot-p6 button{border:1px solid #cde5da;background:#fff;padding:6px 10px;border-radius:999px;cursor:pointer}
#shot-p6 .is-active{background:#0d9488;color:#fff}
CSS;
    $shot_6_js = <<<'JS'
(() => {
  const root = document.getElementById("shot-p6");
  if (!root) return;
  const copy = root.querySelector("#p6-copy");
  const map = {
    1: "Użytkownik trafia na sekcję problem-solution i widzi jasną obietnicę.",
    2: "W 12 sekund rozumie ofertę i widzi konkretne korzyści biznesowe.",
    3: "Wypełnia uproszczony formularz z niskim oporem i jasnym CTA.",
    4: "Lead trafia do CRM i ma natychmiastowy follow-up handlowy."
  };
  root.querySelectorAll("[data-step]").forEach((button) => {
    button.addEventListener("click", () => {
      root.querySelectorAll("[data-step]").forEach((item) => item.classList.remove("is-active"));
      button.classList.add("is-active");
      copy.textContent = map[button.getAttribute("data-step")] || "";
    });
  });
})();
JS;

    $shot_7_html = <<<'HTML'
<div class="ups-shot" id="shot-p7">
  <strong>System magazynowy — harmonogram</strong>
  <input type="range" id="p7-slider" min="1" max="5" value="2" />
  <p id="p7-text">Etap 2/5: przyjęcie towaru i skan etykiet.</p>
</div>
HTML;
    $shot_7_css = <<<'CSS'
#shot-p7{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p7 input{width:100%;margin:10px 0}
CSS;
    $shot_7_js = <<<'JS'
(() => {
  const slider = document.getElementById("p7-slider");
  const text = document.getElementById("p7-text");
  if (!slider || !text) return;
  const map = {
    1: "Etap 1/5: awizacja dostawy i planowanie okien czasowych.",
    2: "Etap 2/5: przyjęcie towaru i skan etykiet.",
    3: "Etap 3/5: kompletacja zamówień według priorytetów SLA.",
    4: "Etap 4/5: kontrola jakości i pakowanie.",
    5: "Etap 5/5: wysyłka + automatyczny status do klienta."
  };
  const update = () => { text.textContent = map[slider.value] || ""; };
  slider.addEventListener("input", update);
  update();
})();
JS;

    $shot_8_html = <<<'HTML'
<div class="ups-shot" id="shot-p8">
  <strong>Dashboard SaaS — przełącznik okresu</strong>
  <div class="p8-switch">
    <button data-period="q1" class="is-active">Q1</button>
    <button data-period="q2">Q2</button>
    <button data-period="q3">Q3</button>
  </div>
  <ul id="p8-list"></ul>
</div>
HTML;
    $shot_8_css = <<<'CSS'
#shot-p8{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p8 .p8-switch{display:flex;gap:8px;margin:10px 0}
#shot-p8 button{border:1px solid #cde5da;background:#fff;padding:6px 10px;border-radius:999px;cursor:pointer}
#shot-p8 .is-active{background:#0d9488;color:#fff}
#shot-p8 ul{margin:0;padding-left:18px}
CSS;
    $shot_8_js = <<<'JS'
(() => {
  const root = document.getElementById("shot-p8");
  if (!root) return;
  const list = root.querySelector("#p8-list");
  const map = {
    q1: ["MRR: 112k PLN", "Churn: 3.1%", "Trial->Paid: 19%"],
    q2: ["MRR: 147k PLN", "Churn: 2.4%", "Trial->Paid: 24%"],
    q3: ["MRR: 181k PLN", "Churn: 2.1%", "Trial->Paid: 27%"]
  };
  const render = (period) => {
    list.innerHTML = (map[period] || []).map((row) => `<li>${row}</li>`).join("");
  };
  root.querySelectorAll("[data-period]").forEach((button) => {
    button.addEventListener("click", () => {
      root.querySelectorAll("[data-period]").forEach((item) => item.classList.remove("is-active"));
      button.classList.add("is-active");
      render(button.getAttribute("data-period"));
    });
  });
  render("q1");
})();
JS;

    $shot_9_html = <<<'HTML'
<div class="ups-shot" id="shot-p9">
  <strong>Platforma rezerwacji — obciążenie slotów</strong>
  <input type="date" id="p9-date">
  <p>Dostępność: <b id="p9-availability">72%</b> • Średni czas obsługi: <b id="p9-time">14 min</b></p>
</div>
HTML;
    $shot_9_css = <<<'CSS'
#shot-p9{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p9 input{margin:10px 0;padding:8px;border:1px solid #d5e2db;border-radius:8px}
CSS;
    $shot_9_js = <<<'JS'
(() => {
  const date = document.getElementById("p9-date");
  const availability = document.getElementById("p9-availability");
  const time = document.getElementById("p9-time");
  if (!date || !availability || !time) return;
  const update = () => {
    const day = new Date(date.value || Date.now()).getDate() || 1;
    const load = 55 + (day % 35);
    availability.textContent = `${Math.max(30, 100 - load)}%`;
    time.textContent = `${Math.round(10 + load / 8)} min`;
  };
  date.addEventListener("change", update);
  update();
})();
JS;

    $shot_10_html = <<<'HTML'
<div class="ups-shot" id="shot-p10">
  <strong>Marka osobista — porównanie przed/po</strong>
  <label><input type="checkbox" id="p10-toggle"> Pokaż po wdrożeniu</label>
  <div id="p10-state">Przed: 0.7% CVR • 22 zapytania / mies.</div>
</div>
HTML;
    $shot_10_css = <<<'CSS'
#shot-p10{border:1px solid #dce7e1;border-radius:14px;padding:12px;background:#f8fcfa}
#shot-p10 #p10-state{margin-top:10px;padding:10px;background:#fff;border:1px solid #e5ede8;border-radius:10px;font-weight:700}
CSS;
    $shot_10_js = <<<'JS'
(() => {
  const toggle = document.getElementById("p10-toggle");
  const state = document.getElementById("p10-state");
  if (!toggle || !state) return;
  toggle.addEventListener("change", () => {
    state.textContent = toggle.checked
      ? "Po: 2.6% CVR • 79 zapytań / mies."
      : "Przed: 0.7% CVR • 22 zapytania / mies.";
  });
})();
JS;

    return [
        [
            "slug" => "sklep-internetowy-suplementy-skalowanie",
            "title" => "Sklep internetowy z suplementami — skalowanie sprzedaży i retencji",
            "excerpt" => "E-commerce z naciskiem na LTV, powtarzalne zakupy i stabilny wzrost przychodów z kanału online.",
            "category" => "Sklepy internetowe",
            "type" => "E-commerce",
            "meta" => "WooCommerce · CRO · Retencja",
            "badge" => "Wyróżniony projekt",
            "cta" => "Zobacz case study",
            "image" => "https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=1600&q=80",
            "industry" => "sklepów internetowych z suplementami i produktami zdrowotnymi",
            "offer" => "sprzedaż suplementów, pakietów i subskrypcji w modelu direct-to-consumer",
            "problem" => "Sklep miał duży ruch z reklam i SEO, ale niski udział klientów powracających i słabą marżowość koszyka.",
            "scope" => "Przebudowano kartę produktu, checkout i automatyzacje retencyjne, aby zwiększyć konwersję i częstotliwość zakupów.",
            "result" => "Sklep poprawił rentowność, wzrósł udział zamówień powracających i spadł koszt pozyskania przychodu.",
            "scope_list" => ["Nowa architektura kategorii SEO.", "Upsell bundle i produkty komplementarne.", "Przebudowa checkout pod mobile.", "Scenariusze e-mail/SMS dla retencji."],
            "outcomes" => ["Wzrost średniej wartości koszyka.", "Lepsza konwersja na mobile.", "Stabilny wzrost przychodów m/m."],
            "keywords" => ["sklep internetowy suplementy", "optymalizacja sklepu ecommerce", "zwiększenie konwersji sklepu"],
            "metrics" => ["+0.9 pp CVR", "+24% AOV", "+33% klienci powracający"],
            "interactive_html" => $shot_1_html,
            "interactive_css" => $shot_1_css,
            "interactive_js" => $shot_1_js,
            "is_featured" => true,
        ],
        [
            "slug" => "salon-beauty-landing-meta-ads",
            "title" => "Salon beauty — landing page pod kampanie Meta Ads i zapisy",
            "excerpt" => "Landing pod usługi beauty z flow zapisu online i prekwalifikacją leadów pod zabiegi premium.",
            "category" => "Landing page",
            "type" => "Landing page",
            "meta" => "Beauty · Meta Ads · Konwersja",
            "badge" => "Lead generation",
            "cta" => "Sprawdź wdrożenie",
            "image" => "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1600&q=80",
            "industry" => "salonów beauty i medycyny estetycznej",
            "offer" => "zabiegi premium, pakiety zabiegowe i konsultacje",
            "problem" => "Kampanie przynosiły dużo wiadomości, ale mało rezerwacji z wysoką wartością koszyka.",
            "scope" => "Wdrożono landing z jasnym komunikatem wartości, social proof i formularzem kwalifikującym klientki.",
            "result" => "Wzrosła liczba jakościowych zapisów oraz efektywność budżetu reklamowego.",
            "scope_list" => ["Hero z ofertą pakietową.", "Sekcja before/after i opinie klientek.", "Krótszy formularz zapisu.", "Automatyczne follow-upy do niezakończonych leadów."],
            "outcomes" => ["Niższy CPL.", "Więcej rezerwacji premium.", "Lepsza jakość leadów z reklam."],
            "keywords" => ["landing beauty", "meta ads salon kosmetyczny", "strona pod zapisy na zabiegi"],
            "metrics" => ["-29% CPL", "+48% zapisów", "+21% średni koszyk zabiegowy"],
            "interactive_html" => $shot_2_html,
            "interactive_css" => $shot_2_css,
            "interactive_js" => $shot_2_js,
            "is_featured" => false,
        ],
        [
            "slug" => "butik-odziezowy-ecommerce-cro",
            "title" => "Butik odzieżowy online — redesign pod konwersję i większy koszyk",
            "excerpt" => "Wdrożenie e-commerce dla butiku odzieżowego z naciskiem na UX mobile, lookbook i sprzedaż kolekcji.",
            "category" => "Sklepy internetowe",
            "type" => "E-commerce",
            "meta" => "Butik odzieżowy · UX · CRO",
            "badge" => "Skalowanie sprzedaży",
            "cta" => "Poznaj wyniki",
            "image" => "https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1600&q=80",
            "industry" => "butików odzieżowych i marek fashion",
            "offer" => "sprzedaż kolekcji sezonowych, dropów i bestsellerów online",
            "problem" => "Ruch z Instagrama i reklam nie przekładał się na sprzedaż przez zbyt długą ścieżkę zakupową.",
            "scope" => "Przebudowano listingi, kartę produktu i ścieżkę dodania zestawów stylizacji do koszyka.",
            "result" => "Sklep zwiększył konwersję, a klientki częściej kupowały więcej niż jeden produkt na zamówienie.",
            "scope_list" => ["Nowe listingi kolekcji.", "Karta produktu z rekomendacją rozmiaru.", "Upsell stylizacji i akcesoriów.", "Skrócony checkout dla mobile."],
            "outcomes" => ["Wyższa konwersja z social media.", "Większy średni koszyk.", "Mniej porzuceń checkoutu."],
            "keywords" => ["sklep internetowy butik odzieżowy", "optymalizacja konwersji fashion", "wdrożenie ecommerce moda"],
            "metrics" => ["+1.1 pp CVR", "+27% AOV", "-18% porzucone koszyki"],
            "interactive_html" => $shot_3_html,
            "interactive_css" => $shot_3_css,
            "interactive_js" => $shot_3_js,
            "is_featured" => false,
        ],
        [
            "slug" => "dietetyk-online-kalkulator-oferty",
            "title" => "Dietetyk online — kalkulator planu i automatyzacja ofert",
            "excerpt" => "System dla gabinetu dietetycznego do szybkiej wyceny planu, onboardingu i zamykania leadów.",
            "category" => "Usługowe",
            "type" => "System wewnętrzny",
            "meta" => "Dietetyk · Automatyzacja · CRM",
            "badge" => "Proces sprzedaży",
            "cta" => "Zobacz jak działa",
            "image" => "https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=1600&q=80",
            "industry" => "dietetyków i gabinetów żywieniowych",
            "offer" => "plany dietetyczne, konsultacje online i pakiety opieki",
            "problem" => "Dietetyk tracił czas na ręczne ofertowanie i odpowiadanie na powtarzalne pytania leadów.",
            "scope" => "Wdrożono kalkulator oferty, automatyczne scenariusze wiadomości i uporządkowany CRM konsultacji.",
            "result" => "Skrócono czas od zapytania do sprzedaży i poprawiono jakość onboardingu klienta.",
            "scope_list" => ["Kalkulator pakietów i zakresu opieki.", "Automatyczne szablony odpowiedzi.", "Panel statusów konsultacji.", "Integracja z płatnościami online."],
            "outcomes" => ["Szybsze domykanie zapytań.", "Mniej pracy manualnej.", "Wyższa wartość klienta."],
            "keywords" => ["strona dietetyka online", "automatyzacja gabinet dietetyczny", "pozyskiwanie klientów dietetyk"],
            "metrics" => ["-68% czasu ofertowania", "+26% zamkniętych leadów", "+17% wartość pakietu"],
            "interactive_html" => $shot_4_html,
            "interactive_css" => $shot_4_css,
            "interactive_js" => $shot_4_js,
            "is_featured" => false,
        ],
        [
            "slug" => "salon-beauty-panel-zespolu-i-grafik",
            "title" => "Salon beauty — panel grafiku i obsługi klientek",
            "excerpt" => "Aplikacja webowa dla salonu beauty porządkująca grafik, potwierdzenia i jakość obsługi.",
            "category" => "Aplikacje webowe",
            "type" => "Aplikacja webowa",
            "meta" => "Beauty tech · Workflow · KPI",
            "badge" => "Automatyzacja",
            "cta" => "Zobacz case",
            "image" => "https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=1600&q=80",
            "industry" => "salonów beauty z wieloma stanowiskami i zespołem specjalistek",
            "offer" => "zabiegi kosmetyczne i estetyczne realizowane w modelu grafikowym",
            "problem" => "Salon miał chaos w grafiku i dużą liczbę przesunięć wizyt, co obniżało wykorzystanie zespołu.",
            "scope" => "Stworzono panel harmonogramu, priorytety wizyt i automatyczne przypomnienia dla klientek.",
            "result" => "Salon lepiej wykorzystał grafik i ograniczył straty wynikające z pustych okien.",
            "scope_list" => ["Widok dzienny i tygodniowy grafiku.", "Automatyczne potwierdzenia wizyt.", "Alerty no-show.", "Raport obłożenia stanowisk."],
            "outcomes" => ["Wyższe obłożenie grafiku.", "Mniej odwołanych wizyt.", "Lepsza organizacja pracy recepcji."],
            "keywords" => ["system salon beauty", "aplikacja do grafiku salonu", "automatyzacja rezerwacji beauty"],
            "metrics" => ["+23% obłożenie", "-31% no-show", "-36% pracy recepcji"],
            "interactive_html" => $shot_5_html,
            "interactive_css" => $shot_5_css,
            "interactive_js" => $shot_5_js,
            "is_featured" => false,
        ],
        [
            "slug" => "strona-uslugowa-firma-lokalna-lead-funnel",
            "title" => "Strona usługowa lokalnej firmy z lejkiem zapytań",
            "excerpt" => "Serwis usługowy zaprojektowany pod szybki kontakt, zaufanie i konwersję ruchu lokalnego.",
            "category" => "Usługowe",
            "type" => "Strona firmowa",
            "meta" => "Usługi lokalne · SEO · Lead funnel",
            "badge" => "Konwersja",
            "cta" => "Poznaj architekturę",
            "image" => "https://images.unsplash.com/photo-1487015307662-4e8de7f451f9?auto=format&fit=crop&w=1600&q=80",
            "industry" => "lokalnych firm usługowych",
            "offer" => "usługi realizowane regionalnie z dużym udziałem zapytań telefonicznych i formularzowych",
            "problem" => "Strona była widoczna w Google, ale użytkownicy rzadko przechodzili do kontaktu.",
            "scope" => "Przebudowano strukturę treści i CTA, dodano sekcje zaufania oraz prekwalifikację zapytań.",
            "result" => "Wzrosła liczba wartościowych leadów, a sprzedaż szybciej identyfikowała klientów z potencjałem.",
            "scope_list" => ["Nowy hero z obietnicą efektu.", "Sekcja zakresu usług i cen orientacyjnych.", "Dowody społeczne i FAQ.", "Formularz kontaktowy o niskim oporze."],
            "outcomes" => ["Więcej zapytań miesięcznie.", "Lepsza jakość leadów.", "Wyższy CVR podstron usługowych."],
            "keywords" => ["strona usługowa SEO", "pozyskiwanie klientów lokalnie", "strona firmowa pod zapytania"],
            "metrics" => ["+61% leadów", "+1.5 pp CVR", "-19% bounce rate"],
            "interactive_html" => $shot_6_html,
            "interactive_css" => $shot_6_css,
            "interactive_js" => $shot_6_js,
            "is_featured" => false,
        ],
        [
            "slug" => "ecommerce-home-decor-zarzadzanie-magazynem",
            "title" => "Sklep internetowy home decor — integracja magazynu i logistyki",
            "excerpt" => "System operacyjny łączący e-commerce z magazynem i wysyłkami dla szybszej realizacji zamówień.",
            "category" => "Sklepy internetowe",
            "type" => "System wewnętrzny",
            "meta" => "E-commerce ops · WMS · Logistyka",
            "badge" => "Procesy",
            "cta" => "Sprawdź wdrożenie",
            "image" => "https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1600&q=80",
            "industry" => "sklepów internetowych z asortymentem home decor",
            "offer" => "e-commerce o dużej rotacji produktów i sezonowych pikach sprzedaży",
            "problem" => "Sklep nie nadążał z kompletacją zamówień, a opóźnienia wpływały na opinię i zwroty.",
            "scope" => "Wdrożono panel operacyjny magazynu z priorytetami, kolejkami i monitorowaniem SLA wysyłek.",
            "result" => "Zespół skrócił czas realizacji i ograniczył liczbę reklamacji logistycznych.",
            "scope_list" => ["Priorytety kompletacji wg kanału sprzedaży.", "Widok kolejki wysyłek.", "Alerty opóźnień i braków.", "Raport wydajności magazynu."],
            "outcomes" => ["Szybsza realizacja zamówień.", "Mniej błędów kompletacji.", "Lepsza terminowość dostaw."],
            "keywords" => ["logistyka sklep internetowy", "integracja magazynu ecommerce", "optymalizacja wysyłek"],
            "metrics" => ["-32% czasu realizacji", "-26% reklamacji", "+22% terminowości"],
            "interactive_html" => $shot_7_html,
            "interactive_css" => $shot_7_css,
            "interactive_js" => $shot_7_js,
            "is_featured" => false,
        ],
        [
            "slug" => "butik-odziezowy-dashboard-kpi",
            "title" => "Butik odzieżowy — dashboard KPI sprzedaży i kampanii",
            "excerpt" => "Panel decyzyjny dla właścicielki butiku, łączący dane sprzedaży, marży i kampanii social ads.",
            "category" => "Sklepy internetowe",
            "type" => "Dashboard",
            "meta" => "Fashion · KPI · Marketing",
            "badge" => "Data-driven",
            "cta" => "Zobacz panel",
            "image" => "https://images.unsplash.com/photo-1551281044-8b5bd6fddf8f?auto=format&fit=crop&w=1600&q=80",
            "industry" => "butików odzieżowych sprzedających online",
            "offer" => "marki fashion zarządzające kampaniami Meta i sprzedażą sezonową",
            "problem" => "Decyzje zakupowe i reklamowe były podejmowane bez aktualnego podglądu marży i rotacji kolekcji.",
            "scope" => "Stworzyliśmy dashboard KPI z marżą, ROAS, bestsellerami i rotacją asortymentu.",
            "result" => "Właścicielka podejmuje szybsze decyzje dotyczące budżetu reklam i stanów magazynowych.",
            "scope_list" => ["Integracja danych sprzedaży i ads.", "Widok marży per kolekcja.", "Alerty słabej rotacji.", "Prognoza zapotrzebowania."],
            "outcomes" => ["Lepsza alokacja budżetu.", "Mniej zamrożonego towaru.", "Większa kontrola rentowności."],
            "keywords" => ["dashboard ecommerce fashion", "panel KPI butik", "analityka sprzedaży odzieży online"],
            "metrics" => ["+18% marży", "+1.4 ROAS", "-21% stock dead"],
            "interactive_html" => $shot_8_html,
            "interactive_css" => $shot_8_css,
            "interactive_js" => $shot_8_js,
            "is_featured" => false,
        ],
        [
            "slug" => "dietetyk-platforma-rezerwacji-konsultacji",
            "title" => "Dietetyk — platforma rezerwacji konsultacji i planów",
            "excerpt" => "System zapisów online dla dietetyka z kalendarzem konsultacji i automatyzacją przypomnień.",
            "category" => "Usługowe",
            "type" => "Aplikacja webowa",
            "meta" => "Dietetyk · Rezerwacje · Workflow",
            "badge" => "Automatyzacja",
            "cta" => "Zobacz case study",
            "image" => "https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=1600&q=80",
            "industry" => "dietetyków i gabinetów konsultacyjnych online",
            "offer" => "konsultacje 1:1, pakiety opieki i plany żywieniowe",
            "problem" => "Ręczna obsługa kalendarza generowała pomyłki, a część leadów nie finalizowała rezerwacji.",
            "scope" => "Wdrożono platformę zapisów z automatycznymi przypomnieniami i prostym procesem opłaty konsultacji.",
            "result" => "Poprawiła się konwersja zapytań na płatne konsultacje i punktualność wizyt.",
            "scope_list" => ["Kalendarz dostępności specjalisty.", "Płatność i potwierdzenie online.", "Reminder SMS/e-mail.", "Panel historii klienta."],
            "outcomes" => ["Więcej rezerwacji opłaconych z góry.", "Mniej nieodbytych konsultacji.", "Lepsza organizacja dnia pracy."],
            "keywords" => ["rezerwacje dietetyk online", "system zapisów gabinet dietetyczny", "automatyzacja konsultacji"],
            "metrics" => ["+36% rezerwacji", "-24% no-show", "+28% konsultacji opłaconych"],
            "interactive_html" => $shot_9_html,
            "interactive_css" => $shot_9_css,
            "interactive_js" => $shot_9_js,
            "is_featured" => false,
        ],
        [
            "slug" => "strona-uslugowa-premium-przed-po",
            "title" => "Strona usługowa premium — transformacja konwersji przed/po",
            "excerpt" => "Kompletna przebudowa strony usługowej pod wizerunek premium, SEO i regularne zapytania sprzedażowe.",
            "category" => "Strony firmowe",
            "type" => "Strona ekspercka",
            "meta" => "Usługi premium · SEO · Lead generation",
            "badge" => "Wizerunek + sprzedaż",
            "cta" => "Zobacz realizację",
            "image" => "https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=1600&q=80",
            "industry" => "firm usługowych premium i doradczych",
            "offer" => "usługi wymagające zaufania i dłuższego procesu decyzji",
            "problem" => "Stara strona była estetyczna, ale nie budowała przewagi eksperckiej i nie generowała stabilnych leadów.",
            "scope" => "Przebudowaliśmy komunikację oferty, sekcje case studies i strategiczne CTA pod kontakt handlowy.",
            "result" => "Strona stała się kanałem inbound i regularnie dostarcza jakościowe zapytania.",
            "scope_list" => ["Nowy messaging i układ oferty.", "Sekcje z dowodami i wynikami.", "Treści SEO pod intent komercyjny.", "CTA do rozmowy osadzone kontekstowo."],
            "outcomes" => ["Więcej leadów miesięcznie.", "Lepszy fit klientów.", "Silniejszy positioning marki."],
            "keywords" => ["strona usługowa premium", "projektowanie strony pod zapytania", "seo dla firm usługowych"],
            "metrics" => ["+189% ruchu organicznego", "+63 leady kwartalnie", "2.8% CVR formularza"],
            "interactive_html" => $shot_10_html,
            "interactive_css" => $shot_10_css,
            "interactive_js" => $shot_10_js,
            "is_featured" => false,
        ],
    ];
}

function upsellio_seed_portfolio_projects($force = false)
{
    if (!post_type_exists("portfolio")) {
        return [
            "created" => 0,
            "updated" => 0,
            "message" => "portfolio_post_type_missing",
        ];
    }

    $projects = upsellio_get_seeded_portfolio_projects();
    $created = 0;
    $updated = 0;

    foreach ($projects as $index => $project) {
        $slug = sanitize_title((string) ($project["slug"] ?? ""));
        if ($slug === "") {
            continue;
        }

        $existing_post = get_page_by_path($slug, OBJECT, "portfolio");
        $post_data = [
            "post_type" => "portfolio",
            "post_status" => "publish",
            "post_title" => (string) $project["title"],
            "post_name" => $slug,
            "post_excerpt" => (string) $project["excerpt"],
            "post_content" => upsellio_portfolio_seed_build_content($project),
            "menu_order" => $index,
        ];

        if ($existing_post instanceof WP_Post) {
            if (!$force) {
                continue;
            }
            $post_data["ID"] = (int) $existing_post->ID;
            $post_id = wp_update_post($post_data, true);
            if (!is_wp_error($post_id) && (int) $post_id > 0) {
                $updated++;
            } else {
                continue;
            }
        } else {
            $post_id = wp_insert_post($post_data, true);
            if (!is_wp_error($post_id) && (int) $post_id > 0) {
                $created++;
            } else {
                continue;
            }
        }

        $term_name = (string) ($project["category"] ?? "Realizacje");
        $term = term_exists($term_name, "portfolio_category");
        if (!$term) {
            $term = wp_insert_term($term_name, "portfolio_category");
        }
        if (!is_wp_error($term) && !empty($term["term_id"])) {
            wp_set_object_terms((int) $post_id, [(int) $term["term_id"]], "portfolio_category");
        }

        update_post_meta((int) $post_id, "_ups_port_type", (string) ($project["type"] ?? ""));
        update_post_meta((int) $post_id, "_ups_port_meta", (string) ($project["meta"] ?? ""));
        update_post_meta((int) $post_id, "_ups_port_badge", (string) ($project["badge"] ?? ""));
        update_post_meta((int) $post_id, "_ups_port_cta", (string) ($project["cta"] ?? "Zobacz case study"));
        update_post_meta((int) $post_id, "_ups_port_image", esc_url_raw((string) ($project["image"] ?? "")));
        update_post_meta((int) $post_id, "_ups_port_problem", (string) ($project["problem"] ?? ""));
        update_post_meta((int) $post_id, "_ups_port_scope", (string) ($project["scope"] ?? ""));
        update_post_meta((int) $post_id, "_ups_port_result", (string) ($project["result"] ?? ""));
        update_post_meta((int) $post_id, "_ups_port_metrics", implode("\n", (array) ($project["metrics"] ?? [])));
        update_post_meta((int) $post_id, "_ups_port_featured", !empty($project["is_featured"]) ? "1" : "0");
        update_post_meta((int) $post_id, "_ups_port_custom_html", (string) ($project["interactive_html"] ?? ""));
        update_post_meta((int) $post_id, "_ups_port_custom_css", (string) ($project["interactive_css"] ?? ""));
        update_post_meta((int) $post_id, "_ups_port_custom_js", (string) ($project["interactive_js"] ?? ""));
    }

    return [
        "created" => $created,
        "updated" => $updated,
        "message" => "ok",
    ];
}

function upsellio_get_portfolio_seed_url($force = false)
{
    return add_query_arg([
        "upsellio_seed_portfolio" => 1,
        "force" => $force ? 1 : 0,
        "_upsellio_nonce" => wp_create_nonce("upsellio_seed_portfolio"),
    ], admin_url("edit.php?post_type=portfolio&page=upsellio-portfolio-seed"));
}

function upsellio_handle_portfolio_seed_request()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    if (!isset($_GET["upsellio_seed_portfolio"])) {
        return;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field(wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_seed_portfolio")) {
        return;
    }

    $force = isset($_GET["force"]) && (int) $_GET["force"] === 1;
    $result = upsellio_seed_portfolio_projects($force);
    update_option("upsellio_portfolio_seed_v1_done", "1");

    $redirect_url = add_query_arg([
        "upsellio_portfolio_seed_done" => 1,
        "created" => (int) ($result["created"] ?? 0),
        "updated" => (int) ($result["updated"] ?? 0),
        "msg" => (string) ($result["message"] ?? "ok"),
    ], admin_url("edit.php?post_type=portfolio&page=upsellio-portfolio-seed"));
    wp_safe_redirect($redirect_url);
    exit;
}
add_action("admin_init", "upsellio_handle_portfolio_seed_request");

function upsellio_register_portfolio_seed_menu()
{
    if (!post_type_exists("portfolio")) {
        return;
    }

    add_submenu_page(
        "edit.php?post_type=portfolio",
        "Generator portfolio",
        "Generator portfolio",
        "manage_options",
        "upsellio-portfolio-seed",
        "upsellio_portfolio_seed_screen",
        30
    );
}
add_action("admin_menu", "upsellio_register_portfolio_seed_menu");

function upsellio_portfolio_seed_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>Generator: 10 zaawansowanych wpisów portfolio</h1>
      <p>Tworzy komplet profesjonalnych wpisów portfolio z opisem celu, zakresu prac, efektów oraz interaktywnymi podglądami projektów.</p>
      <p><a class="button button-primary" href="<?php echo esc_url(upsellio_get_portfolio_seed_url(false)); ?>">Wygeneruj brakujące wpisy portfolio</a></p>
      <p><a class="button" href="<?php echo esc_url(upsellio_get_portfolio_seed_url(true)); ?>">Nadpisz i odśwież wszystkie 10 wpisów</a></p>
    </div>
    <?php
}

function upsellio_portfolio_seed_notice()
{
    if (!is_admin() || !isset($_GET["upsellio_portfolio_seed_done"])) {
        return;
    }

    $created = isset($_GET["created"]) ? (int) $_GET["created"] : 0;
    $updated = isset($_GET["updated"]) ? (int) $_GET["updated"] : 0;
    $msg = isset($_GET["msg"]) ? sanitize_text_field(wp_unslash($_GET["msg"])) : "ok";

    if ($msg !== "ok") {
        echo '<div class="notice notice-error"><p>Nie udało się wygenerować wpisów portfolio.</p></div>';
        return;
    }

    echo '<div class="notice notice-success"><p>';
    echo esc_html("Portfolio zaktualizowane. Utworzono: {$created}, zaktualizowano: {$updated}.");
    echo "</p></div>";
}
add_action("admin_notices", "upsellio_portfolio_seed_notice");

// Seeding is now manual-only (bootstrap/migration). Do not auto-run on admin_init.
