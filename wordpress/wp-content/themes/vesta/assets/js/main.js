(() => {
  const header = document.querySelector("[data-site-header]");
  const onScroll = () => {
    if (!header) return;
    header.classList.toggle("is-scrolled", window.scrollY > 12);
  };
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  const menuToggle = document.querySelector("[data-menu-toggle]");
  const mobile = document.querySelector("[data-nav-mobile]");
  if (menuToggle && mobile) {
    menuToggle.addEventListener("click", () => {
      const open = mobile.classList.toggle("is-open");
      menuToggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  const searchToggle = document.querySelector("[data-search-toggle]");
  const searchPanel = document.querySelector("[data-header-search]");
  if (searchToggle && searchPanel) {
    searchToggle.addEventListener("click", () => {
      const open = searchPanel.classList.toggle("is-open");
      if (open) {
        searchPanel.removeAttribute("hidden");
        const input = searchPanel.querySelector("input[type=search]");
        if (input) input.focus();
      } else {
        searchPanel.setAttribute("hidden", "");
      }
    });
  }

  document.querySelectorAll("[data-product-rail]").forEach((rail) => {
    const track = rail.querySelector("[data-rail-track]");
    const prev = rail.querySelector("[data-rail-prev]");
    const next = rail.querySelector("[data-rail-next]");
    if (!track) return;
    const scrollBy = (dir) => {
      track.scrollBy({ left: dir * Math.min(track.clientWidth * 0.8, 560), behavior: "smooth" });
    };
    if (prev) prev.addEventListener("click", () => scrollBy(-1));
    if (next) next.addEventListener("click", () => scrollBy(1));
  });

  const tabs = document.querySelector("[data-carousel-tabs]");
  if (tabs) {
    const section = tabs.closest("section");
    const cards = section ? section.querySelectorAll(".product-card") : [];
    tabs.querySelectorAll("button[data-tab]").forEach((btn) => {
      btn.addEventListener("click", () => {
        tabs.querySelectorAll("button").forEach((b) => b.classList.remove("is-active"));
        btn.classList.add("is-active");
        const tab = (btn.getAttribute("data-tab") || "").toLowerCase();
        let visible = 0;
        cards.forEach((card) => {
          const cat = (card.getAttribute("data-category") || "").toLowerCase();
          const match =
            !tab ||
            cat.includes(tab.slice(0, -1)) ||
            cat.includes(tab) ||
            (tab === "blazers" && cat.includes("blazer")) ||
            (tab === "camisas" && cat.includes("camisa")) ||
            (tab === "vestidos" && cat.includes("vestido")) ||
            (tab === "sapatos" && cat.includes("sapato")) ||
            (tab === "bolsas" && cat.includes("bolsa"));
          card.style.display = match ? "" : "none";
          if (match) visible += 1;
        });
        if (visible === 0) {
          cards.forEach((card) => {
            card.style.display = "";
          });
        }
      });
    });
  }

  const filtersToggle = document.querySelector("[data-filters-toggle]");
  const filtersPanel = document.querySelector("[data-filters-panel]");
  if (filtersToggle && filtersPanel) {
    filtersToggle.addEventListener("click", () => {
      const open = filtersPanel.classList.toggle("is-open");
      filtersToggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  const testimonials = document.querySelector("[data-testimonials]");
  if (testimonials) {
    const track = testimonials.querySelector("[data-testimonials-track]");
    const slides = track ? Array.from(track.querySelectorAll(".testimonial")) : [];
    const prev = testimonials.querySelector("[data-testimonials-prev]");
    const next = testimonials.querySelector("[data-testimonials-next]");
    let index = 0;
    const go = (nextIndex) => {
      if (!track || !slides.length) return;
      index = (nextIndex + slides.length) % slides.length;
      track.style.transform = `translateX(-${index * 100}%)`;
      slides.forEach((slide, i) => slide.classList.toggle("is-active", i === index));
    };
    if (prev) prev.addEventListener("click", () => go(index - 1));
    if (next) next.addEventListener("click", () => go(index + 1));
  }

  document.querySelectorAll("[data-reveal]").forEach((el, i) => {
    el.style.opacity = "0";
    el.style.transform = "translateY(14px)";
    el.style.transition = `opacity .55s ease ${i * 0.05}s, transform .55s ease ${i * 0.05}s`;
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            el.style.opacity = "1";
            el.style.transform = "none";
            io.disconnect();
          }
        });
      },
      { threshold: 0.12 }
    );
    io.observe(el);
  });
})();
