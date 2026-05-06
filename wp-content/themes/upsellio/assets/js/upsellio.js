(function () {
  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  let reveals = [];
  const topBtn = document.getElementById("scroll-top");
  const nav = document.querySelector(".nav");
  const ham = document.getElementById("hamburger");
  const mob = document.getElementById("mobile-menu");
  const navOffset = 84;

  function getNavOffset(extra = 0) {
    return navOffset + extra;
  }

  function revealElement(el) {
    if (!el.classList.contains("visible")) el.classList.add("visible");
  }

  function refreshRevealElements() {
    reveals = Array.from(document.querySelectorAll(".reveal, .lp-reveal, .motion-fade"));
  }

  function initGlobalMotion() {
    document.documentElement.classList.add("js-motion-ready");

    const revealTargets = Array.from(
      document.querySelectorAll(
        "main section, .section, .pf-section, .pf-contact, .mc-section, .mc-contact, .definition-card, .definition-tool, .definition-contact, .ups-footer"
      )
    );
    revealTargets.forEach((el, index) => {
      if (el.classList.contains("lp-reveal") || el.classList.contains("reveal")) return;
      el.classList.add("motion-fade");
      el.classList.add(`d${(index % 3) + 1}`);
    });

    const hoverLiftTargets = document.querySelectorAll(
      ".pr-card, .mp-card, .pf-rel-card, .definition-card, .ups-blog-card, .portfolio-mini-card, .ups-footer__section, .ups-footer__btn, .button, button"
    );
    hoverLiftTargets.forEach((el) => {
      if (el.classList.contains("motion-hover-lift")) return;
      el.classList.add("motion-hover-lift");
    });

    refreshRevealElements();
  }

  function initRevealObserver() {
    const revealByViewport = () => {
      const vh = window.innerHeight || document.documentElement.clientHeight || 0;
      reveals.forEach((el) => {
        if (el.classList.contains("visible")) return;
        const rect = el.getBoundingClientRect();
        if (rect.top < vh * 0.92) revealElement(el);
      });
    };

    if (prefersReducedMotion) {
      reveals.forEach((el) => revealElement(el));
      return;
    }

    if (!("IntersectionObserver" in window)) {
      revealByViewport();
      window.addEventListener("scroll", revealByViewport, { passive: true });
      window.addEventListener("resize", revealByViewport);
      return;
    }

    const revealObserver = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          revealElement(entry.target);
          observer.unobserve(entry.target);
        });
      },
      { rootMargin: "0px 0px -8% 0px", threshold: 0.05 }
    );
    reveals.forEach((el) => revealObserver.observe(el));
    revealByViewport();
    window.addEventListener("scroll", revealByViewport, { passive: true });
    window.addEventListener("resize", revealByViewport);
  }

  function initScrollUI() {
    let rafId = null;
    function run() {
      const isPastThreshold = window.scrollY > 12;
      if (nav) nav.classList.toggle("is-scrolled", isPastThreshold);
      if (topBtn) topBtn.classList.toggle("visible", window.scrollY > 450);
      rafId = null;
    }
    function onScroll() {
      if (rafId) return;
      rafId = window.requestAnimationFrame(run);
    }
    window.addEventListener("scroll", onScroll, { passive: true });
    run();
  }

  initGlobalMotion();
  initRevealObserver();
  initScrollUI();

  if (topBtn) {
    topBtn.addEventListener("click", () =>
      window.scrollTo({ top: 0, behavior: prefersReducedMotion ? "auto" : "smooth" })
    );
  }

  if (ham && mob) {
    let wasOpen = false;
    const getMenuFocusable = () =>
      Array.from(mob.querySelectorAll("a, button, [tabindex]:not([tabindex='-1'])")).filter(
        (element) => !element.hasAttribute("disabled")
      );

    const setMobileMenuState = (isOpen) => {
      ham.classList.toggle("open", isOpen);
      mob.classList.toggle("open", isOpen);
      ham.setAttribute("aria-expanded", isOpen ? "true" : "false");
      ham.setAttribute("aria-label", isOpen ? "Zamknij menu" : "Otwórz menu");
      document.body.classList.toggle("is-mobile-menu-open", isOpen);

      if (isOpen) {
        const [firstFocusable] = getMenuFocusable();
        if (firstFocusable) {
          window.requestAnimationFrame(() => firstFocusable.focus());
        }
      } else if (wasOpen) {
        window.requestAnimationFrame(() => ham.focus());
      }
      wasOpen = isOpen;
    };

    setMobileMenuState(false);

    ham.addEventListener("click", () => {
      const isOpen = !ham.classList.contains("open");
      setMobileMenuState(isOpen);
    });

    mob.querySelectorAll("a").forEach((a) => {
      a.addEventListener("click", () => {
        setMobileMenuState(false);
      });
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth >= 761) {
        setMobileMenuState(false);
      }
    });

    window.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        setMobileMenuState(false);
        return;
      }
      if (event.key !== "Tab" || !ham.classList.contains("open")) return;
      const focusable = getMenuFocusable();
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      const active = document.activeElement;

      if (event.shiftKey && active === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
      }
    });
  }

  const dropdownRoots = Array.from(document.querySelectorAll(".nav-dropdown"));
  if (dropdownRoots.length) {
    const closeDropdown = (root) => {
      root.classList.remove("open");
      const toggle = root.querySelector(".nav-dropdown-toggle");
      if (toggle) toggle.setAttribute("aria-expanded", "false");
    };
    const closeAllDropdowns = () => {
      dropdownRoots.forEach((root) => closeDropdown(root));
    };

    dropdownRoots.forEach((root) => {
      const toggle = root.querySelector(".nav-dropdown-toggle");
      if (!toggle) return;
      toggle.addEventListener("click", (event) => {
        event.preventDefault();
        const isOpen = root.classList.contains("open");
        closeAllDropdowns();
        if (!isOpen) {
          root.classList.add("open");
          toggle.setAttribute("aria-expanded", "true");
        }
      });
    });

    document.addEventListener("click", (event) => {
      const clickedInside = dropdownRoots.some((root) => root.contains(event.target));
      if (!clickedInside) closeAllDropdowns();
    });
  }

  document.addEventListener("click", (event) => {
    if (event.defaultPrevented) return;
    if (event.button !== 0) return;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const link = event.target.closest('a[href*="#"]');
    if (!link) return;
    if (link.hasAttribute("download")) return;
    if ((link.getAttribute("target") || "").toLowerCase() === "_blank") return;

    const href = link.getAttribute("href") || "";
    if (!href || href === "#") return;

    let url;
    try {
      url = new URL(href, window.location.href);
    } catch (error) {
      return;
    }

    // Intercept only same-page hash links to avoid breaking regular navigation.
    if (url.pathname !== window.location.pathname) return;
    if (url.search !== window.location.search) return;
    if (!url.hash || url.hash.length < 2) return;

    const id = decodeURIComponent(url.hash.slice(1));
    const target = id ? document.getElementById(id) : null;
    if (!target) return;

    event.preventDefault();
    const offset = target.getBoundingClientRect().top + window.scrollY - getNavOffset(8);
    window.scrollTo({ top: Math.max(0, offset), behavior: prefersReducedMotion ? "auto" : "smooth" });
  });

  function initScrollSpy() {
    const navLinks = Array.from(document.querySelectorAll('.nav-links a[href^="#"], .mobile-menu a[href^="#"]'));
    const pairs = navLinks
      .map((link) => {
        const id = (link.getAttribute("href") || "").replace("#", "");
        const target = id ? document.getElementById(id) : null;
        return target ? { link, target, id } : null;
      })
      .filter(Boolean);
    if (!pairs.length || !("IntersectionObserver" in window)) return;

    const seen = new Map();
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          const id = entry.target.getAttribute("id");
          if (!id) return;
          seen.set(id, entry.isIntersecting ? entry.intersectionRatio : 0);
        });

        let activeId = "";
        let bestScore = 0;
        seen.forEach((score, id) => {
          if (score > bestScore) {
            bestScore = score;
            activeId = id;
          }
        });

        if (!activeId) return;
        pairs.forEach(({ link, id }) => {
          const isActive = id === activeId;
          link.classList.toggle("is-active-section", isActive);
          if (isActive) link.setAttribute("aria-current", "location");
          else if (link.classList.contains("is-active")) link.setAttribute("aria-current", "page");
          else link.removeAttribute("aria-current");
        });
      },
      { rootMargin: "-20% 0px -58% 0px", threshold: [0.2, 0.45, 0.7] }
    );

    pairs.forEach(({ target }) => observer.observe(target));
  }

  function initFaq() {
    const items = Array.from(document.querySelectorAll(".faq-item"));
    if (!items.length) return;

    const closeAll = () => {
      items.forEach((item) => {
        const trigger = item.querySelector(".faq-q");
        const answer = item.querySelector(".faq-a");
        item.classList.remove("open");
        if (trigger) trigger.setAttribute("aria-expanded", "false");
        if (answer) answer.setAttribute("hidden", "hidden");
      });
    };

    items.forEach((item, index) => {
      const trigger = item.querySelector(".faq-q");
      const answer = item.querySelector(".faq-a");
      if (!trigger || !answer) return;

      const answerId = answer.id || `faq-a-${index + 1}`;
      const triggerId = trigger.id || `faq-q-${index + 1}`;
      answer.id = answerId;
      trigger.id = triggerId;
      trigger.setAttribute("aria-controls", answerId);
      trigger.setAttribute("aria-expanded", "false");
      answer.setAttribute("role", "region");
      answer.setAttribute("aria-labelledby", triggerId);
      answer.setAttribute("hidden", "hidden");
      if (trigger.tagName !== "BUTTON") {
        trigger.setAttribute("role", "button");
        trigger.setAttribute("tabindex", "0");
      }

      const toggle = () => {
        const willOpen = !item.classList.contains("open");
        closeAll();
        if (!willOpen) return;
        item.classList.add("open");
        trigger.setAttribute("aria-expanded", "true");
        answer.removeAttribute("hidden");
      };

      trigger.addEventListener("click", toggle);
      trigger.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") return;
        event.preventDefault();
        toggle();
      });
    });
  }

  initScrollSpy();
  initFaq();

  function initLivePreviewSwitchers() {
    const previewRoots = Array.from(document.querySelectorAll("[data-live-preview='1']"));
    if (!previewRoots.length) return;

    previewRoots.forEach((root) => {
      const buttons = Array.from(root.querySelectorAll("[data-preview-device]"));
      const frameWrap = root.querySelector("[data-preview-frame-wrap]");
      if (!buttons.length || !frameWrap) return;

      buttons.forEach((button) => {
        button.addEventListener("click", () => {
          const device = button.getAttribute("data-preview-device");
          buttons.forEach((item) => item.classList.remove("is-active"));
          button.classList.add("is-active");
          frameWrap.classList.toggle("is-mobile", device === "mobile");
        });
      });
    });
  }

  initLivePreviewSwitchers();

  const blogRoot = document.querySelector(".js-ups-blog-root");
  if (blogRoot && window.upsellioData?.ajaxUrl && window.upsellioData?.blogNonce) {
    const dynamicContainer = blogRoot.querySelector(".js-ups-blog-dynamic");
    const searchInput = blogRoot.querySelector(".js-ups-blog-search-input");
    const searchForm = blogRoot.querySelector(".js-ups-blog-search-form");
    const activeFiltersContainer = blogRoot.querySelector(".js-ups-blog-active-filters");
    const clearFiltersButton = blogRoot.querySelector(".js-ups-blog-clear-filters");
    const filterNote = blogRoot.querySelector(".js-ups-blog-filter-note");

    const setActiveCategory = (category) => {
      blogRoot.querySelectorAll(".js-ups-blog-category").forEach((item) => {
        const itemCategory = item.dataset.category || "";
        item.classList.toggle("active", itemCategory === (category || ""));
      });
    };
    const setActiveTags = (tags) => {
      blogRoot.querySelectorAll(".js-ups-blog-tag").forEach((item) => {
        const itemTag = item.dataset.tag || "";
        if (!itemTag) {
          item.classList.toggle("active", tags.length === 0);
        } else {
          item.classList.toggle("active", tags.includes(itemTag));
        }
      });
    };
    const parseTags = (rawTags) => {
      if (!rawTags) return [];
      return String(rawTags)
        .split(",")
        .map((tag) => tag.trim())
        .filter(Boolean)
        .slice(0, 3);
    };
    const getSelectedTags = () => parseTags(blogRoot.dataset.currentTags || "");
    const setFilterNoteError = (showError) => {
      if (!filterNote) return;
      filterNote.classList.toggle("error", showError);
      filterNote.textContent = showError
        ? "Limit osiągnięty: możesz wybrać maksymalnie 3 tagi."
        : "Możesz wybrać maksymalnie 3 tagi jednocześnie.";
    };
    const getCategoryLabel = (categorySlug) => {
      const selected = blogRoot.querySelector(`.js-ups-blog-category[data-category="${categorySlug}"]`);
      return selected ? selected.textContent.trim() : categorySlug;
    };
    const getTagLabel = (tagSlug) => {
      const selected = blogRoot.querySelector(`.js-ups-blog-tag[data-tag="${tagSlug}"]`);
      if (!selected) return tagSlug;
      return selected.textContent.trim().replace(/^#/, "");
    };
    const renderActiveBadges = () => {
      if (!activeFiltersContainer) return;
      const currentCategory = blogRoot.dataset.currentCategory || "";
      const currentTags = getSelectedTags();
      const badges = [];

      if (currentCategory) {
        badges.push(
          `<span class="ups-blog-active-badge">Kategoria: ${getCategoryLabel(currentCategory)}
            <button type="button" class="ups-blog-active-remove js-ups-blog-remove-category" aria-label="Usuń filtr kategorii">×</button>
          </span>`
        );
      }

      currentTags.forEach((tagSlug) => {
        badges.push(
          `<span class="ups-blog-active-badge">Tag: #${getTagLabel(tagSlug)}
            <button type="button" data-tag="${tagSlug}" class="ups-blog-active-remove js-ups-blog-remove-tag" aria-label="Usuń filtr tagu">×</button>
          </span>`
        );
      });

      activeFiltersContainer.innerHTML = badges.join("");
    };

    const setLoading = (isLoading) => {
      if (!dynamicContainer) return;
      dynamicContainer.classList.toggle("is-loading", isLoading);
    };

    const buildUrl = (category, tags, search, paged) => {
      const url = new URL(window.upsellioData.blogIndexUrl, window.location.origin);
      if (category) url.searchParams.set("category", category);
      if (tags.length) url.searchParams.set("tags", tags.join(","));
      if (search) url.searchParams.set("s", search);
      if (paged > 1) url.searchParams.set("paged", String(paged));
      return url;
    };

    const scrollToResults = () => {
      const resultsSection = blogRoot.querySelector(".ups-blog-list-wrap");
      if (!resultsSection) return;
      const top = resultsSection.getBoundingClientRect().top + window.scrollY - getNavOffset(56);
      window.scrollTo({ top: Math.max(0, top), behavior: prefersReducedMotion ? "auto" : "smooth" });
    };

    const fetchBlogContent = async ({
      category = "",
      tags = [],
      paged = 1,
      pushState = true,
      focusResults = false,
    } = {}) => {
      const search = (searchInput?.value || "").trim();
      const payload = new FormData();
      payload.append("action", "upsellio_filter_blog_posts");
      payload.append("nonce", window.upsellioData.blogNonce);
      payload.append("category", category);
      payload.append("tags", tags.join(","));
      payload.append("search", search);
      payload.append("paged", String(paged));

      setLoading(true);
      try {
        const response = await fetch(window.upsellioData.ajaxUrl, {
          method: "POST",
          body: payload,
          credentials: "same-origin",
        });
        const result = await response.json();
        if (!result?.success || !dynamicContainer) return;

        dynamicContainer.innerHTML = result.data.html;
        blogRoot.dataset.currentCategory = category;
        blogRoot.dataset.currentTags = tags.join(",");
        blogRoot.dataset.currentPage = String(paged);
        setActiveCategory(category);
        setActiveTags(tags);
        renderActiveBadges();
        setFilterNoteError(false);

        if (pushState) {
          const nextUrl = buildUrl(category, tags, search, paged);
          window.history.pushState(
            { category, tags, paged, search },
            "",
            `${nextUrl.pathname}${nextUrl.search}`
          );
        }
        if (focusResults) {
          scrollToResults();
        }
      } catch (error) {
        // No-op fallback: if AJAX fails, links/forms still work with full reload.
      } finally {
        setLoading(false);
      }
    };

    blogRoot.addEventListener("click", (event) => {
      const categoryLink = event.target.closest(".js-ups-blog-category");
      if (categoryLink) {
        event.preventDefault();
        const selectedCategory = categoryLink.dataset.category || "";
        const currentTags = getSelectedTags();
        fetchBlogContent({ category: selectedCategory, tags: currentTags, paged: 1, focusResults: true });
        return;
      }

      const tagLink = event.target.closest(".js-ups-blog-tag");
      if (tagLink) {
        event.preventDefault();
        const selectedTag = tagLink.dataset.tag || "";
        const currentCategory = blogRoot.dataset.currentCategory || "";
        const currentTags = getSelectedTags();
        let nextTags = [];

        if (!selectedTag) {
          nextTags = [];
        } else if (currentTags.includes(selectedTag)) {
          nextTags = currentTags.filter((tag) => tag !== selectedTag);
        } else if (currentTags.length >= 3) {
          setFilterNoteError(true);
          return;
        } else {
          nextTags = [...currentTags, selectedTag];
        }

        fetchBlogContent({ category: currentCategory, tags: nextTags, paged: 1, focusResults: true });
        return;
      }

      const removeCategoryButton = event.target.closest(".js-ups-blog-remove-category");
      if (removeCategoryButton) {
        event.preventDefault();
        fetchBlogContent({ category: "", tags: getSelectedTags(), paged: 1, focusResults: true });
        return;
      }

      const removeTagButton = event.target.closest(".js-ups-blog-remove-tag");
      if (removeTagButton) {
        event.preventDefault();
        const removedTag = removeTagButton.dataset.tag || "";
        const nextTags = getSelectedTags().filter((tag) => tag !== removedTag);
        fetchBlogContent({
          category: blogRoot.dataset.currentCategory || "",
          tags: nextTags,
          paged: 1,
          focusResults: true,
        });
        return;
      }

      const paginationLink = event.target.closest(".ups-blog-pagination a");
      if (paginationLink) {
        event.preventDefault();
        const paginationUrl = new URL(paginationLink.href);
        const pagedParam = Number.parseInt(paginationUrl.searchParams.get("paged") || "1", 10);
        const currentCategory = blogRoot.dataset.currentCategory || "";
        const currentTags = getSelectedTags();
        fetchBlogContent({
          category: currentCategory,
          tags: currentTags,
          paged: Number.isNaN(pagedParam) ? 1 : Math.max(1, pagedParam),
          focusResults: true,
        });
      }
    });

    if (clearFiltersButton) {
      clearFiltersButton.addEventListener("click", () => {
        if (searchInput) searchInput.value = "";
        fetchBlogContent({ category: "", tags: [], paged: 1 });
      });
    }

    if (searchForm) {
      searchForm.addEventListener("submit", (event) => {
        event.preventDefault();
        const currentCategory = blogRoot.dataset.currentCategory || "";
        const currentTags = getSelectedTags();
        fetchBlogContent({ category: currentCategory, tags: currentTags, paged: 1, focusResults: true });
      });
    }

    if (searchInput) {
      let searchDebounceId;
      searchInput.addEventListener("input", () => {
        window.clearTimeout(searchDebounceId);
        searchDebounceId = window.setTimeout(() => {
          const currentCategory = blogRoot.dataset.currentCategory || "";
          const currentTags = getSelectedTags();
          fetchBlogContent({ category: currentCategory, tags: currentTags, paged: 1 });
        }, 260);
      });
    }

    window.addEventListener("popstate", () => {
      const currentUrl = new URL(window.location.href);
      const category = currentUrl.searchParams.get("category") || "";
      const rawTags = currentUrl.searchParams.get("tags") || currentUrl.searchParams.get("tag") || "";
      const tags = parseTags(rawTags);
      const search = currentUrl.searchParams.get("s") || "";
      const pagedParam = Number.parseInt(currentUrl.searchParams.get("paged") || "1", 10);
      if (searchInput) searchInput.value = search;
      fetchBlogContent({
        category,
        tags,
        paged: Number.isNaN(pagedParam) ? 1 : Math.max(1, pagedParam),
        pushState: false,
      });
    });

    setActiveCategory(blogRoot.dataset.currentCategory || "");
    setActiveTags(getSelectedTags());
    renderActiveBadges();
  }

  function getStoredAttribution() {
    try {
      const raw = window.localStorage.getItem("upsellioAttribution");
      if (!raw) return {};
      const data = JSON.parse(raw);
      if (data.savedAt && Date.now() - data.savedAt > 90 * 24 * 3600 * 1000) return {};
      return data;
    } catch (error) {
      return {};
    }
  }

  function saveAttribution(value) {
    try {
      window.localStorage.setItem("upsellioAttribution", JSON.stringify({ ...value, savedAt: Date.now() }));
    } catch (error) {
      // noop
    }
  }

  function applyAttributionToLeadForms(attrib) {
    if (!attrib) return;
    document.querySelectorAll('form[data-upsellio-lead-form="1"]').forEach((leadForm) => {
      leadForm.querySelectorAll("[data-ups-utm]").forEach((input) => {
        const key = input.getAttribute("data-ups-utm");
        if (key && key in attrib) input.value = attrib[key] || "";
      });
      const landingInput = leadForm.querySelector('[data-ups-context="landing"]');
      const referrerInput = leadForm.querySelector('[data-ups-context="referrer"]');
      if (landingInput) landingInput.value = attrib.landing;
      if (referrerInput) referrerInput.value = attrib.referrer;
    });
  }

  const currentUrl = new URL(window.location.href);
  const rememberedAttribution = getStoredAttribution();
  const docReferrer = document.referrer || "";
  const firstTouchLanding =
    typeof rememberedAttribution.landing === "string" && rememberedAttribution.landing.trim() !== ""
      ? rememberedAttribution.landing
      : window.location.href;
  const firstTouchReferrer =
    typeof rememberedAttribution.referrer === "string" && rememberedAttribution.referrer.trim() !== ""
      ? rememberedAttribution.referrer
      : docReferrer;
  const attribution = {
    source: currentUrl.searchParams.get("utm_source") || rememberedAttribution.source || "",
    medium: currentUrl.searchParams.get("utm_medium") || rememberedAttribution.medium || "",
    campaign: currentUrl.searchParams.get("utm_campaign") || rememberedAttribution.campaign || "",
    term: currentUrl.searchParams.get("utm_term") || rememberedAttribution.term || "",
    content: currentUrl.searchParams.get("utm_content") || rememberedAttribution.content || "",
    gclid: currentUrl.searchParams.get("gclid") || rememberedAttribution.gclid || "",
    fbclid: currentUrl.searchParams.get("fbclid") || rememberedAttribution.fbclid || "",
    msclkid: currentUrl.searchParams.get("msclkid") || rememberedAttribution.msclkid || "",
    landing: firstTouchLanding,
    referrer: firstTouchReferrer,
  };
  saveAttribution(attribution);
  applyAttributionToLeadForms(attribution);

  function initLeadPolicyNotices() {
    const policyUrl = `${window.location.origin}/polityka-prywatnosci/`;
    document.querySelectorAll('form[data-upsellio-lead-form="1"]').forEach((leadForm) => {
      if (leadForm.querySelector("[data-policy-note='1']")) return;
      const note = document.createElement("p");
      note.setAttribute("data-policy-note", "1");
      note.className = "form-policy-note";
      note.innerHTML =
        'Wysyłając formularz, zgadzasz się na przetwarzanie danych zgodnie z <a href="' +
        policyUrl +
        '">polityką prywatności</a>.';

      const submit =
        leadForm.querySelector("button[type='submit']") ||
        leadForm.querySelector("input[type='submit']");
      if (submit && submit.parentNode) submit.parentNode.insertBefore(note, submit);
      else leadForm.appendChild(note);
    });
  }
  initLeadPolicyNotices();

  const skipAnalytics = Boolean(window.upsellioData && window.upsellioData.skipAnalytics);

  function upsellioHashPII(value) {
    const input = String(value || "").trim().toLowerCase();
    if (!input) return "";
    let hash = 5381;
    for (let i = 0; i < input.length; i += 1) hash = (hash * 33) ^ input.charCodeAt(i);
    return "h_" + (hash >>> 0).toString(16);
  }

  function sanitizeDataLayerPayload(payload) {
    if (!payload || typeof payload !== "object") return {};
    const clean = { ...payload };
    ["email", "lead_email", "user_email", "phone", "lead_phone", "user_phone"].forEach((key) => {
      if (!clean[key]) return;
      const hashedKey = key.indexOf("phone") !== -1 ? "phone_hash" : "email_hash";
      clean[hashedKey] = upsellioHashPII(clean[key]);
      delete clean[key];
    });
    return clean;
  }

  function pushDataLayerEvent(eventName, payload = {}) {
    if (skipAnalytics) return;
    window.dataLayer = window.dataLayer || [];
    const safePayload = sanitizeDataLayerPayload(payload);
    window.dataLayer.push({
      event: eventName,
      page_location: window.location.href,
      page_path: window.location.pathname,
      page_title: document.title,
      ...safePayload,
    });
  }

  /**
   * Konkretny event konwersji pod GTM/GA4 (obok legacy generate_lead).
   */
  function resolveLeadConversionEventName(formOrigin) {
    const o = String(formOrigin || "");
    if (o === "landing-reklama-form") return "lead_form_submit";
    if (o === "landing-www-form") return "lead_form_submit";
    if (o === "landing-b2c-form") return "lead_form_submit";
    if (o === "audit-form") return "lead_audit_form";
    if (o === "blog-form") return "lead_blog_form";
    if (o === "home-lead-magnet" || o === "lead-magnet-single") return "";
    const contactLike = new Set([
      "contact-page-form",
      "contact-form",
      "home-contact-form",
      "offer-page-form",
      "single-post-contact-form",
      "hero-microform",
      "miasto-single",
      "single-portfolio-form",
      "single-marketing-portfolio-form",
      "definicja-single",
    ]);
    if (contactLike.has(o)) return "lead_contact_form";
    return "lead_web_form";
  }

  function pushLeadConversionStack(formElement) {
    const payload = buildLeadPayload(formElement);
    const eventId =
      window.crypto && crypto.randomUUID
        ? crypto.randomUUID()
        : "lead_" + Date.now() + "_" + Math.random().toString(36).slice(2, 10);
    const enrichedPayload = { ...payload, event_id: eventId };

    if (formElement) {
      const existingEventInput = formElement.querySelector('input[name="lead_event_id"]');
      if (existingEventInput) {
        existingEventInput.value = eventId;
      } else {
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "lead_event_id";
        hiddenInput.value = eventId;
        formElement.appendChild(hiddenInput);
      }
    }

    pushDataLayerEvent("generate_lead", enrichedPayload);
    const extra = resolveLeadConversionEventName(payload.form_origin);
    if (extra) pushDataLayerEvent(extra, enrichedPayload);
    if (payload.lead_magnet_name) pushDataLayerEvent("lead_magnet_signup", enrichedPayload);

    if (!skipAnalytics && typeof window.fbq === "function") {
      window.fbq(
        "track",
        "Lead",
        {
          content_name: payload.form_origin || "lead_form",
          content_category: payload.lead_service || "",
        },
        { eventID: eventId }
      );
    }

    if (
      !skipAnalytics &&
      typeof window.gtag === "function" &&
      window.upsellioAds &&
      window.upsellioAds.conversionLabel
    ) {
      window.gtag("event", "conversion", {
        send_to: window.upsellioAds.conversionLabel,
        transaction_id: eventId,
      });
    }
  }

  function buildLeadPayload(formElement) {
    if (!formElement) return {};
    const formId = formElement.id || formElement.dataset.upsellioLeadForm || "lead-form";
    const formOrigin = formElement.querySelector('input[name="lead_form_origin"]')?.value || "";
    const leadSource = formElement.querySelector('input[name="lead_source"]')?.value || "";
    const leadMagnetName = formElement.querySelector('input[name="lead_magnet_name"]')?.value || "";
    const leadServiceEl = formElement.querySelector('select[name="lead_service"], input[name="lead_service"]');
    const leadService = leadServiceEl?.value || "";
    const leadBudgetEl = formElement.querySelector('select[name="lead_budget"], input[name="lead_budget"]');
    const lead_budget = leadBudgetEl?.value || "";
    const emailInput = formElement.querySelector('input[type="email"], input[name*="email"]');
    const hasEmail = Boolean(emailInput?.value && String(emailInput.value).trim() !== "");
    const variantMatch = (formElement.className || "").match(/ups-form--([a-z0-9_-]+)/i);

    const payload = {
      form_id: formId,
      form_origin: formOrigin,
      form_variant: variantMatch?.[1] || "unknown",
      lead_source: leadSource,
      lead_magnet_name: leadMagnetName,
      lead_service: leadService,
      lead_budget,
      has_email: hasEmail,
      utm_source: attribution.source || "",
      utm_medium: attribution.medium || "",
      utm_campaign: attribution.campaign || "",
      utm_term: attribution.term || "",
      utm_content: attribution.content || "",
      gclid: attribution.gclid || "",
      fbclid: attribution.fbclid || "",
      msclkid: attribution.msclkid || "",
      landing_url: attribution.landing || "",
      referrer: attribution.referrer || "",
    };
    if (
      formOrigin === "landing-reklama-form" ||
      formOrigin === "landing-www-form" ||
      formOrigin === "landing-b2c-form"
    ) {
      payload.form_type = "free_consultation";
    }
    if (formOrigin === "landing-www-form") {
      payload.service_category = "website_development";
    }
    if (formOrigin === "landing-b2c-form") {
      payload.service_category = "b2c_marketing";
    }
    return payload;
  }

  (function attachFormEngagementEvents() {
    document.querySelectorAll('form[data-upsellio-lead-form="1"]').forEach((formEl) => {
      let started = false;
      let submitted = false;
      let lastFieldFired = "";
      const formId = formEl.id || formEl.dataset.upsellioLeadForm || "lead-form";
      const formOrigin = formEl.querySelector('input[name="lead_form_origin"]')?.value || "";

      formEl.addEventListener("focusin", (event) => {
        if (!started && event.target.matches("input,textarea,select")) {
          started = true;
          pushDataLayerEvent("form_start", { form_id: formId, form_origin: formOrigin });
        }
      });

      formEl.addEventListener("change", (event) => {
        if (!event.target.matches("input,textarea,select")) return;
        const name = event.target.name || "";
        if (!name || name === lastFieldFired) return;
        const value = String(event.target.value || "").trim();
        if (value.length < 2) return;
        lastFieldFired = name;
        pushDataLayerEvent("form_field_complete", {
          form_id: formId,
          form_origin: formOrigin,
          field_name: name,
        });
      });

      formEl.addEventListener("submit", () => {
        submitted = true;
      });

      window.addEventListener("beforeunload", () => {
        if (!started || submitted) return;
        try {
          window.dataLayer = window.dataLayer || [];
          window.dataLayer.push({ event: "form_abandon", form_id: formId, form_origin: formOrigin });
        } catch (error) {
          // noop
        }
      });
    });
  })();

  function trackContactClick(type, target) {
    if (skipAnalytics) return;
    const base = {
      contact_type: type,
      contact_target: target,
      click_text: target,
    };
    if (type === "mailto") pushDataLayerEvent("contact_click_mail", base);
    if (type === "tel") pushDataLayerEvent("contact_click_phone", base);
    pushDataLayerEvent("contact_click", base);

    if (!window.upsellioData?.ajaxUrl || !window.upsellioData?.contactNonce) return;
    const body = new URLSearchParams();
    body.append("action", "upsellio_track_contact_click");
    body.append("nonce", window.upsellioData.contactNonce);
    body.append("contact_type", type);
    body.append("target", target);
    body.append("landing_url", window.location.href);
    body.append("referrer", document.referrer || "");

    if (navigator.sendBeacon) {
      navigator.sendBeacon(window.upsellioData.ajaxUrl, body);
      return;
    }

    fetch(window.upsellioData.ajaxUrl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
      body: body.toString(),
      keepalive: true,
      credentials: "same-origin",
    }).catch(() => {
      // noop
    });
  }

  document.querySelectorAll('a[href^="mailto:"]').forEach((link) => {
    link.addEventListener("click", () => trackContactClick("mailto", link.getAttribute("href") || ""));
  });

  document.querySelectorAll('a[href^="tel:"]').forEach((link) => {
    link.addEventListener("click", () => trackContactClick("tel", link.getAttribute("href") || ""));
  });

  document.querySelectorAll('a[href$=".pdf"], a[data-upsellio-lead-magnet-download="1"]').forEach((link) => {
    link.addEventListener("click", () => {
      pushDataLayerEvent("lead_magnet_download", {
        file_url: link.getAttribute("href") || "",
        link_text: (link.textContent || "").trim(),
      });
    });
  });

  document.addEventListener("click", (event) => {
    const cta = event.target.closest("[data-cta]");
    if (!cta) return;
    pushDataLayerEvent("cta_click", {
      cta_label: cta.getAttribute("data-cta") || (cta.textContent || "").trim().slice(0, 80),
      cta_section: cta.getAttribute("data-cta-section") || "",
      cta_position: cta.getAttribute("data-cta-position") || "",
      cta_href: cta.getAttribute("href") || "",
      page_path: window.location.pathname,
    });
  });

  (function initOfferPageTracking() {
    const offerSections = document.querySelectorAll("[data-offer-section]");
    if (!offerSections.length) return;
    const marks = [25, 50, 75, 90];
    const hit = {};

    if ("IntersectionObserver" in window) {
      const seen = {};
      const io = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const sectionId = entry.target.getAttribute("data-offer-section");
            if (!sectionId || seen[sectionId]) return;
            seen[sectionId] = true;
            pushDataLayerEvent("offer_public_section_view", { section_id: sectionId });
          });
        },
        { threshold: 0.5 }
      );
      offerSections.forEach((section) => io.observe(section));
    }

    const onScroll = () => {
      const root = document.documentElement;
      if (!root || !root.scrollHeight) return;
      const pct = Math.round(((root.scrollTop + window.innerHeight) / root.scrollHeight) * 100);
      marks.forEach((mark) => {
        if (pct >= mark && !hit[mark]) {
          hit[mark] = true;
          pushDataLayerEvent("scroll_depth", { percent: mark });
        }
      });
    };
    window.addEventListener("scroll", onScroll, { passive: true });

    let active = true;
    let ticks = 0;
    document.addEventListener("visibilitychange", () => {
      active = !document.hidden;
    });
    window.setInterval(() => {
      if (!active) return;
      ticks += 15;
      if (ticks === 15 || ticks === 30 || ticks === 60 || ticks === 120) {
        pushDataLayerEvent("offer_public_engagement_tick", { seconds: ticks });
      }
    }, 15000);
  })();

  (function initOfferQualifier() {
    const root = document.querySelector(".offer-qualifier");
    if (!root) return;
    const answers = {};
    root.addEventListener("click", (event) => {
      const button = event.target.closest("[data-quiz-answer]");
      if (!button) return;
      const question = button.getAttribute("data-quiz-question") || "";
      const answer = button.getAttribute("data-quiz-answer") || "";
      if (!question || !answer) return;

      answers[question] = answer;
      pushDataLayerEvent("quiz_answer", { quiz_question: question, quiz_answer: answer });

      const currentStep = button.closest("[data-quiz-step]");
      const currentNum = Number.parseInt(currentStep?.getAttribute("data-quiz-step") || "1", 10);
      const nextStep = root.querySelector(`[data-quiz-step="${currentNum + 1}"]`);
      if (currentStep) currentStep.hidden = true;
      if (nextStep) {
        nextStep.hidden = false;
        return;
      }

      const result = root.querySelector("[data-quiz-result]");
      if (result) result.hidden = false;
      const summary = root.querySelector("[data-quiz-summary]");
      if (summary) {
        summary.textContent = `Wybrane: ${answers.problem || "-"}, ${answers.industry || "-"}, budzet: ${answers.budget || "-"}.`;
      }
      try {
        window.localStorage.setItem("upsellioOfferQuiz", JSON.stringify(answers));
      } catch (error) {
        // noop
      }
      pushDataLayerEvent("quiz_complete", answers);

      const formEl = document.querySelector("form[data-upsellio-server-form='1']");
      if (!formEl) return;
      Object.entries(answers).forEach(([key, value]) => {
        const inputName = `lead_quiz_${key}`;
        const existing = formEl.querySelector(`input[name="${inputName}"]`);
        if (existing) {
          existing.value = value;
          return;
        }
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = inputName;
        input.value = value;
        formEl.appendChild(input);
      });
    });
  })();

  (function initExitIntentModal() {
    const modal = document.getElementById("ups-exit-intent");
    if (!modal) return;
    if (window.sessionStorage.getItem("ups_exit_shown")) return;
    let armed = false;
    window.setTimeout(() => {
      armed = true;
    }, 15000);

    const closeModal = () => {
      modal.hidden = true;
    };

    if (!window.matchMedia("(max-width: 900px)").matches) {
      document.addEventListener("mouseout", (event) => {
        if (!armed) return;
        if (event.clientY > 0) return;
        if (event.relatedTarget || event.toElement) return;
        modal.hidden = false;
        window.sessionStorage.setItem("ups_exit_shown", "1");
        pushDataLayerEvent("exit_intent_shown", { page_path: window.location.pathname });
      });
    } else {
      let scrolled50 = false;
      let idleTimer;
      const maybeShow = () => {
        if (!armed || !scrolled50 || window.sessionStorage.getItem("ups_exit_shown")) return;
        modal.hidden = false;
        window.sessionStorage.setItem("ups_exit_shown", "1");
        pushDataLayerEvent("exit_intent_shown", { page_path: window.location.pathname });
      };
      window.addEventListener("scroll", () => {
        const root = document.documentElement;
        const pct = Math.round(((root.scrollTop + window.innerHeight) / root.scrollHeight) * 100);
        if (pct >= 50) scrolled50 = true;
        window.clearTimeout(idleTimer);
        idleTimer = window.setTimeout(maybeShow, 10000);
      }, { passive: true });
      window.setTimeout(maybeShow, 60000);
    }

    modal.addEventListener("click", (event) => {
      if (!event.target.closest("[data-exit-close]")) return;
      closeModal();
      pushDataLayerEvent("exit_intent_dismissed", {});
    });
  })();

  function upsellioMessageForLeadFormResponse(finalUrl, responseOk) {
    var generic =
      "Nie udało się wysłać formularza. Odśwież stronę i spróbuj ponownie, albo napisz na kontakt@upsellio.pl.";
    var byReason = {
      nonce:
        "Sesja formularza wygasła (strona otwarta długo lub z pamięci podręcznej). Odśwież stronę (F5) i wyślij ponownie.",
      fields: "Uzupełnij wymagane pola (imię, e-mail, wiadomość) i zaznacz zgodę na kontakt.",
      rate: "Zbyt wiele prób z tej sieci lub adresu e-mail. Spróbuj ponownie za około godzinę albo napisz na kontakt@upsellio.pl.",
      save: "Nie udało się zapisać zgłoszenia. Napisz na kontakt@upsellio.pl — odbiorę wiadomość.",
    };

    function messageForLeadErrorUrl(u) {
      if (u.searchParams.get("ups_lead_status") !== "error") {
        return null;
      }
      var reason = u.searchParams.get("ups_lead_reason") || "";
      return byReason[reason] || generic;
    }

    if (!finalUrl) {
      return responseOk ? null : generic;
    }
    try {
      var u = new URL(finalUrl, window.location.origin);
      var fromRedirect = messageForLeadErrorUrl(u);
      if (!responseOk) {
        return fromRedirect !== null ? fromRedirect : generic;
      }
      return fromRedirect;
    } catch (e) {
      return responseOk ? null : generic;
    }
  }

  /**
   * URL wysyłki formularza. Nie używać form.action — przy <input name="action"> (admin-post)
   * właściwość form.action w JS wskazuje na ten input, a fetch() dostaje [object HTMLInputElement] → 404.
   */
  function resolveFormActionUrl(formEl) {
    if (!formEl || typeof formEl.getAttribute !== "function") {
      return String(window.location.href);
    }
    const attr = formEl.getAttribute("action");
    if (!attr || !String(attr).trim()) {
      return String(window.location.href);
    }
    try {
      return new URL(attr, window.location.href).href;
    } catch (e) {
      return String(window.location.href);
    }
  }

  function resolveFormMethod(formEl) {
    if (!formEl || typeof formEl.getAttribute !== "function") return "POST";
    const m = String(formEl.getAttribute("method") || "POST").trim().toUpperCase();
    return m === "GET" ? "GET" : "POST";
  }

  function initServerLeadForms() {
    const serverForms = Array.from(document.querySelectorAll("form[data-upsellio-server-form='1']"));
    if (!serverForms.length) return;

    serverForms.forEach((serverForm) => {
      if (serverForm.dataset.upsellioAjaxReady === "1") return;
      const serverSubmit = serverForm.querySelector("button[type='submit'], input[type='submit']");
      if (!serverSubmit) return;
      serverForm.dataset.upsellioAjaxReady = "1";

      serverForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        const defaultText = serverSubmit.textContent || serverSubmit.value || "Wyślij";
        let feedback = serverForm.querySelector("[data-form-feedback]");
        if (!feedback) {
          feedback = document.createElement("div");
          feedback.setAttribute("data-form-feedback", "1");
          feedback.setAttribute("role", "status");
          feedback.className = "form-feedback";
          serverForm.insertBefore(feedback, serverForm.firstChild);
        }

        if ("textContent" in serverSubmit) serverSubmit.textContent = "Wysyłanie...";
        if ("value" in serverSubmit) serverSubmit.value = "Wysyłanie...";
        serverSubmit.disabled = true;
        feedback.textContent = "";
        feedback.classList.remove("is-success", "is-error");

        try {
          const response = await fetch(resolveFormActionUrl(serverForm), {
            method: resolveFormMethod(serverForm),
            body: new FormData(serverForm),
            credentials: "same-origin",
            redirect: "follow",
          });
          if (!response.ok || (response.url && response.url.includes("ups_lead_status=error"))) {
            var msg = upsellioMessageForLeadFormResponse(response.url, response.ok);
            throw new Error(msg || "Nie udało się wysłać formularza. Odśwież stronę i spróbuj ponownie.");
          }

          feedback.textContent = "Dziękuję! Wiadomość została zapisana i odezwę się możliwie szybko.";
          feedback.classList.add("is-success");
          if (!skipAnalytics && typeof window.gtag === "function") {
            window.gtag("event", "lead_form_submitted", {
              form_id: serverForm.id || serverForm.dataset.upsellioLeadForm || "lead-form",
            });
          }
          try {
            pushLeadConversionStack(serverForm);
          } catch (analyticsError) {
            // Sukces zapisu leada nie może zależeć od GTM/dataLayer
          }
          serverForm.reset();
          applyAttributionToLeadForms(attribution);
        } catch (error) {
          feedback.textContent = error.message || "Błąd wysyłki. Spróbuj ponownie.";
          feedback.classList.add("is-error");
        } finally {
          if ("textContent" in serverSubmit) serverSubmit.textContent = defaultText;
          if ("value" in serverSubmit) serverSubmit.value = defaultText;
          serverSubmit.disabled = false;
        }
      });
    });
  }

  initServerLeadForms();

  const form = document.getElementById("contact-form") || document.getElementById("audit-form");
  const submitBtn = document.getElementById("submit-btn");
  if (form && submitBtn) {
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

    if (form.dataset.upsellioServerForm === "1") {
      if (form.dataset.upsellioAjaxReady === "1") return;
      form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const defaultText = submitBtn.textContent;
        let feedback = form.querySelector("[data-form-feedback]");
        if (!feedback) {
          feedback = document.createElement("div");
          feedback.setAttribute("data-form-feedback", "1");
          feedback.setAttribute("role", "status");
          feedback.style.margin = "0 0 12px";
          feedback.style.padding = "10px 12px";
          feedback.style.borderRadius = "10px";
          feedback.style.fontSize = "13px";
          form.insertBefore(feedback, form.firstChild);
        }

        submitBtn.textContent = "Wysyłanie...";
        submitBtn.disabled = true;
        feedback.textContent = "";
        feedback.style.display = "none";

        try {
          const response = await fetch(resolveFormActionUrl(form), {
            method: resolveFormMethod(form),
            body: new FormData(form),
            credentials: "same-origin",
            redirect: "follow",
          });
          if (!response.ok || (response.url && response.url.includes("ups_lead_status=error"))) {
            var msgOffer = upsellioMessageForLeadFormResponse(response.url, response.ok);
            throw new Error(msgOffer || "Nie udało się wysłać formularza. Odśwież stronę i spróbuj ponownie.");
          }

          feedback.textContent = "Dziękuję! Wiadomość została zapisana i odezwę się możliwie szybko.";
          feedback.style.display = "block";
          feedback.style.border = "1px solid #c3eddd";
          feedback.style.background = "#e8f8f2";
          feedback.style.color = "#085041";
          submitBtn.textContent = "Wysłano!";
          if (!skipAnalytics && typeof window.gtag === "function") {
            window.gtag("event", "lead_form_submitted", {
              form_id: form.id || form.dataset.upsellioLeadForm || "lead-form",
            });
          }
          try {
            pushLeadConversionStack(form);
          } catch (analyticsError) {
            // Sukces zapisu leada nie może zależeć od GTM/dataLayer
          }
          setTimeout(() => {
            submitBtn.textContent = defaultText;
            submitBtn.disabled = false;
            form.reset();
            applyAttributionToLeadForms(attribution);
          }, 2800);
        } catch (error) {
          feedback.textContent = error.message || "Błąd wysyłki. Spróbuj ponownie.";
          feedback.style.display = "block";
          feedback.style.border = "1px solid #edcccc";
          feedback.style.background = "#fff2f2";
          feedback.style.color = "#b13a3a";
          submitBtn.textContent = defaultText;
          submitBtn.disabled = false;
        }
      });
      return;
    }

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const name = (document.getElementById("fname")?.value || "").trim();
      const email = (document.getElementById("femail")?.value || "").trim();
      const msg = (document.getElementById("fmsg")?.value || "").trim();
      const phone = (document.getElementById("fphone")?.value || "").trim();
      const service = (document.getElementById("fservice")?.value || "").trim();
      const budget = (document.getElementById("fbudget")?.value || "").trim();
      const goal = (document.getElementById("fgoal")?.value || "").trim();

      let ok = true;
      ok = setError("fname", "fname-err", name.length < 2) && ok;
      ok = setError("femail", "femail-err", !validateEmail(email)) && ok;
      ok = setError("fmsg", "fmsg-err", msg.length < 10) && ok;
      if (!ok) return;

      const defaultText = submitBtn.textContent;
      submitBtn.textContent = "Wysyłanie...";
      submitBtn.disabled = true;
      try {
        const payload = new FormData();
        payload.append("action", "upsellio_submit_contact_form");
        payload.append("nonce", window.upsellioData?.contactNonce || "");
        payload.append("name", name);
        payload.append("email", email);
        payload.append("message", msg);
        payload.append("phone", phone);
        payload.append("service", service);
        payload.append("budget", budget);
        payload.append("goal", goal);
        payload.append("source", window.location.href);
        payload.append("website", "");

        const response = await fetch(window.upsellioData?.ajaxUrl || "/wp-admin/admin-ajax.php", {
          method: "POST",
          body: payload,
          credentials: "same-origin",
        });
        const result = await response.json();

        if (!response.ok || !result?.success) {
          throw new Error(result?.data?.message || "Nie udało się wysłać formularza.");
        }

        submitBtn.textContent = "Wysłano! Odezwę się wkrótce ✓";
        submitBtn.style.background = "var(--teal-dark)";
        if (!skipAnalytics && typeof window.gtag === "function") {
          window.gtag("event", "lead_form_submitted", {
            form_id: form.id || "contact-form",
          });
        }
        try {
          pushLeadConversionStack(form);
        } catch (analyticsError) {
          // Sukces zapisu leada nie może zależeć od GTM/dataLayer
        }
        setTimeout(() => {
          submitBtn.textContent = defaultText;
          submitBtn.style.background = "";
          submitBtn.disabled = false;
          form.reset();
        }, 3200);
      } catch (error) {
        submitBtn.textContent = error.message || "Błąd wysyłki. Spróbuj ponownie.";
        submitBtn.style.background = "#d94c4c";
        setTimeout(() => {
          submitBtn.textContent = defaultText;
          submitBtn.style.background = "";
          submitBtn.disabled = false;
        }, 3500);
      }
    });
  }
})();

