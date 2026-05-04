/**
 * Upsellio: scroll reveal ([data-animate]), liczniki ([data-count-to]).
 * Nawigacja (scroll, dropdown, mobile) obsługuje upsellio.js.
 */
(function () {
  const prefersReducedMotion =
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function initScrollReveal() {
    const animatedItems = document.querySelectorAll("[data-animate]");
    if (!animatedItems.length) return;

    if (prefersReducedMotion) {
      animatedItems.forEach((el) => el.classList.add("is-visible"));
      return;
    }

    if (!("IntersectionObserver" in window)) {
      animatedItems.forEach((el) => el.classList.add("is-visible"));
      return;
    }

    const observer = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-visible");
          obs.unobserve(entry.target);
        });
      },
      {
        threshold: 0.14,
        rootMargin: "0px 0px -40px 0px",
      }
    );

    animatedItems.forEach((item) => observer.observe(item));
  }

  function formatCountText(value, target) {
    if (target >= 1000000) return "1M+";
    if (target >= 500000) return "500k+";
    if (target >= 1000) return `${Math.round(value / 1000)}k+`;
    return `${value}+`;
  }

  function initCounters() {
    const counters = document.querySelectorAll("[data-count-to]");
    if (!counters.length) return;

    const runCounter = (el) => {
      const target = Number(el.dataset.countTo || 0);
      const finalText = (el.dataset.countFinal || "").trim();
      const duration = 900;
      const start = performance.now();

      const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(target * eased);

        if (progress >= 1 && finalText) {
          el.textContent = finalText;
          return;
        }

        el.textContent = formatCountText(current, target);

        if (progress < 1) {
          requestAnimationFrame(tick);
        } else if (finalText) {
          el.textContent = finalText;
        }
      };

      if (prefersReducedMotion) {
        el.textContent = finalText || formatCountText(target, target);
        return;
      }

      requestAnimationFrame(tick);
    };

    if (prefersReducedMotion) {
      counters.forEach((el) => runCounter(el));
      return;
    }

    if (!("IntersectionObserver" in window)) {
      counters.forEach((el) => runCounter(el));
      return;
    }

    const counterObserver = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          runCounter(entry.target);
          obs.unobserve(entry.target);
        });
      },
      { threshold: 0.4 }
    );

    counters.forEach((counter) => counterObserver.observe(counter));
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      initScrollReveal();
      initCounters();
    });
  } else {
    initScrollReveal();
    initCounters();
  }
})();
