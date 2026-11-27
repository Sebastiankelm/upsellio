<?php
if (!defined("ABSPATH")) {
    exit;
}
?>
<?php
echo function_exists("upsellio_render_unified_footer")
    ? upsellio_render_unified_footer()
    : "";
?>
<style id="upsellio-reference-motion">
:root{--up-ease:cubic-bezier(.2,.7,.2,1);--up-fast:150ms;--up-base:200ms;--up-slow:600ms}
@keyframes ur-section-reveal{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
@keyframes ur-faq-reveal{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}
.ur-reveal-in{animation:ur-section-reveal var(--up-slow) var(--up-ease) both;animation-delay:var(--ur-delay,0ms)}
:is(.usr,#up-ref-main) details[open]>p{animation:ur-faq-reveal 220ms var(--up-ease) both}
:is(.usr,#up-ref-main,.ub,.sp-art,.ct-reference-form) :is(input,textarea):focus-visible{outline:none;border-color:#0bb39c;box-shadow:0 0 0 3px rgba(11,179,156,.18)}
:is(.usr,#up-ref-main,.ub) :is(.usr-btn,.upref-btn,.ub-btn):active{transform:translateY(0)}
:is(.usr,#up-ref-main) :is(.usr-btn,.upref-btn) svg{transition:transform 180ms var(--up-ease)}
:is(.usr,#up-ref-main) :is(.usr-primary,.upref-btn-p):hover svg{transform:translateX(4px)}
@media(prefers-reduced-motion:reduce){.ur-reveal-in,:is(.usr,#up-ref-main) details[open]>p{animation:none!important}:is(.usr,#up-ref-main,.ub) :is(a,button,svg){transition:none!important;transform:none!important}}
</style>
<script>
(function(){if(!('IntersectionObserver' in window)||window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;var selectors='#up-ref-main > section:not(.upref-hero):not(.ur-cta-band), .usr > section:not(.usr-hero), .ub-list';var observer=new IntersectionObserver(function(entries){entries.forEach(function(entry){if(!entry.isIntersecting)return;observer.unobserve(entry.target);entry.target.classList.add('ur-reveal-in')})},{threshold:.1});document.querySelectorAll(selectors).forEach(function(section){var heading=section.querySelector('h2');if(heading&&heading.getBoundingClientRect().top>window.innerHeight)observer.observe(heading);var cards=section.querySelectorAll('.usr-card,.ub-card');cards.forEach(function(card,index){if(card.getBoundingClientRect().top>window.innerHeight){card.style.setProperty('--ur-delay',Math.min(index,5)*80+'ms');observer.observe(card)}})})})();
</script>
<?php wp_footer(); ?>
</body>
</html>

