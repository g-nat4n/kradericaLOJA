(function () {
  const toggle = document.querySelector("[data-atelier-menu]");
  const nav = document.querySelector("[data-atelier-mobile-nav]");
  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      nav.classList.toggle("is-open");
      const open = nav.classList.contains("is-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      toggle.textContent = open ? "Fechar" : "Abrir menu";
    });
  }

  document.querySelectorAll("[data-confirm]").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      const msg = form.getAttribute("data-confirm") || "Confirmar?";
      if (!window.confirm(msg)) {
        e.preventDefault();
      }
    });
  });
})();
