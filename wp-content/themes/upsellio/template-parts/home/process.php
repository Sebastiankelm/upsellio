<?php

if (!defined("ABSPATH")) {
    exit;
}

$process_steps = [
    [
        "number" => "01",
        "title" => "Analiza",
        "description" => "Sprawdzam kampanie, stronę, ofertę i jakość leadów, żeby znaleźć realną przyczynę braku sprzedaży.",
        "duration" => "Tydzień 1",
        "deliverable" => "Dostajesz: dokument analizy i priorytety działań.",
        "icon" => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="5.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m15 15 4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    ],
    [
        "number" => "02",
        "title" => "Strategia",
        "description" => "Układam priorytety: co poprawić w reklamach, co uprościć na stronie i jaki komunikat powinien prowadzić do decyzji.",
        "duration" => "Tydzień 2",
        "deliverable" => "Dostajesz: roadmapę z kolejnością wdrożeń.",
        "icon" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 18V6l5 3 5-3 4 2v12l-4-2-5 3-5-3Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>',
    ],
    [
        "number" => "03",
        "title" => "Wdrożenie",
        "description" => "Wdrażam kampanie, treści, sekcje sprzedażowe, CTA i pomiar tak, żeby każdy element pracował na leady.",
        "duration" => "Tydzień 3-4",
        "deliverable" => "Dostajesz: uruchomione kampanie i poprawione strony.",
        "icon" => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M8 20h8m-4-4v4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    ],
    [
        "number" => "04",
        "title" => "Optymalizacja",
        "description" => "Na podstawie danych poprawiam CPL, konwersję strony i jakość rozmów, zamiast tylko dokładać budżet.",
        "duration" => "Stały proces",
        "deliverable" => "Dostajesz: regularny raport z rekomendacjami.",
        "icon" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 17h14M7 14l3-3 3 2 4-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ],
];
?>
<section class="section section-border bg-soft" id="jak-dzialam">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">Jak działam</div>
          <h2 class="h2 reveal d1">Jak wygląda współpraca</h2>
          <p class="body reveal d2" style="margin-top:18px">Najpierw znajdujemy miejsce, w którym marketing traci pieniądze. Dopiero potem wdrażamy kampanie, stronę i optymalizację.</p>
        </div>
        <div class="steps process-timeline reveal d1 section-grid-gap-lg">
          <?php foreach ($process_steps as $process_step) : ?>
            <div class="step process-step">
              <div class="step-node">
                <span class="step-num"><?php echo esc_html((string) $process_step["number"]); ?></span>
                <span class="step-icon"><?php echo $process_step["icon"]; ?></span>
              </div>
              <div class="step-content">
                <div class="step-title"><?php echo esc_html((string) $process_step["title"]); ?></div>
                <div class="step-duration"><?php echo esc_html((string) ($process_step["duration"] ?? "")); ?></div>
                <div class="step-desc"><?php echo esc_html((string) $process_step["description"]); ?></div>
                <div class="step-deliverable"><?php echo esc_html((string) ($process_step["deliverable"] ?? "")); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="section-cta-row reveal d2">
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary btn-sm">Umów bezpłatną rozmowę →</a>
        </div>
      </div>
    </section>

    <section class="section section-border" id="dla-kogo">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">Dla kogo</div>
          <h2 class="h2 reveal d1">Z kim pracuję najlepiej</h2>
        </div>
        <div class="fit-grid">
          <div class="fit-card yes reveal">
            <div class="fit-label">Dobry fit, jeśli:</div>
            <div class="fit-items">
              <div class="fit-item"><span class="fit-icon">✅</span><span>Prowadzisz firmę i chcesz łączyć marketing oraz stronę WWW tak, by wspólnie dowoziły więcej zapytań</span></div>
              <div class="fit-item"><span class="fit-icon">✅</span><span>Chcesz jasnej oferty, wyraźnych CTA i strony, która nie rozprasza</span></div>
              <div class="fit-item"><span class="fit-icon">✅</span><span>Szukasz partnera, który patrzy na marketing i sprzedaż razem</span></div>
              <div class="fit-item"><span class="fit-icon">✅</span><span>Masz ruch lub kampanie, ale czujesz, że strona mogłaby zamieniać więcej odwiedzających w kontakty</span></div>
            </div>
          </div>
          <div class="fit-card no reveal d1">
            <div class="fit-label">Mniejszy fit, jeśli:</div>
            <div class="fit-items">
              <div class="fit-item"><span class="fit-icon">—</span><span>Szukasz tylko najtańszego wykonania bez myślenia o wyniku</span></div>
              <div class="fit-item"><span class="fit-icon">—</span><span>Oczekujesz rozbudowanej agencji z dużym zespołem od wszystkiego</span></div>
              <div class="fit-item"><span class="fit-icon">—</span><span>Nie chcesz rozmawiać o ofercie, kliencie i procesie decyzji</span></div>
              <div class="fit-item"><span class="fit-icon">—</span><span>Zależy Ci tylko na ruchu, a nie na jakości leadów i sprzedaży</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>
