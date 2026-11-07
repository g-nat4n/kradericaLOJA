(function ($) {
  function formatCpf(v) {
    v = (v || "").replace(/\D/g, "").slice(0, 11);
    return v
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  }
  function formatPhone(v) {
    v = (v || "").replace(/\D/g, "").slice(0, 11);
    if (v.length > 10) return v.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
    return v.replace(/(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
  }
  function formatCep(v) {
    v = (v || "").replace(/\D/g, "").slice(0, 8);
    return v.replace(/(\d{5})(\d{1,3})/, "$1-$2");
  }

  $(document).on("input", "#billing_cpf", function () {
    this.value = formatCpf(this.value);
  });
  $(document).on("input", "#billing_phone", function () {
    this.value = formatPhone(this.value);
  });
  $(document).on("input", "#billing_postcode", function () {
    this.value = formatCep(this.value);
  });

  // Coupon on checkout
  $(document).on("click", "#vesta_apply_coupon", function (e) {
    e.preventDefault();
    if (typeof vestaCart === "undefined") return;
    const code = $("#vesta_checkout_coupon").val();
    const $msg = $("#vesta_coupon_msg");
    $.post(vestaCart.ajaxUrl, {
      action: "vesta_apply_coupon",
      nonce: vestaCart.nonce,
      coupon: code,
    })
      .done(function (res) {
        if (!res || !res.success) {
          $msg.text((res && res.data && res.data.message) || "Cupom inválido").prop("hidden", false);
          return;
        }
        $msg.text(res.data.message).prop("hidden", false);
        $("body").trigger("update_checkout");
      })
      .fail(function (xhr) {
        const msg =
          (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ||
          "Cupom inválido";
        $msg.text(msg).prop("hidden", false);
      });
  });

  // Qty +/- in order summary
  $(document).on("click", "[data-qty-minus], [data-qty-plus]", function () {
    const wrap = $(this).closest("[data-qty]");
    const key = wrap.data("qty");
    const isPlus = $(this).is("[data-qty-plus]");
    const $span = wrap.find("span");
    let qty = parseInt($span.text(), 10) || 1;
    qty = isPlus ? qty + 1 : Math.max(1, qty - 1);
    $span.text(qty);
    $.post(wc_checkout_params.ajax_url || (window.vestaCart && vestaCart.ajaxUrl), {
      action: "vesta_update_qty",
      nonce: vestaCart.nonce,
      cart_item_key: key,
      quantity: qty,
    }).done(function () {
      $("body").trigger("update_checkout");
    });
  });

  // Se o Woo não devolver métodos, mantém o texto de frete a combinar.
  $(document.body).on("updated_checkout", function () {
    const $methods = $("#vesta-shipping-methods");
    if ($methods.find(".woocommerce-shipping-methods, .vesta-shipping-table tr, .woocommerce-shipping-methods li").length === 0) {
      if (!$methods.find(".vesta-checkout__hint").length) {
        $methods.html(
          '<p class="vesta-checkout__hint">Frete a combinar — alinhamos a entrega após o pedido.</p>'
        );
      }
    }
  });

  function vestaLoaderLogoUrl() {
    if (window.vestaCheckout && vestaCheckout.logoUrl) return vestaCheckout.logoUrl;
    return "";
  }

  function vestaEnsureCheckoutLoader() {
    if (document.getElementById("vesta-checkout-loader")) return;
    const logo = vestaLoaderLogoUrl();
    const logoHtml = logo
      ? '<img class="vesta-checkout-loader__logo" src="' +
        logo +
        '" alt="Kra de Rica" width="72" height="72" />'
      : '<span class="vesta-checkout-loader__logo-fallback" aria-hidden="true">V</span>';
    const el = document.createElement("div");
    el.id = "vesta-checkout-loader";
    el.className = "vesta-checkout-loader";
    el.setAttribute("hidden", "");
    el.setAttribute("aria-live", "assertive");
    el.setAttribute("aria-busy", "true");
    el.innerHTML =
      '<div class="vesta-checkout-loader__card">' +
      '<div class="vesta-checkout-loader__logo-wrap" aria-hidden="true">' +
      logoHtml +
      '<span class="vesta-checkout-loader__ring"></span>' +
      "</div>" +
      '<p class="vesta-checkout-loader__brand">KRA DE RICA</p>' +
      '<p class="vesta-checkout-loader__text">Processando pagamento…</p>' +
      '<p class="vesta-checkout-loader__hint">Não feche esta página. Estamos finalizando seu pedido.</p>' +
      "</div>";
    document.body.appendChild(el);
  }

  var vestaLoaderTimer = null;
  var vestaLoaderWatch = null;
  var vestaLoaderArmed = false;

  function vestaShowCheckoutLoader(message) {
    vestaEnsureCheckoutLoader();
    const el = document.getElementById("vesta-checkout-loader");
    if (!el) return;
    const text = el.querySelector(".vesta-checkout-loader__text");
    const hint = el.querySelector(".vesta-checkout-loader__hint");
    if (text) text.textContent = message || "Processando pagamento…";
    if (hint) {
      hint.textContent =
        window.vestaCheckout && vestaCheckout.hasMercadoPago
          ? "Não feche esta página. Estamos confirmando com o Mercado Pago."
          : "Não feche esta página. Estamos finalizando seu pedido.";
    }
    el.removeAttribute("hidden");
    document.body.classList.add("vesta-checkout-loading");
  }

  function vestaHideCheckoutLoader(opts) {
    opts = opts || {};
    vestaLoaderArmed = false;
    if (vestaLoaderTimer) {
      clearTimeout(vestaLoaderTimer);
      vestaLoaderTimer = null;
    }
    if (vestaLoaderWatch) {
      clearInterval(vestaLoaderWatch);
      vestaLoaderWatch = null;
    }
    const el = document.getElementById("vesta-checkout-loader");
    if (el) el.setAttribute("hidden", "");
    document.body.classList.remove("vesta-checkout-loading", "processing");
    const $btn = $("#place_order");
    if ($btn.length) {
      $btn.text($btn.data("orig") || $btn.data("value") || "Finalizar pedido").prop("disabled", false);
    }
    if (opts.focusErrors) {
      const err = document.querySelector(
        ".woocommerce-error, .woocommerce-NoticeGroup-checkout, .mp-alert, .mp-form-error, .mp-error, [data-checkout='cardNumber'], #form-checkout__cardNumber"
      );
      if (err && typeof err.scrollIntoView === "function") {
        err.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    }
  }

  function vestaCheckoutHasErrors() {
    const groups = document.querySelectorAll(
      ".woocommerce-error, .woocommerce-NoticeGroup-checkout .woocommerce-error, .woocommerce-notices-wrapper .woocommerce-error"
    );
    for (let i = 0; i < groups.length; i++) {
      if (groups[i].children.length || (groups[i].textContent || "").trim()) return true;
    }
    // Alertas do Mercado Pago (cartão inválido / negado / campos)
    const mpErr = document.querySelector(
      ".mp-alert-message, .mp-form-message--error, .mp-input-error, .mp-checkout-custom-alert, .mp-status-red"
    );
    if (mpErr && mpErr.offsetParent !== null && (mpErr.textContent || "").trim()) return true;
    return false;
  }

  function vestaClearStaleCheckoutErrors() {
    document.querySelectorAll(".woocommerce-NoticeGroup-checkout, .woocommerce-notices-wrapper .woocommerce-error").forEach(function (el) {
      el.remove();
    });
  }

  function vestaMpCardLooksFilled() {
    if (!vestaIsMpCustomSelected()) return true;
    const num =
      document.querySelector("#form-checkout__cardNumber input, #form-checkout__cardNumber, [data-checkout='cardNumber'] input") ||
      document.querySelector("input[name='mercadopago_custom[cardNumber]']");
    const name =
      document.querySelector("#form-checkout__cardholderName, input[name='mercadopago_custom[cardholderName]']");
    const rawNum = num ? String(num.value || num.textContent || "").replace(/\D/g, "") : "";
    const rawName = name ? String(name.value || "").trim() : "";
    // Secure Fields às vezes não expõem value — se iframe existe, deixa seguir
    const hasIframe = !!document.querySelector(
      "#form-checkout__cardNumber iframe, .mp-checkout-custom-card-form iframe"
    );
    if (hasIframe && rawNum.length < 4) {
      // iframe: não dá para ler; só bloqueia se campos obrigatórios vizinhos vazios e sem token
      const token = document.querySelector("input[name='mercadopago_custom[token]'], #MPHiddenInputToken");
      if (token && token.value) return true;
      if (!rawName) return false;
      return true;
    }
    if (rawNum.length > 0 && rawNum.length < 12) return false;
    if (!hasIframe && rawNum.length < 12) return false;
    if (!hasIframe && !rawName) return false;
    return true;
  }

  function vestaArmPayLoader() {
    if (vestaLoaderArmed) return;
    vestaLoaderArmed = true;
    vestaClearStaleCheckoutErrors();
    const $btn = $("#place_order");
    if ($btn.length) {
      if (!$btn.data("orig")) $btn.data("orig", $btn.text());
      $btn.text("Processando…").prop("disabled", true);
    }
    vestaShowCheckoutLoader("Processando pagamento…");

    // Timeout de segurança — nunca fica infinito
    if (vestaLoaderTimer) clearTimeout(vestaLoaderTimer);
    vestaLoaderTimer = setTimeout(function () {
      vestaHideCheckoutLoader({ focusErrors: true });
    }, 22000);

    // Observa erro do Woo / MP e fecha na hora
    if (vestaLoaderWatch) clearInterval(vestaLoaderWatch);
    vestaLoaderWatch = setInterval(function () {
      if (!document.body.classList.contains("vesta-checkout-loading")) {
        clearInterval(vestaLoaderWatch);
        vestaLoaderWatch = null;
        return;
      }
      if (vestaCheckoutHasErrors()) {
        vestaHideCheckoutLoader({ focusErrors: true });
      }
    }, 350);
  }

  vestaEnsureCheckoutLoader();

  // Só arma o loader quando o Woo realmente inicia o pedido (validação ok).
  $(document.body).on("checkout_place_order", function () {
    if (vestaIsMpCustomSelected() && !vestaMpCardLooksFilled()) {
      vestaHideCheckoutLoader({ focusErrors: true });
      return true; // deixa o MP/Woo mostrar o erro do campo
    }
    vestaArmPayLoader();
  });

  // PIX e demais meios: mostra o loader no clique. Cartão incompleto não trava a tela.
  $(document).on("submit", "form.checkout, form#order_review", function () {
    if (vestaIsMpCustomSelected() && !vestaMpCardLooksFilled()) {
      vestaHideCheckoutLoader({ focusErrors: true });
      return;
    }
    vestaArmPayLoader();
  });

  $(document).on("click", "#place_order, .vesta-place-order, .vesta-pay__btn", function () {
    if (vestaIsMpCustomSelected() && !vestaMpCardLooksFilled()) {
      return;
    }
    vestaArmPayLoader();
  });

  $(document.body).on("checkout_error", function () {
    vestaHideCheckoutLoader({ focusErrors: true });
  });

  // Clique em "fechar" / escape no overlay (só se erro)
  $(document).on("keydown", function (e) {
    if (e.key === "Escape" && document.body.classList.contains("vesta-checkout-loading")) {
      vestaHideCheckoutLoader({ focusErrors: true });
    }
  });

  /**
   * Parcelas sempre visíveis no cartão (MP às vezes esconde o select por race).
   */
  var vestaMpLastBin = "";
  var vestaMpFetchBusy = false;

  function vestaIsMpCustomSelected() {
    return (
      document.querySelector('input[name="payment_method"]:checked')?.value ===
      "woo-mercado-pago-custom"
    );
  }

  function vestaFormatBrl(n) {
    return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  }

  function vestaGetCheckoutAmount() {
    const amountEl = document.getElementById("mp-amount");
    const raw = String((amountEl && amountEl.value) || "0").replace(",", ".");
    const n = parseFloat(raw);
    return Number.isFinite(n) && n > 0 ? n : 0;
  }

  function vestaShowInstallmentsCard() {
    const card = document.getElementById("mp-checkout-custom-installments-card");
    if (!card) return;
    card.classList.remove("mp-checkout-custom-installments-display-none");
    card.classList.add("mp-checkout-custom-installments");
    card.style.setProperty("display", "block", "important");
    card.style.setProperty("visibility", "visible", "important");
    card.style.setProperty("height", "auto", "important");
    card.style.setProperty("margin-top", "1rem", "important");
  }

  function vestaFillFallbackInstallments() {
    const sel = document.getElementById("form-checkout__installments");
    const amount = vestaGetCheckoutAmount();
    if (!sel || amount <= 0) return;
    const hasReal = sel.querySelectorAll("option[data-vesta-real='1']").length > 0;
    if (hasReal) return;
    const existing = sel.querySelectorAll("option:not([data-placeholder]):not([disabled])").length;
    if (existing >= 2 && !sel.querySelector("option[data-vesta-fallback]")) return;

    const max = amount >= 50 ? 12 : 1;
    sel.innerHTML = "";
    const ph = document.createElement("option");
    ph.textContent = "Selecione as parcelas";
    ph.disabled = true;
    ph.selected = true;
    ph.setAttribute("data-placeholder", "");
    sel.appendChild(ph);
    for (let i = 1; i <= max; i++) {
      const opt = document.createElement("option");
      opt.value = String(i);
      opt.setAttribute("data-vesta-fallback", "1");
      const parcela = amount / i;
      opt.textContent =
        i === 1
          ? "1x de " + vestaFormatBrl(amount)
          : i + "x de " + vestaFormatBrl(parcela) + " (sem juros*)";
      sel.appendChild(opt);
    }
    const hidden = document.getElementById("cardInstallments");
    if (hidden && !hidden.value) hidden.value = "1";
  }

  function vestaPatchMpInstallments() {
    if (typeof CheckoutPage === "undefined" || CheckoutPage.__vestaInstallmentsPatched) {
      return typeof CheckoutPage !== "undefined";
    }
    CheckoutPage.__vestaInstallmentsPatched = true;
    CheckoutPage.installmentsEnabled = true;

    const originalSet = CheckoutPage.setChangeEventOnInstallments.bind(CheckoutPage);
    CheckoutPage.setChangeEventOnInstallments = function (data) {
      CheckoutPage.installmentsEnabled = true;
      const out = originalSet(data);
      vestaShowInstallmentsCard();
      const sel = document.getElementById("form-checkout__installments");
      if (sel) {
        Array.prototype.forEach.call(sel.options, function (opt) {
          if (opt.value && !opt.disabled) opt.setAttribute("data-vesta-real", "1");
        });
      }
      return out;
    };

    const originalShould = CheckoutPage.shouldEnableInstallmentsComponent.bind(CheckoutPage);
    CheckoutPage.shouldEnableInstallmentsComponent = function (type) {
      originalShould(type);
      if (type !== "debit_card") {
        CheckoutPage.installmentsEnabled = true;
        vestaShowInstallmentsCard();
      }
    };

    if (typeof CheckoutPage.setElementDisplay === "function") {
      const originalDisplay = CheckoutPage.setElementDisplay.bind(CheckoutPage);
      CheckoutPage.setElementDisplay = function (key, value) {
        if (key === "mpInstallmentsCard" && value === "none" && vestaIsMpCustomSelected()) {
          return originalDisplay(key, "block");
        }
        return originalDisplay(key, value);
      };
    }

    return true;
  }

  function vestaReadMpBin() {
    const cf =
      (window.mpEventHandler && window.mpEventHandler.cardForm) ||
      (window.mpCustomCheckoutHandler && window.mpCustomCheckoutHandler.cardForm);
    const fromForm = (cf && (cf.currentBin || cf.lastVerdictBin)) || "";
    if (fromForm && String(fromForm).length >= 6) return String(fromForm).replace(/\D/g, "").slice(0, 8);

    const pm = document.getElementById("paymentMethodId");
    // Sem BIN no form: usa Mastercard comum só para listar opções até o cartão real chegar.
    if (pm && pm.value) return vestaMpLastBin || "51629222";
    return vestaMpLastBin || "";
  }

  function vestaFetchMpInstallments(forceBin) {
    if (!vestaIsMpCustomSelected()) return;
    if (typeof MercadoPago === "undefined" && !window.mpSdkInstance) return;
    if (typeof wc_mercadopago_custom_checkout_params === "undefined") return;

    const amount = vestaGetCheckoutAmount();
    if (amount <= 0) return;

    const bin = (forceBin || vestaReadMpBin() || "51629222").replace(/\D/g, "").slice(0, 8);
    if (bin.length < 6 || vestaMpFetchBusy) return;
    if (!forceBin && bin === vestaMpLastBin && document.querySelector("#form-checkout__installments option[data-vesta-real='1']")) {
      vestaShowInstallmentsCard();
      return;
    }

    vestaMpFetchBusy = true;
    vestaMpLastBin = bin;
    const mp =
      window.mpSdkInstance ||
      new MercadoPago(wc_mercadopago_custom_checkout_params.public_key, {
        locale: wc_mercadopago_custom_checkout_params.locale || "pt-BR",
      });

    if (typeof CheckoutPage !== "undefined") {
      CheckoutPage.installmentsEnabled = true;
    }

    mp.getInstallments({ amount: String(amount), bin: bin })
      .then(function (res) {
        if (res && res[0] && typeof CheckoutPage !== "undefined") {
          CheckoutPage.setChangeEventOnInstallments(res[0]);
        } else {
          vestaFillFallbackInstallments();
          vestaShowInstallmentsCard();
        }
      })
      .catch(function () {
        vestaFillFallbackInstallments();
        vestaShowInstallmentsCard();
      })
      .finally(function () {
        vestaMpFetchBusy = false;
      });
  }

  function vestaEnsureInstallmentsVisible() {
    if (!vestaIsMpCustomSelected()) return;
    vestaPatchMpInstallments();
    vestaShowInstallmentsCard();
    vestaFillFallbackInstallments();
    vestaFetchMpInstallments();
  }

  let mpPatchTries = 0;
  const mpPatchTimer = setInterval(function () {
    const ready = vestaPatchMpInstallments();
    if (vestaIsMpCustomSelected()) vestaEnsureInstallmentsVisible();
    if (ready || ++mpPatchTries > 60) clearInterval(mpPatchTimer);
  }, 100);

  /**
   * PIX e cartão ficam abertos (sem acordeão).
   * O MP Secure Fields bugava ao montar com display:none → abrir depois.
   */
  function vestaPaymentRoot() {
    return document.querySelector(".vesta-pay-methods, #payment .payment_methods");
  }

  function vestaOpenAllPaymentBoxes() {
    const root = vestaPaymentRoot();
    if (!root) return;
    root.classList.remove("vesta-pay-accordion");
    root.classList.add("vesta-pay-open");
    root.querySelectorAll(".wc_payment_method").forEach(function (li) {
      li.classList.add("is-open");
      const box = li.querySelector(".payment_box");
      if (box) {
        box.style.display = "block";
        box.style.visibility = "visible";
        box.style.opacity = "1";
        box.style.height = "auto";
        box.style.overflow = "visible";
      }
    });
  }

  function vestaRefreshMpCardForm() {
    const box = document.querySelector(
      ".payment_method_woo-mercado-pago-custom .payment_box, li.payment_method_woo-mercado-pago-custom .payment_box"
    );
    if (box) {
      box.style.display = "block";
      box.style.visibility = "visible";
      box.style.opacity = "1";
      box.style.height = "auto";
      box.style.overflow = "visible";
    }
    try {
      window.dispatchEvent(new Event("resize"));
    } catch (e) {}
    if (vestaIsMpCustomSelected()) {
      vestaEnsureInstallmentsVisible();
      setTimeout(vestaEnsureInstallmentsVisible, 300);
      setTimeout(vestaEnsureInstallmentsVisible, 900);
    }
  }

  $(document.body).on("updated_checkout payment_method_selected", function () {
    setTimeout(vestaOpenAllPaymentBoxes, 40);
    setTimeout(vestaOpenAllPaymentBoxes, 250);
    setTimeout(vestaRefreshMpCardForm, 300);
  });

  $(document).on("change", 'input[name="payment_method"]', function () {
    vestaOpenAllPaymentBoxes();
    setTimeout(vestaRefreshMpCardForm, 100);
    setTimeout(vestaEnsureInstallmentsVisible, 200);
  });

  setTimeout(vestaOpenAllPaymentBoxes, 80);
  setTimeout(vestaOpenAllPaymentBoxes, 400);
  setTimeout(vestaRefreshMpCardForm, 500);

  setInterval(function () {
    if (!vestaIsMpCustomSelected()) return;
    vestaShowInstallmentsCard();
    const cf =
      (window.mpEventHandler && window.mpEventHandler.cardForm) ||
      (window.mpCustomCheckoutHandler && window.mpCustomCheckoutHandler.cardForm);
    const bin = cf && (cf.currentBin || cf.lastVerdictBin);
    if (bin && String(bin).length >= 6 && String(bin) !== vestaMpLastBin) {
      vestaFetchMpInstallments(String(bin));
    } else if (!document.querySelector("#form-checkout__installments option:not([disabled])")) {
      vestaEnsureInstallmentsVisible();
    }
  }, 700);
})(jQuery);
