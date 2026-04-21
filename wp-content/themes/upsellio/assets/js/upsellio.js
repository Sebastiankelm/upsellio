(function () {
  const reveals = document.querySelectorAll(".reveal");
  const topBtn = document.getElementById("scroll-top");
  const ham = document.getElementById("hamburger");
  const mob = document.getElementById("mobile-menu");

  function onScroll() {
    const vh = window.innerHeight;
    reveals.forEach((el) => {
      if (el.getBoundingClientRect().top < vh * 0.9) el.classList.add("visible");
    });
    if (topBtn) {
      if (window.scrollY > 450) topBtn.classList.add("visible");
      else topBtn.classList.remove("visible");
    }
  }

  window.addEventListener("scroll", onScroll, { passive: true });
  setTimeout(onScroll, 120);

  if (topBtn) {
    topBtn.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
  }

  if (ham && mob) {
    ham.addEventListener("click", () => {
      ham.classList.toggle("open");
      mob.classList.toggle("open");
    });

    mob.querySelectorAll("a").forEach((a) => {
      a.addEventListener("click", () => {
        ham.classList.remove("open");
        mob.classList.remove("open");
      });
    });
  }

  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener("click", (e) => {
      const id = a.getAttribute("href").slice(1);
      const target = id ? document.getElementById(id) : null;
      if (!target) return;
      e.preventDefault();
      const offset = target.getBoundingClientRect().top + window.scrollY - 72;
      window.scrollTo({ top: offset, behavior: "smooth" });
    });
  });

  document.querySelectorAll(".faq-item").forEach((item) => {
    const q = item.querySelector(".faq-q");
    if (!q) return;
    q.addEventListener("click", () => {
      const isOpen = item.classList.contains("open");
      document.querySelectorAll(".faq-item").forEach((i) => i.classList.remove("open"));
      if (!isOpen) item.classList.add("open");
    });
  });

  const form = document.getElementById("contact-form") || document.getElementById("audit-form");
  const submitBtn = document.getElementById("submit-btn");
  if (!form || !submitBtn) return;

  function validateEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  function setError(inputId, errId, show) {
    const input = document.getElementById(inputId);
    const err = document.getElementById(errId);
    if (!input || !err) return !show;
    if (show) {
      input.classList.add("error");
      err.classList.add("show");
    } else {
      input.classList.remove("error");
      err.classList.remove("show");
    }
    return !show;
  }

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const name = (document.getElementById("fname")?.value || "").trim();
    const email = (document.getElementById("femail")?.value || "").trim();
    const msg = (document.getElementById("fmsg")?.value || "").trim();

    let ok = true;
    ok = setError("fname", "fname-err", name.length < 2) && ok;
    ok = setError("femail", "femail-err", !validateEmail(email)) && ok;
    ok = setError("fmsg", "fmsg-err", msg.length < 10) && ok;
    if (!ok) return;

    const defaultText = submitBtn.textContent;
    submitBtn.textContent = "Wysyłanie...";
    submitBtn.disabled = true;

    setTimeout(() => {
      submitBtn.textContent = "Wysłano! Odezwę się wkrótce ✓";
      submitBtn.style.background = "var(--teal-dark)";
      setTimeout(() => {
        submitBtn.textContent = defaultText;
        submitBtn.style.background = "";
        submitBtn.disabled = false;
        form.reset();
      }, 4000);
    }, 600);
  });
})();

