<?php
if (!defined("ABSPATH")) {
    exit;
}
?>
<section class="op-section op-section-soft offer-qualifier" data-offer-section="qualifier">
  <div class="op-wrap">
    <div data-quiz-step="1">
      <h3>W 30 sekund pokaz, co Cie najbardziej blokuje</h3>
      <div class="quiz-options">
        <button type="button" data-quiz-answer="brak-leadow" data-quiz-question="problem">Brak leadow</button>
        <button type="button" data-quiz-answer="drogie-kliki" data-quiz-question="problem">Drogie kliki</button>
        <button type="button" data-quiz-answer="niska-konwersja" data-quiz-question="problem">Niska konwersja</button>
        <button type="button" data-quiz-answer="nowa-strona" data-quiz-question="problem">Nowa strona</button>
      </div>
    </div>
    <div data-quiz-step="2" hidden>
      <h3>Twoja branza</h3>
      <div class="quiz-options">
        <button type="button" data-quiz-answer="ecommerce" data-quiz-question="industry">e-commerce</button>
        <button type="button" data-quiz-answer="b2b-uslugi" data-quiz-question="industry">B2B uslugi</button>
        <button type="button" data-quiz-answer="lokalne-uslugi" data-quiz-question="industry">Lokalne uslugi</button>
        <button type="button" data-quiz-answer="inna" data-quiz-question="industry">Inna</button>
      </div>
    </div>
    <div data-quiz-step="3" hidden>
      <h3>Miesieczny budzet marketingu</h3>
      <div class="quiz-options">
        <button type="button" data-quiz-answer="lt-2k" data-quiz-question="budget">&lt;2k</button>
        <button type="button" data-quiz-answer="2-5k" data-quiz-question="budget">2-5k</button>
        <button type="button" data-quiz-answer="5-10k" data-quiz-question="budget">5-10k</button>
        <button type="button" data-quiz-answer="10k-plus" data-quiz-question="budget">10k+</button>
      </div>
    </div>
    <div data-quiz-result hidden>
      <p data-quiz-summary></p>
      <a class="op-btn op-btn-primary" href="#formularz-oferta" data-cta="quiz-final" data-cta-section="qualifier" data-cta-position="final">Pokaz moja diagnoze</a>
    </div>
  </div>
</section>
