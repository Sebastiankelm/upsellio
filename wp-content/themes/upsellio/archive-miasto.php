<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();
?>
<style>
  .miasta-wrap{width:min(1240px,calc(100% - 32px));margin:0 auto;padding:56px 0}
  .miasta-hero{position:relative;overflow:hidden;padding:64px 0 36px;border-bottom:1px solid var(--border,#e2e8f0);background:radial-gradient(circle at top right, rgba(20,184,166,0.18), transparent 40%), linear-gradient(180deg,#ecfeff,#f1f5f9)}
  .miasta-hero-inner{position:relative;z-index:1;display:grid;gap:30px;align-items:center}
  .miasta-hero-decor{display:none;position:relative}
  .miasta-hero-decor svg{width:100%;max-width:320px;height:auto;display:block;margin-left:auto}
  .miasta-pill{display:inline-flex;align-items:center;gap:8px;margin-bottom:14px;padding:6px 12px;border-radius:999px;background:#fff;border:1px solid #99f6e4;color:#0f766e;font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
  .miasta-pill::before{content:"";width:6px;height:6px;border-radius:50%;background:#0d9488}
  .miasta-title{font-family:Syne,sans-serif;font-size:clamp(32px,5vw,52px);line-height:1.05;letter-spacing:-1px}
  .miasta-lead{max-width:780px;margin-top:16px;color:#334155;font-size:17px;line-height:1.8}
  .miasta-stats{display:flex;flex-wrap:wrap;gap:18px;margin-top:18px;font-size:13px;color:#475569}
  .miasta-stats strong{display:block;font-family:Syne,sans-serif;font-size:22px;color:#0f766e;letter-spacing:-.02em}
  .miasta-grid{margin-top:28px;display:grid;grid-template-columns:1fr;gap:14px}
  .miasta-card{display:flex;gap:14px;align-items:center;padding:16px;border:1px solid #e2e8f0;border-radius:16px;color:#334155;text-decoration:none;background:#fff;transition:.2s ease;box-shadow:0 4px 14px rgba(15,23,42,.04)}
  .miasta-card:hover{border-color:#0d9488;color:#081827;transform:translateY(-3px);box-shadow:0 14px 30px rgba(15,23,42,.08)}
  .miasta-card-icon{flex:0 0 44px;width:44px;height:44px;border-radius:12px;background:#ecfeff;color:#0f766e;display:grid;place-items:center}
  .miasta-card-icon svg{width:22px;height:22px}
  .miasta-card-body{min-width:0}
  .miasta-card-name{display:block;font-weight:800;color:#081827;font-size:15px;letter-spacing:-.01em}
  .miasta-card-region{display:block;margin-top:2px;font-size:12px;color:#64748b;letter-spacing:.04em;text-transform:uppercase;font-weight:700}
  .miasta-empty{padding:18px;border:1px dashed #c9c9c3;border-radius:12px;color:#6f6f67;background:#fff}
  @media(min-width:761px){.miasta-wrap{width:min(1240px,calc(100% - 48px))}.miasta-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(min-width:961px){.miasta-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.miasta-hero-inner{grid-template-columns:1.3fr .7fr}.miasta-hero-decor{display:block}}
</style>
<main>
  <section class="miasta-hero">
    <div class="miasta-wrap">
      <div class="miasta-hero-inner">
        <div>
          <span class="miasta-pill">Obsługa lokalna</span>
          <h1 class="miasta-title">Marketing i strony WWW - miasta w Polsce</h1>
          <p class="miasta-lead">
            Obsługujemy firmy lokalnie i ogólnopolsko. Poniżej znajdziesz podstrony lokalne z ofertą marketingu, kampanii reklamowych i stron WWW dla największych miast w Polsce.
          </p>
          <?php
          $cities_list = get_posts([
              "post_type" => "miasto",
              "numberposts" => 200,
              "post_status" => "publish",
              "orderby" => "title",
              "order" => "ASC",
          ]);
          $voivodeships = [];
          foreach ($cities_list as $cityPostObj) {
              $voi = (string) get_post_meta($cityPostObj->ID, "_upsellio_city_voivodeship", true);
              if ($voi !== "") {
                  $voivodeships[$voi] = true;
              }
          }
          ?>
          <div class="miasta-stats" aria-hidden="true">
            <div><strong><?php echo esc_html(count($cities_list)); ?></strong>miast w bazie</div>
            <div><strong><?php echo esc_html(count($voivodeships)); ?></strong>województw</div>
            <div><strong>3</strong>obszary usług</div>
          </div>
        </div>
        <div class="miasta-hero-decor" aria-hidden="true">
          <svg viewBox="0 0 240 220" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="miasta-grad" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#ecfeff"/>
                <stop offset="100%" stop-color="#fff"/>
              </linearGradient>
            </defs>
            <path d="M40 60 L70 30 L110 28 L140 12 L180 28 L210 50 L218 92 L208 130 L196 158 L172 190 L140 200 L110 196 L78 184 L52 162 L28 130 L24 98 Z" fill="url(#miasta-grad)" stroke="#99f6e4" stroke-width="2"/>
            <circle cx="100" cy="80" r="6" fill="#0d9488"/>
            <circle cx="148" cy="60" r="5" fill="#14b8a6"/>
            <circle cx="170" cy="120" r="5" fill="#14b8a6"/>
            <circle cx="80" cy="140" r="5" fill="#14b8a6"/>
            <circle cx="130" cy="160" r="6" fill="#0d9488"/>
            <circle cx="60" cy="100" r="4" fill="#5eead4"/>
            <circle cx="190" cy="90" r="4" fill="#5eead4"/>
            <circle cx="120" cy="120" r="4" fill="#5eead4"/>
          </svg>
        </div>
      </div>
    </div>
  </section>
  <section class="miasta-wrap">
    <div class="miasta-grid">
    <?php
    if (!empty($cities_list)) :
        foreach ($cities_list as $cityPost) :
            $cityVoi = (string) get_post_meta($cityPost->ID, "_upsellio_city_voivodeship", true);
            ?>
            <a class="miasta-card" href="<?php echo esc_url(get_permalink($cityPost->ID)); ?>">
              <span class="miasta-card-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
              </span>
              <span class="miasta-card-body">
                <span class="miasta-card-name"><?php echo esc_html(get_the_title($cityPost->ID)); ?></span>
                <?php if ($cityVoi !== "") : ?>
                  <span class="miasta-card-region"><?php echo esc_html("woj. " . $cityVoi); ?></span>
                <?php endif; ?>
              </span>
            </a>
            <?php
        endforeach;
    else :
        if (current_user_can("manage_options")) :
            ?>
            <div class="miasta-empty">
              Brak opublikowanych wpisów typu <code>miasto</code>. Seedy służą tylko do bootstrapu/migracji - opublikuj realne wpisy w CMS.
            </div>
            <?php
        endif;
    endif;
    ?>
    </div>
  </section>
</main>
<?php
get_footer();
