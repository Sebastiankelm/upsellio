// Home-only interactions extracted from front-page.php.
var upsellioReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var upsellioIntervalRegistry = [];

    function upsellioStartInterval(callback, delay) {
      if (typeof callback !== "function") return null;
      var state = { callback: callback, delay: delay, timer: null };
      function run() {
        if (document.hidden) return;
        callback();
      }
      state.timer = setInterval(run, delay);
      upsellioIntervalRegistry.push(state);
      return state;
    }

    document.addEventListener("visibilitychange", function () {
      if (!upsellioIntervalRegistry.length) return;
      if (document.hidden) {
        upsellioIntervalRegistry.forEach(function (item) {
          if (!item || item.timer === null) return;
          clearInterval(item.timer);
          item.timer = null;
        });
        return;
      }
      upsellioIntervalRegistry.forEach(function (item) {
        if (!item || item.timer !== null) return;
        item.timer = setInterval(function () {
          if (document.hidden) return;
          item.callback();
        }, item.delay);
      });
    });

    (function () {
      var curatedRoot = document.querySelector("[data-home-curated='1']");
      if (!curatedRoot) return;
      document.body.classList.add("js-home-curated");

      var optionalSections = Array.prototype.slice.call(curatedRoot.querySelectorAll(".js-home-optional-section"));
      var toggleButton = document.getElementById("home-structure-toggle-btn");
      var isExpanded = false;

      function setExpanded(nextState, withScroll) {
        isExpanded = !!nextState;
        document.body.classList.toggle("home-all-sections-visible", isExpanded);
        if (toggleButton) {
          toggleButton.setAttribute("aria-expanded", isExpanded ? "true" : "false");
          toggleButton.textContent = isExpanded ? "Pokaz mniej sekcji" : "Pokaz pelny widok strony";
        }
        if (withScroll && toggleButton) {
          var offset = Math.max(0, toggleButton.getBoundingClientRect().top + window.scrollY - 120);
          window.scrollTo({ top: offset, behavior: upsellioReducedMotion ? "auto" : "smooth" });
        }
      }

      if (toggleButton) {
        toggleButton.addEventListener("click", function () {
          setExpanded(!isExpanded, true);
        });
      }

      function ensureSectionVisibleByHash(hash) {
        if (!hash || hash.length < 2) return;
        var target = document.getElementById(hash.replace("#", ""));
        if (!target) return;
        var isOptional = optionalSections.indexOf(target) > -1;
        if (isOptional && !isExpanded) setExpanded(true, false);
      }

      ensureSectionVisibleByHash(window.location.hash);

      document.addEventListener("click", function (event) {
        var anchor = event.target.closest('a[href^="#"]');
        if (!anchor) return;
        ensureSectionVisibleByHash(anchor.getAttribute("href") || "");
      });

      if (!upsellioReducedMotion && !window.matchMedia("(max-width: 980px)").matches) {
        var interactiveCards = Array.prototype.slice.call(document.querySelectorAll(
          ".hero-system-core, .hero-kpi-block, .why-one-panel, .service-meta-panel, .case-panel"
        ));
        interactiveCards.forEach(function (card) {
          card.setAttribute("data-interactive-card", "1");
          card.addEventListener("mousemove", function (event) {
            var rect = card.getBoundingClientRect();
            var cx = rect.left + (rect.width / 2);
            var cy = rect.top + (rect.height / 2);
            var dx = (event.clientX - cx) / rect.width;
            var dy = (event.clientY - cy) / rect.height;
            card.style.setProperty("--interactive-rx", (dx * 4).toFixed(2) + "deg");
            card.style.setProperty("--interactive-ry", (-dy * 4).toFixed(2) + "deg");
          });
          card.addEventListener("mouseleave", function () {
            card.style.setProperty("--interactive-rx", "0deg");
            card.style.setProperty("--interactive-ry", "0deg");
          });
        });
      }
    })();

    (function () {
      var heroSystem = document.getElementById("hero-system");
      if (!heroSystem) return;

      var sparkGroups = Array.prototype.slice.call(heroSystem.querySelectorAll("[data-hero-spark]"));
      var progressBars = Array.prototype.slice.call(heroSystem.querySelectorAll("[data-hero-kpi-progress]"));
      var pipeSteps = Array.prototype.slice.call(heroSystem.querySelectorAll(".hero-pipe-step"));
      var growthGroups = Array.prototype.slice.call(heroSystem.querySelectorAll("[data-hero-growth-line]"));
      var chaosNotes = Array.prototype.slice.call(heroSystem.querySelectorAll(".hero-chaos-note-grid span"));
      var pipeIndex = 0;

      function randomizeSparks() {
        sparkGroups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar) {
            var next = 16 + Math.floor(Math.random() * 70);
            bar.style.height = next + "%";
          });
        });
      }

      function pulseProgress() {
        progressBars.forEach(function (bar) {
          var current = parseInt(bar.style.width || "50", 10);
          if (!isFinite(current)) current = 50;
          var next = Math.max(38, Math.min(92, current + (Math.random() > 0.5 ? 6 : -6)));
          bar.style.width = next + "%";
        });
      }

      function rotatePipeline() {
        if (!pipeSteps.length) return;
        pipeSteps.forEach(function (step) { step.classList.remove("is-active"); });
        pipeSteps[pipeIndex % pipeSteps.length].classList.add("is-active");
        pipeIndex += 1;
      }

      function pulseGrowth() {
        growthGroups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = 20 + (idx * 8);
            var jitter = Math.floor(Math.random() * 15) - 7;
            var next = Math.max(14, Math.min(92, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function pulseChaos() {
        chaosNotes.forEach(function (note) {
          var shift = (Math.random() * 2.4) - 1.2;
          var opacity = 0.78 + (Math.random() * 0.22);
          note.style.transform = "translateY(" + shift.toFixed(2) + "px)";
          note.style.opacity = opacity.toFixed(2);
        });
      }

      randomizeSparks();
      pulseProgress();
      rotatePipeline();
      pulseGrowth();
      pulseChaos();

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(randomizeSparks, 3600);
      upsellioStartInterval(pulseProgress, 4200);
      upsellioStartInterval(rotatePipeline, 3400);
      upsellioStartInterval(pulseGrowth, 3900);
      upsellioStartInterval(pulseChaos, 4300);
    })();

    (function () {
      var webVisual = document.getElementById("service-meta-visual");
      if (!webVisual) return;

      var flowItems = Array.prototype.slice.call(webVisual.querySelectorAll(".web-flow-item"));
      var uxItems = Array.prototype.slice.call(webVisual.querySelectorAll(".web-ux-item"));
      var lineGroups = Array.prototype.slice.call(webVisual.querySelectorAll("[data-web-line]"));
      var funnelBars = Array.prototype.slice.call(webVisual.querySelectorAll("[data-web-funnel-bar]"));
      var resultValues = Array.prototype.slice.call(webVisual.querySelectorAll("[data-web-result-value]"));
      var leadsValue = webVisual.querySelector("[data-web-leads]");
      var cplValue = webVisual.querySelector("[data-web-cpl]");
      var convValue = webVisual.querySelector("[data-web-conv]");
      var roasValue = webVisual.querySelector("[data-web-roas]");
      var flowIndex = 0;
      var uxIndex = 0;
      var baseLeads = 362;
      var baseCpl = 37.21;
      var baseConv = 6.42;
      var baseRoas = 4.87;

      function animateLines(min, max, drift) {
        lineGroups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = min + (idx * drift);
            var jitter = Math.floor(Math.random() * 12) - 6;
            var next = Math.max(min - 4, Math.min(max, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateFlow() {
        if (!flowItems.length) return;
        flowItems.forEach(function (item) { item.classList.remove("is-active"); });
        flowItems[flowIndex % flowItems.length].classList.add("is-active");
        flowIndex += 1;
      }

      function rotateUx() {
        if (!uxItems.length) return;
        uxItems.forEach(function (item) { item.classList.remove("is-active"); });
        uxItems[uxIndex % uxItems.length].classList.add("is-active");
        uxIndex += 1;
      }

      function pulseFunnel() {
        funnelBars.forEach(function (bar, idx) {
          var base = 86 - (idx * 20);
          var jitter = Math.floor(Math.random() * 8) - 4;
          var next = Math.max(12, Math.min(92, base + jitter));
          bar.style.width = next + "%";
        });
      }

      function pulseKpis() {
        var leads = baseLeads + Math.floor(Math.random() * 21) - 9;
        var cpl = baseCpl + ((Math.random() * 1.2) - 0.6);
        var conv = baseConv + ((Math.random() * 0.22) - 0.11);
        var roas = baseRoas + ((Math.random() * 0.34) - 0.17);

        if (leadsValue) leadsValue.textContent = String(Math.max(330, leads));
        if (cplValue) cplValue.textContent = cpl.toFixed(2).replace(".", ",");
        if (convValue) convValue.textContent = conv.toFixed(2).replace(".", ",") + "%";
        if (roasValue) roasValue.textContent = roas.toFixed(2).replace(".", ",");

        if (resultValues.length === 3) {
          resultValues[0].textContent = "+" + String(Math.max(142, 168 + Math.floor(Math.random() * 16) - 7)) + "%";
          resultValues[1].textContent = "-" + String(Math.max(31, 42 + Math.floor(Math.random() * 9) - 4)) + "%";
          resultValues[2].textContent = "+" + String(Math.max(61, 73 + Math.floor(Math.random() * 11) - 5)) + "%";
        }
      }

      rotateFlow();
      rotateUx();
      pulseFunnel();
      pulseKpis();
      animateLines(16, 72, 8);

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(rotateFlow, 3200);
      upsellioStartInterval(rotateUx, 3800);
      upsellioStartInterval(pulseFunnel, 3600);
      upsellioStartInterval(pulseKpis, 4300);
      upsellioStartInterval(function () { animateLines(16, 72, 8); }, 4000);
    })();

    (function () {
      var snapshot = document.querySelector("[data-service-case-snapshot]");
      if (!snapshot) return;

      var bars = Array.prototype.slice.call(snapshot.querySelectorAll("[data-service-case-bar]"));
      var values = Array.prototype.slice.call(snapshot.querySelectorAll("[data-service-case-value]"));

      function pulseSnapshot() {
        bars.forEach(function (bar, idx) {
          var base = 62 + (idx * 5);
          var jitter = Math.floor(Math.random() * 12) - 6;
          var next = Math.max(38, Math.min(92, base + jitter));
          bar.style.width = next + "%";
        });

        if (values.length >= 4) {
          values[0].textContent = String(152 + (Math.floor(Math.random() * 11) - 5));
          values[1].textContent = (4.8 + ((Math.random() * 0.4) - 0.2)).toFixed(1).replace(".", ",") + "%";
          values[2].textContent = String(49 + (Math.floor(Math.random() * 7) - 3)) + " zl";
          values[3].textContent = (5.2 + ((Math.random() * 0.4) - 0.2)).toFixed(1).replace(".", ",");
        }
      }

      pulseSnapshot();
      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;
      upsellioStartInterval(pulseSnapshot, 4200);
    })();

    (function () {
      var whyVisual = document.getElementById("why-trust-visual");
      if (!whyVisual) return;

      var processSteps = Array.prototype.slice.call(whyVisual.querySelectorAll(".why-one-step"));
      var guaranteeItems = Array.prototype.slice.call(whyVisual.querySelectorAll(".why-one-guarantee"));
      var principleItems = Array.prototype.slice.call(whyVisual.querySelectorAll(".why-one-principle"));
      var lineGroups = Array.prototype.slice.call(whyVisual.querySelectorAll("[data-why-one-line]"));
      var revenueGroups = Array.prototype.slice.call(whyVisual.querySelectorAll("[data-why-one-revenue]"));
      var funnelBars = Array.prototype.slice.call(whyVisual.querySelectorAll("[data-why-one-funnel-bar]"));
      var growthValue = whyVisual.querySelector("[data-why-one-growth]");
      var leadsValue = whyVisual.querySelector("[data-why-one-leads]");
      var cplValue = whyVisual.querySelector("[data-why-one-cpl]");
      var convValue = whyVisual.querySelector("[data-why-one-conv]");
      var roasValue = whyVisual.querySelector("[data-why-one-roas]");
      var stepIndex = 0;
      var guaranteeIndex = 0;
      var principleIndex = 0;

      function animateBarGroups(groups, min, max, drift) {
        groups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = min + (idx * drift);
            var jitter = Math.floor(Math.random() * 12) - 6;
            var next = Math.max(min - 4, Math.min(max, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateProcess() {
        if (!processSteps.length) return;
        processSteps.forEach(function (step) { step.classList.remove("is-active"); });
        processSteps[stepIndex % processSteps.length].classList.add("is-active");
        stepIndex += 1;
      }

      function rotateGuarantees() {
        if (!guaranteeItems.length) return;
        guaranteeItems.forEach(function (item) { item.classList.remove("is-active"); });
        guaranteeItems[guaranteeIndex % guaranteeItems.length].classList.add("is-active");
        guaranteeIndex += 1;
      }

      function rotatePrinciples() {
        if (!principleItems.length) return;
        principleItems.forEach(function (item) { item.classList.remove("is-active"); });
        principleItems[principleIndex % principleItems.length].classList.add("is-active");
        principleIndex += 1;
      }

      function pulseFunnel() {
        funnelBars.forEach(function (bar, idx) {
          var base = 86 - (idx * 22);
          var jitter = Math.floor(Math.random() * 8) - 4;
          var next = Math.max(12, Math.min(92, base + jitter));
          bar.style.width = next + "%";
        });
      }

      function pulseWhyKpis() {
        var growth = 68 + Math.floor(Math.random() * 9) - 4;
        var leads = 362 + Math.floor(Math.random() * 21) - 9;
        var cpl = 37.21 + ((Math.random() * 1.2) - 0.6);
        var conv = 6.42 + ((Math.random() * 0.22) - 0.11);
        var roas = 4.87 + ((Math.random() * 0.34) - 0.17);

        if (growthValue) growthValue.textContent = "+" + String(Math.max(58, growth)) + "%";
        if (leadsValue) leadsValue.textContent = String(Math.max(330, leads));
        if (cplValue) cplValue.textContent = cpl.toFixed(2).replace(".", ",");
        if (convValue) convValue.textContent = conv.toFixed(2).replace(".", ",") + "%";
        if (roasValue) roasValue.textContent = roas.toFixed(2).replace(".", ",");
      }

      rotateProcess();
      rotateGuarantees();
      rotatePrinciples();
      pulseFunnel();
      pulseWhyKpis();
      animateBarGroups(lineGroups, 16, 72, 7);
      animateBarGroups(revenueGroups, 18, 92, 9);

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(rotateProcess, 3400);
      upsellioStartInterval(rotateGuarantees, 3800);
      upsellioStartInterval(rotatePrinciples, 4200);
      upsellioStartInterval(pulseFunnel, 3600);
      upsellioStartInterval(pulseWhyKpis, 4400);
      upsellioStartInterval(function () { animateBarGroups(lineGroups, 16, 72, 7); }, 4000);
      upsellioStartInterval(function () { animateBarGroups(revenueGroups, 18, 92, 9); }, 4200);
    })();

    (function () {
      var processVisual = document.getElementById("process-visual");
      if (!processVisual) return;

      var processSteps = Array.prototype.slice.call(processVisual.querySelectorAll(".process-card"));
      var processLines = Array.prototype.slice.call(processVisual.querySelectorAll("[data-process-line]"));
      var impactValues = Array.prototype.slice.call(processVisual.querySelectorAll("[data-process-impact]"));
      var processIndex = 0;
      var impactBase = [62, 38, -27, 41, 55, 73];

      function animateProcessLines() {
        processLines.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = 16 + (idx * 7);
            var jitter = Math.floor(Math.random() * 12) - 6;
            var next = Math.max(12, Math.min(72, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateProcessSteps() {
        if (!processSteps.length) return;
        processSteps.forEach(function (step) { step.classList.remove("is-active"); });
        processSteps[processIndex % processSteps.length].classList.add("is-active");
        processIndex += 1;
      }

      function pulseImpacts() {
        impactValues.forEach(function (item, idx) {
          var base = impactBase[idx] || 0;
          var jitter = Math.floor(Math.random() * 7) - 3;
          var next = base + jitter;
          var sign = next > 0 ? "+" : "";
          item.textContent = sign + String(next) + "%";
        });
      }

      rotateProcessSteps();
      animateProcessLines();
      pulseImpacts();

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(rotateProcessSteps, 3600);
      upsellioStartInterval(animateProcessLines, 4000);
      upsellioStartInterval(pulseImpacts, 4600);
    })();

    (function () {
      var caseVisual = document.getElementById("case-portfolio-visual");
      if (!caseVisual) return;

      var impactBars = Array.prototype.slice.call(caseVisual.querySelectorAll("[data-case-impact-bar]"));
      var afterValues = Array.prototype.slice.call(caseVisual.querySelectorAll("[data-case-after]"));
      var portfolioLines = Array.prototype.slice.call(caseVisual.querySelectorAll("[data-portfolio-line]"));
      var kpiValues = Array.prototype.slice.call(caseVisual.querySelectorAll("[data-portfolio-kpi]"));
      var kpiItems = Array.prototype.slice.call(caseVisual.querySelectorAll(".portfolio-kpi-item"));
      var kpiIndex = 0;

      function animateBars() {
        impactBars.forEach(function (bar, idx) {
          var base = idx === 1 ? 34 : (80 + (idx * 4));
          var jitter = Math.floor(Math.random() * 8) - 4;
          var next = Math.max(24, Math.min(92, base + jitter));
          bar.style.width = next + "%";
        });
      }

      function animateLines() {
        portfolioLines.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = 14 + (idx * 8);
            var jitter = Math.floor(Math.random() * 10) - 5;
            var next = Math.max(10, Math.min(66, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateKpis() {
        if (!kpiItems.length) return;
        kpiItems.forEach(function (item) { item.classList.remove("is-active"); });
        kpiItems[kpiIndex % kpiItems.length].classList.add("is-active");
        kpiIndex += 1;
      }

      function pulseValues() {
        if (afterValues.length >= 4) {
          afterValues[0].textContent = String(162 + (Math.floor(Math.random() * 7) - 3));
          afterValues[1].textContent = String(76 + (Math.floor(Math.random() * 5) - 2)) + " zl";
          afterValues[2].textContent = (2.89 + ((Math.random() * 0.16) - 0.08)).toFixed(2).replace(".", ",") + "%";
          afterValues[3].textContent = String(186000 + (Math.floor(Math.random() * 9000) - 4500)).replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " zl";
        }

        if (kpiValues.length === 4) {
          kpiValues[0].textContent = "+" + String(108 + (Math.floor(Math.random() * 8) - 3)) + "%";
          kpiValues[1].textContent = "-" + String(46 + (Math.floor(Math.random() * 6) - 2)) + "%";
          kpiValues[2].textContent = "+" + String(139 + (Math.floor(Math.random() * 10) - 4)) + "%";
          kpiValues[3].textContent = "+" + String(114 + (Math.floor(Math.random() * 8) - 3)) + "%";
        }
      }

      animateBars();
      animateLines();
      rotateKpis();
      pulseValues();

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(animateBars, 3900);
      upsellioStartInterval(animateLines, 3700);
      upsellioStartInterval(rotateKpis, 3400);
      upsellioStartInterval(pulseValues, 4500);
    })();

    (function () {
      var contactVisual = document.getElementById("contact-strategy-visual");
      if (!contactVisual) return;

      var flowItems = Array.prototype.slice.call(contactVisual.querySelectorAll(".contact-flow-step"));
      var proofItems = Array.prototype.slice.call(contactVisual.querySelectorAll(".contact-proof-item"));
      var metricGroups = Array.prototype.slice.call(contactVisual.querySelectorAll("[data-contact-line]"));
      var flowIndex = 0;
      var proofIndex = 0;

      function animateContactLines() {
        metricGroups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = 16 + (idx * 8);
            var jitter = Math.floor(Math.random() * 12) - 6;
            var next = Math.max(12, Math.min(72, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateFlow() {
        if (!flowItems.length) return;
        flowItems.forEach(function (item) { item.classList.remove("is-active"); });
        flowItems[flowIndex % flowItems.length].classList.add("is-active");
        flowIndex += 1;
      }

      function rotateProofs() {
        if (!proofItems.length) return;
        proofItems.forEach(function (item) { item.classList.remove("is-active"); });
        proofItems[proofIndex % proofItems.length].classList.add("is-active");
        proofIndex += 1;
      }

      rotateFlow();
      rotateProofs();
      animateContactLines();

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(rotateFlow, 3400);
      upsellioStartInterval(rotateProofs, 4200);
      upsellioStartInterval(animateContactLines, 3900);
    })();
