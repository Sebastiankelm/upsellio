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


/* Upsellio mobile contact UI, shared across public theme pages. */
(function () {
  "use strict";
  function init() {
    const nav = document.querySelector("header.nav.site-nav");
    if (!nav || document.getElementById("up-contact-bar") || typeof HTMLDialogElement === "undefined") return;
    const mobile = matchMedia("(max-width: 979px)");
    const oldBar = document.querySelector(".mobile-sticky-cta[href^='tel:']");
    const telLink = oldBar || document.querySelector("footer a[href^='tel:'], main a[href^='tel:']");
    if (!telLink) return;
    const tel = telLink.getAttribute("href");
    if (!/^tel:\+?[0-9 ()-]+$/.test(tel)) return;
    const phone = tel.slice(4).replace(/[^+\d]/g, "");
    const diag = nav.querySelector("a.nav-cta")?.getAttribute("href") || "/kontakt/";
    const email = "kontakt@upsellio.pl";
    const mail = "mailto:" + email + "?subject=" + encodeURIComponent("Bezpłatna diagnoza — upsellio.pl") + "&body=" + encodeURIComponent("Dzień dobry,\nmoja firma / sklep: ");
    const icon = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3.1 5.2 2 2 0 0 1 5.1 3h3a2 2 0 0 1 2 1.7l.5 2.8a2 2 0 0 1-.6 2.1L8.7 11a16 16 0 0 0 4.3 4.3l1.4-1.3a2 2 0 0 1 2.1-.6l2.8.5A2 2 0 0 1 22 16.9z"/></svg>';
    const bar = document.createElement("div");
    bar.id = "up-contact-bar";
    bar.className = "up-contact-bar";
    bar.setAttribute("aria-label", "Szybki kontakt");
    bar.dataset.contactLocation = "bar";
    bar.innerHTML = '<a class="up-contact-primary"></a><button type="button" class="up-contact-primary up-contact-after" aria-controls="up-contact-sheet" aria-expanded="false" hidden>Zostaw numer</button><button type="button" class="up-contact-secondary" aria-controls="up-contact-sheet" aria-expanded="false">Napisz</button>';
    const call = bar.querySelector("a");
    call.href = tel;
    call.innerHTML = icon + "Zadzwoń";
    const after = bar.querySelector(".up-contact-after");
    const write = bar.querySelector(".up-contact-secondary");
    const sheet = document.createElement("dialog");
    sheet.id = "up-contact-sheet";
    sheet.className = "up-contact-sheet";
    sheet.setAttribute("aria-labelledby","up-contact-title");
    sheet.dataset.contactLocation = "sheet";
    sheet.innerHTML = '<div class="up-sheet-grip" aria-hidden="true"></div><button type="button" class="up-sheet-close" aria-label="Zamknij">×</button><h2 id="up-contact-title">Jak wolisz się skontaktować?</h2><p>Odpowiadam osobiście · pn–pt 9–17</p><a class="up-sheet-row up-sheet-mail"><strong>Napisz e-mail</strong><span>kontakt@upsellio.pl · temat już wpisany</span></a><button type="button" class="up-sheet-copy">Kopiuj adres e-mail</button><a class="up-sheet-row up-sheet-sms"><strong>Wyślij SMS</strong><span></span></a><a class="up-sheet-row up-sheet-diag"><strong>Oddzwonię do Ciebie</strong><span>Przejdź do formularza diagnozy</span></a><p>W ciągu 24h roboczych. Bez zobowiązań.</p><a class="up-sheet-call">Wolisz od razu? Zadzwoń</a><span class="up-sheet-feedback" role="status" aria-live="polite"></span>';
    sheet.querySelector(".up-sheet-mail").href = mail;
    sheet.querySelector(".up-sheet-sms").href = "sms:" + phone;
    sheet.querySelector(".up-sheet-sms span").textContent = phone;
    sheet.querySelector(".up-sheet-diag").href = diag;
    sheet.querySelector(".up-sheet-diag").addEventListener("click", () => sheet.close());
    sheet.querySelector(".up-sheet-call").href = tel;
    document.body.append(bar, sheet);
    document.body.classList.add("up-mobile-contact-ready");
    const ham = nav.querySelector("#hamburger");
    if (ham) {
      const link = document.createElement("a");
      link.className = "up-nav-call";
      link.href = tel;
      link.dataset.contactLocation = "nav";
      link.setAttribute("aria-label", "Zadzwoń do Upsellio");
      link.innerHTML = icon;
      ham.before(link);
    }
    const menu = nav.querySelector("#mobile-menu .wrap");
    if (menu && !menu.querySelector('a[href^="tel:"]')) {
      const link = document.createElement("a");
      link.href = tel;
      link.dataset.contactLocation = "nav";
      link.textContent = "Zadzwoń: " + phone;
      menu.append(link);
    }
    function emit(event, location) {
      if (!window.upsellioData || window.upsellioData.skipAnalytics || !window.upsellioCookie?.statistics) return;
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({event, location});
    }
    // Keep the full diagnosis form as fallback when the callback API is unavailable.
    const diagLink = sheet.querySelector(".up-sheet-diag");
    let callbackPhone = null;
    const ajaxUrl = window.upsellioData?.ajaxUrl;
    if (ajaxUrl && typeof fetch === "function") {
      let endpoint;
      try { endpoint = new URL(ajaxUrl, location.href); } catch (_) {}
      if (endpoint && endpoint.origin === location.origin) {
        const form = document.createElement("form");
        form.className = "up-sheet-callback";
        form.innerHTML = '<h3>Oddzwonię do Ciebie</h3><label for="up-callback-phone">Telefon</label><input id="up-callback-phone" name="lead_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+48 000 000 000" maxlength="24" required><div class="up-callback-honeypot" aria-hidden="true"><label>Strona<input name="lead_website" tabindex="-1" autocomplete="off"></label></div><label class="up-callback-consent"><input type="checkbox" name="lead_consent" value="1" required><span>Zgadzam się na kontakt telefoniczny w sprawie diagnozy.</span></label><button class="up-callback-send" type="submit">Oddzwoń</button><p class="up-callback-status" role="status" aria-live="polite"></p>';
        diagLink.before(form);
        diagLink.querySelector("strong").textContent = "Wolisz opisać sytuację?";
        diagLink.querySelector("span").textContent = "Przejdź do pełnego formularza diagnozy";
        callbackPhone = form.querySelector('input[name="lead_phone"]');
        const status = form.querySelector(".up-callback-status");
        const submit = form.querySelector("button[type=submit]");
        let busy = false;
        let submitted = false;
        callbackPhone.addEventListener("input", () => {
          callbackPhone.setCustomValidity("");
        });
        form.addEventListener("submit", async e => {
          e.preventDefault();
          if (busy || submitted) return;
          const phone = callbackPhone.value.trim();
          const digits = phone.replace(/\D/g, "");
          let normalized = "";
          if (phone.startsWith("+")) normalized = "+" + digits;
          else if (digits.startsWith("00")) normalized = "+" + digits.slice(2);
          else if (digits.length === 9) normalized = "+48" + digits;
          else if (digits.length === 11 && digits.startsWith("48")) normalized = "+" + digits;
          if (!/^\+?[0-9 ()-]+$/.test(phone) || !/^\+[1-9][0-9]{8,14}$/.test(normalized) || (normalized.startsWith("+48") && !/^\+48[1-9][0-9]{8}$/.test(normalized))) {
            callbackPhone.setCustomValidity("Podaj poprawny numer telefonu. Dla numeru zagranicznego użyj prefiksu kraju.");
            callbackPhone.reportValidity();
            return;
          }
          if (!form.reportValidity()) return;
          const data = new FormData(form);
          data.set("lead_phone", normalized);
          busy = true;
          submit.disabled = true;
          form.setAttribute("aria-busy","true");
          status.textContent = "Wysyłam…";
          status.classList.remove("is-error");
          let serverMessage = "";
          try {
            const nonceBody = new URLSearchParams({action:"upsellio_refresh_lead_form_nonce"});
            const nonceResponse = await fetch(endpoint.href,{method:"POST",credentials:"same-origin",body:nonceBody,cache:"no-store"});
            const nonceJson = await nonceResponse.json();
            if (!nonceResponse.ok || !nonceJson.success || typeof nonceJson.data?.nonce !== "string") throw new Error("Nie udało się rozpocząć zgłoszenia.");
            data.set("action","upsellio_mobile_callback");
            data.set("nonce",nonceJson.data.nonce);
            const response = await fetch(endpoint.href,{method:"POST",credentials:"same-origin",body:data,cache:"no-store"});
            const result = await response.json();
            if (!response.ok || !result.success) {
              if ([400,403,429,500].includes(response.status) && typeof result.data?.message === "string") serverMessage = result.data.message;
              throw new Error("Callback request rejected");
            }
            submitted = true;
            form.reset();
            status.textContent = "Dzięki! Oddzwonię w ciągu 24h roboczych.";
            submit.textContent = "Zgłoszenie wysłane";
            form.querySelectorAll("input").forEach(input => { input.disabled = true; });
            emit("callback_submit","sheet");
          } catch (error) {
            status.textContent = serverMessage || "Nie udało się potwierdzić zgłoszenia. Skorzystaj z pełnego formularza lub zadzwoń.";
            status.classList.add("is-error");
          } finally {
            busy = false;
            submit.disabled = submitted;
            form.removeAttribute("aria-busy");
          }
        });
      }
    }
    let opener = null;
    function open(button) {
      if (!mobile.matches || sheet.open) return;
      opener = button;
      sheet.showModal();
      document.body.classList.add("up-sheet-open");
      button.setAttribute("aria-expanded","true");
      if (button === after && callbackPhone && !callbackPhone.disabled) callbackPhone.focus();
      else sheet.querySelector(".up-sheet-close").focus();
      emit("open_contact_sheet","bar");
    }
    write.addEventListener("click", () => open(write));
    after.addEventListener("click", () => open(after));
    sheet.querySelector(".up-sheet-close").addEventListener("click", () => sheet.close());
    sheet.addEventListener("click", e => {
      if (e.target !== sheet) return;
      const r = sheet.getBoundingClientRect();
      if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) sheet.close();
    });
    sheet.addEventListener("close", () => {
      document.body.classList.remove("up-sheet-open");
      if (opener) { opener.setAttribute("aria-expanded","false"); opener.focus(); }
      opener = null;
    });
    let touchY = null;
    sheet.querySelector(".up-sheet-grip").addEventListener("touchstart", e => { touchY = e.touches[0].clientY; }, {passive:true});
    sheet.querySelector(".up-sheet-grip").addEventListener("touchend", e => { if (touchY !== null && e.changedTouches[0].clientY - touchY > 65) sheet.close(); touchY = null; }, {passive:true});
    sheet.querySelector(".up-sheet-copy").addEventListener("click", async () => {
      const status = sheet.querySelector(".up-sheet-feedback");
      try { await navigator.clipboard.writeText(email); status.textContent = "Skopiowano"; }
      catch (_) { status.textContent = email; }
    });
    function hours() {
      const parts = new Intl.DateTimeFormat("en-GB",{timeZone:"Europe/Warsaw",weekday:"short",hour:"2-digit",hourCycle:"h23"}).formatToParts(new Date());
      const day = parts.find(p => p.type === "weekday")?.value;
      const hour = Number(parts.find(p => p.type === "hour")?.value);
      const working = ["Mon","Tue","Wed","Thu","Fri"].includes(day) && hour >= 9 && hour < 17;
      call.hidden = !working;
      after.hidden = working;
    }
    hours();
    setInterval(hours,60000);
    const visible = new Set();
    function update() {
      const el = document.activeElement;
      const editing = el && el.matches("input:not([type=checkbox]):not([type=radio]):not([type=button]):not([type=submit]),textarea,select,[contenteditable=true]");
      const keyboard = window.visualViewport && window.innerHeight - window.visualViewport.height > 150;
      bar.classList.toggle("up-contact-hidden", visible.size > 0 || !!editing || !!keyboard);
      if (!mobile.matches && sheet.open) sheet.close();
    }
    if ("IntersectionObserver" in window) {
      const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting && e.intersectionRatio >= .15) visible.add(e.target); else visible.delete(e.target); });
        update();
      },{threshold:[0,.15]});
      document.querySelectorAll("form.ups-form").forEach(f => observer.observe(f));
    }
    document.addEventListener("focusin",update);
    document.addEventListener("focusout",() => setTimeout(update,0));
    window.visualViewport?.addEventListener("resize",update);
    mobile.addEventListener("change",update);
    document.addEventListener("click", e => {
      const a = e.target.closest?.('a[href^="tel:"],a[href^="mailto:"],a[href^="sms:"]');
      if (!a) return;
      const loc = a.closest("[data-contact-location]")?.dataset.contactLocation || (a.closest("footer") ? "footer" : a.closest("header.nav") ? "nav" : a.closest('[id="faq"]') ? "faq" : a.closest('[id="start"]') ? "hero" : "page");
      const event = a.protocol === "tel:" ? "click_call" : a.protocol === "mailto:" ? "click_email" : "click_sms";
      emit(event,loc);
    });
    // Topic chips enhance the existing form, without changing its API or consent fields.
    document.querySelectorAll(".up-contact-topics").forEach(fieldset => {
      const form = fieldset.parentElement?.querySelector("form.ups-form");
      const message = form?.querySelector('textarea[name="lead_message"]');
      if (!message) return;
      const buttons = [...fieldset.querySelectorAll("button[data-up-topic]")];
      let previous = "";
      function clear() { buttons.forEach(b => b.setAttribute("aria-pressed","false")); previous = ""; }
      form.addEventListener("reset",clear);
      buttons.forEach(button => button.addEventListener("click", () => {
        button.setAttribute("aria-pressed", button.getAttribute("aria-pressed") === "true" ? "false" : "true");
        const selected = buttons.filter(b => b.getAttribute("aria-pressed") === "true").map(b => b.dataset.upTopic);
        const prefix = selected.length ? "Temat: " + selected.join(", ") + "\n" : "";
        const original = previous && message.value.startsWith(previous) ? message.value.slice(previous.length) : message.value;
        message.value = prefix + original;
        previous = prefix;
        message.dispatchEvent(new Event("input",{bubbles:true}));
      }));
      fieldset.hidden = false;
    });
    // The same TOC remains visible on desktop; use a native disclosure on mobile.
    const toc = document.querySelector(".sp-toc");
    const list = toc?.querySelector("ol");
    const heading = toc?.querySelector(".sp-toc-head");
    if (list && heading) {
      const disclosure = document.createElement("details");
      disclosure.className = "up-mobile-toc";
      const summary = document.createElement("summary");
      summary.textContent = heading.textContent.trim();
      disclosure.append(summary);
      heading.before(disclosure);
      function syncToc() {
        if (mobile.matches) { disclosure.append(list); heading.hidden = true; disclosure.hidden = false; }
        else { disclosure.after(heading,list); heading.hidden = false; disclosure.hidden = true; }
      }
      syncToc();
      mobile.addEventListener("change",syncToc);
      list.addEventListener("click",e => { if (mobile.matches && e.target.closest("a")) disclosure.open = false; });
    }
    update();
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded",init,{once:true});
  else init();
})();


// Open native extended-content disclosure before following its deep links.
// Independent of the mobile contact initializer, so it also works on desktop.
(function () {
  function reveal(hash) {
    if (!hash || hash.length < 2) return null;
    let id;
    try { id = decodeURIComponent(hash.slice(1)); } catch (_) { return null; }
    const target = document.getElementById(id);
    if (!target) return null;
    const parent = target.closest("details.up-extra-content");
    if (!parent || parent.open) return null;
    parent.open = true;
    return target;
  }
  function init() {
    function fromLocation() {
      const target = reveal(location.hash);
      if (target) requestAnimationFrame(() => target.scrollIntoView({block:"start",behavior:"instant"}));
    }
    fromLocation();
    window.addEventListener("hashchange",fromLocation);
    document.addEventListener("click", e => {
      const anchor = e.target.closest?.('a[href^="#"]');
      if (!anchor || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      reveal(anchor.hash);
    },true);
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded",init,{once:true});
  else init();
})();
