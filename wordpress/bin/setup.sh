#!/usr/bin/env bash
set -euo pipefail

SITE_URL="${SITE_URL:-http://localhost:8083}"
ADMIN_USER="${WP_ADMIN_USER:-kraderica}"
ADMIN_PASS="${WP_ADMIN_PASSWORD:-ALTERE-ANTES-DO-DEPLOY}"
ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@kraderica.com}"
TITLE="${WP_SITE_TITLE:-Kra de Rica}"

echo "==> Aguardando WordPress..."
until wp core is-installed --allow-root 2>/dev/null || wp core version --allow-root >/dev/null 2>&1; do
  sleep 2
done

if ! wp core is-installed --allow-root 2>/dev/null; then
  echo "==> Instalando WordPress..."
  wp core install \
    --url="$SITE_URL" \
    --title="$TITLE" \
    --admin_user="$ADMIN_USER" \
    --admin_password="$ADMIN_PASS" \
    --admin_email="$ADMIN_EMAIL" \
    --skip-email \
    --allow-root
fi

echo "==> Idioma pt_BR..."
wp language core install pt_BR --activate --allow-root || true
wp site switch-language pt_BR --allow-root || true

echo "==> Permalinks..."
wp rewrite structure '/%postname%/' --hard --allow-root
wp rewrite flush --hard --allow-root

echo "==> Plugins WooCommerce e Mercado Pago..."
wp plugin install woocommerce --version=9.8.5 --activate --force --allow-root
wp plugin install woocommerce-mercadopago --activate --allow-root || true
wp language plugin install woocommerce pt_BR --allow-root || true

echo "==> Ativando tema e plugins..."
wp plugin activate vesta-core --allow-root
wp theme activate vesta --allow-root

echo "==> WooCommerce páginas e ajustes BR..."
wp option update woocommerce_store_address "Rua Voluntários da Pátria, 233, Loja 05" --allow-root
wp option update woocommerce_store_city "Curitiba" --allow-root
wp option update woocommerce_store_postcode "80020310" --allow-root
wp option update woocommerce_default_country "BR:PR" --allow-root
wp option update woocommerce_currency "BRL" --allow-root
wp option update woocommerce_weight_unit "kg" --allow-root
wp option update woocommerce_dimension_unit "cm" --allow-root
wp option update woocommerce_enable_guest_checkout "yes" --allow-root
wp option update woocommerce_enable_signup_and_login_from_checkout "yes" --allow-root
wp option update woocommerce_enable_myaccount_registration "yes" --allow-root
wp option update woocommerce_calc_taxes "no" --allow-root
wp option update woocommerce_coming_soon "no" --allow-root
wp option update woocommerce_store_pages_only "no" --allow-root
wp option update woocommerce_cart_redirect_after_add "no" --allow-root
wp option update woocommerce_enable_ajax_add_to_cart "yes" --allow-root
wp wc tool run install_pages --user=1 --allow-root || true

SHOP_ID=$(wp option get woocommerce_shop_page_id --allow-root || echo "")
CART_ID=$(wp option get woocommerce_cart_page_id --allow-root || echo "")
CHECKOUT_ID=$(wp option get woocommerce_checkout_page_id --allow-root || echo "")
ACCOUNT_ID=$(wp option get woocommerce_myaccount_page_id --allow-root || echo "")

if [ -n "$SHOP_ID" ] && [ "$SHOP_ID" != "0" ]; then
  wp post update "$SHOP_ID" --post_name=produtos --post_title="Peças" --allow-root
fi
if [ -n "$CART_ID" ] && [ "$CART_ID" != "0" ]; then
  wp post update "$CART_ID" --post_name=carrinho --post_title="Sacola" --allow-root
  wp post update "$CART_ID" --post_content='[woocommerce_cart]' --allow-root
fi
if [ -n "$CHECKOUT_ID" ] && [ "$CHECKOUT_ID" != "0" ]; then
  wp post update "$CHECKOUT_ID" --post_name=finalizar-compra --post_title="Finalizar compra" --allow-root
  wp post update "$CHECKOUT_ID" --post_content='[woocommerce_checkout]' --allow-root
fi
if [ -n "$ACCOUNT_ID" ] && [ "$ACCOUNT_ID" != "0" ]; then
  wp post update "$ACCOUNT_ID" --post_name=minha-conta --post_title="Minha conta" --allow-root
fi

echo "==> Páginas institucionais..."
wp post list --post_type=page --name=a-loja --field=ID --allow-root | grep -q . || \
  wp post create --post_type=page --post_title="A Loja" --post_name=a-loja --post_status=publish --allow-root
wp post list --post_type=page --name=contato --field=ID --allow-root | grep -q . || \
  wp post create --post_type=page --post_title="Contato" --post_name=contato --post_status=publish --allow-root
wp post list --post_type=page --name=diario --field=ID --allow-root | grep -q . || \
  wp post create --post_type=page --post_title="Diário" --post_name=diario --post_status=publish --allow-root
wp post list --post_type=page --name=login --field=ID --allow-root | grep -q . || \
  wp post create --post_type=page --post_title="Entrar" --post_name=login --post_status=publish --allow-root
wp post list --post_type=page --name=cadastro --field=ID --allow-root | grep -q . || \
  wp post create --post_type=page --post_title="Cadastro" --post_name=cadastro --post_status=publish --allow-root
wp post list --post_type=page --name=recuperar-senha --field=ID --allow-root | grep -q . || \
  wp post create --post_type=page --post_title="Recuperar senha" --post_name=recuperar-senha --post_status=publish --allow-root

FRONT=$(wp post list --post_type=page --name=inicio --field=ID --allow-root | head -n1)
if [ -z "$FRONT" ]; then
  FRONT=$(wp post create --post_type=page --post_title="Início" --post_name=inicio --post_status=publish --porcelain --allow-root)
fi
wp option update show_on_front page --allow-root
wp option update page_on_front "$FRONT" --allow-root

echo "==> Zona de frete Brasil..."
ZONE_ID=$(wp wc shipping_zone create --user=1 --name="Brasil" --porcelain --allow-root 2>/dev/null || true)
if [ -n "${ZONE_ID:-}" ]; then
  wp wc shipping_zone_location create "$ZONE_ID" --user=1 --code=BR --type=country --allow-root || true
  wp wc shipping_zone_method create "$ZONE_ID" --user=1 --method_id=local_pickup --allow-root || true
  wp wc shipping_zone_method create "$ZONE_ID" --user=1 --method_id=flat_rate --allow-root || true
fi

echo "==> Seed demo..."
wp vesta seed --allow-root || true
wp rewrite flush --hard --allow-root

echo ""
echo "============================================"
echo " Kra de Rica pronto em $SITE_URL"
echo " Admin:   $SITE_URL/wp-admin/?wp=1"
echo " Atelier: $SITE_URL/admin/"
echo " Usuário: $ADMIN_USER"
echo "============================================"
