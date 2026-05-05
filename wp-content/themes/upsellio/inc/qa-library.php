<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_get_qa_library()
{
    $defaults = [
        "Jaki jest miesięczny budżet reklamowy (bez fee)?|Pozwala dobrać realny mix kanałów i tempo testów.",
        "Kiedy chcesz wystartować?|To wpływa na priorytety wdrożenia i onboarding.",
        "Jakie kanały działały wcześniej i z jakim wynikiem?|Unikamy powtarzania kosztownych błędów.",
        "Jaki jest główny KPI na najbliższe 90 dni?|Leady, SQL, sprzedaż, ROAS lub MRR.",
        "Kto podejmuje finalną decyzję i w jakim procesie?|Pomaga zaplanować komunikację i timeline decyzji.",
        "Jakie są największe obawy przed startem?|Często cena, zasoby, timing lub doświadczenia z agencjami.",
    ];
    $stored = get_option("ups_offer_qa_library", []);
    if (!is_array($stored) || empty($stored)) {
        return $defaults;
    }
    $clean = [];
    foreach ($stored as $row) {
        $row = sanitize_text_field((string) $row);
        if ($row !== "") {
            $clean[] = $row;
        }
    }
    return !empty($clean) ? $clean : $defaults;
}

function upsellio_render_qa_picker($target_id = "fld_offer_questions_raw")
{
    $target_id = sanitize_html_class((string) $target_id);
    $items = upsellio_get_qa_library();
    ?>
    <div class="qa-picker" data-qa-target="<?php echo esc_attr($target_id); ?>">
      <label><strong>Biblioteka Q&A</strong></label>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <select class="qa-picker-select" style="min-width:320px">
          <option value="">Wybierz gotowe pytanie...</option>
          <?php foreach ($items as $item) : ?>
            <option value="<?php echo esc_attr((string) $item); ?>"><?php echo esc_html((string) $item); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="btn alt qa-picker-add">Dodaj do pola</button>
      </div>
    </div>
    <script>
    (function(){
      if(window.__upsQaPickerInit){return;}
      window.__upsQaPickerInit=true;
      document.addEventListener('click',function(e){
        var btn=e.target.closest('.qa-picker-add');
        if(!btn)return;
        var wrap=btn.closest('.qa-picker');
        if(!wrap)return;
        var sel=wrap.querySelector('.qa-picker-select');
        if(!sel||!sel.value)return;
        var target=document.getElementById(wrap.getAttribute('data-qa-target'));
        if(!target)return;
        var next=String(sel.value||'').trim();
        if(!next)return;
        var current=String(target.value||'').trim();
        target.value=current?current+"\n"+next:next;
        target.dispatchEvent(new Event('change',{bubbles:true}));
      });
    })();
    </script>
    <?php
}
