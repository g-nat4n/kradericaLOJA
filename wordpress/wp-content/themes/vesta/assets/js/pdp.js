(function ($) {
  // Galeria
  $(document).on("click", "[data-pdp-thumb]", function () {
    const full = $(this).data("full");
    if (!full) return;
    const $gallery = $(this).closest("[data-pdp-gallery]");
    $gallery.find("[data-pdp-main]").attr("src", full);
    $gallery.find("[data-pdp-thumb]").removeClass("is-active").attr("aria-current", "false");
    $(this).addClass("is-active").attr("aria-current", "true");
  });

  // Frete
  function formatCep(v) {
    v = (v || "").replace(/\D/g, "").slice(0, 8);
    return v.replace(/(\d{5})(\d{1,3})/, "$1-$2");
  }

  $(document).on("input", "[data-pdp-shipping] input[name=cep]", function () {
    this.value = formatCep(this.value);
  });

  $(document).on("submit", "[data-pdp-shipping]", function (e) {
    e.preventDefault();
    if (typeof vestaCart === "undefined") return;
    const $form = $(this);
    const cep = $form.find("input[name=cep]").val();
    const $msg = $form.find("[data-shipping-msg]");
    const $list = $form.find("[data-shipping-list]");
    const $btn = $form.find("button[type=submit]");
    $msg.prop("hidden", true).text("");
    $list.empty();
    $btn.prop("disabled", true).text("...");
    $.post(vestaCart.ajaxUrl, {
      action: "vesta_pdp_shipping",
      nonce: vestaCart.nonce,
      cep: cep,
    })
      .done(function (res) {
        if (!res || !res.success) {
          $msg
            .text((res && res.data && res.data.message) || "Não foi possível calcular o envio.")
            .prop("hidden", false);
          return;
        }
        (res.data.quotes || []).forEach(function (q) {
          $list.append(
            $("<li/>").append($("<span/>").text(q.label), $("<span/>").html(q.price))
          );
        });
      })
      .fail(function (xhr) {
        const msg =
          (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ||
          "Não foi possível calcular o envio.";
        $msg.text(msg).prop("hidden", false);
      })
      .always(function () {
        $btn.prop("disabled", false).text("Calcular");
      });
  });
})(jQuery);
