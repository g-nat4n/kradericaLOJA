# Setup Kra de Rica WordPress via Docker + WP-CLI (Windows)
$ErrorActionPreference = "Continue"

$SiteUrl = if ($env:SITE_URL) { $env:SITE_URL } else { "http://localhost:8083" }
$AdminUser = if ($env:WP_ADMIN_USER) { $env:WP_ADMIN_USER } else { "kraderica" }
$AdminPass = if ($env:WP_ADMIN_PASSWORD) { $env:WP_ADMIN_PASSWORD } else { "ALTERE-ANTES-DO-DEPLOY" }
$AdminEmail = if ($env:WP_ADMIN_EMAIL) { $env:WP_ADMIN_EMAIL } else { "admin@kraderica.com" }

function Invoke-Wp {
  param([Parameter(ValueFromRemainingArguments = $true)][string[]]$CliArgs)
  $prev = $ErrorActionPreference
  $ErrorActionPreference = "SilentlyContinue"
  & docker compose run --rm --entrypoint wp wpcli @CliArgs --allow-root
  $code = $LASTEXITCODE
  $ErrorActionPreference = $prev
  if ($code -ne 0) {
    throw "wp falhou: $($CliArgs -join ' ')"
  }
}

function Try-Wp {
  param([Parameter(ValueFromRemainingArguments = $true)][string[]]$CliArgs)
  $prev = $ErrorActionPreference
  $ErrorActionPreference = "SilentlyContinue"
  & docker compose run --rm --entrypoint wp wpcli @CliArgs --allow-root | Out-Null
  $ok = ($LASTEXITCODE -eq 0)
  $ErrorActionPreference = $prev
  return $ok
}

Write-Host "==> Aguardando WordPress..."
$ready = $false
for ($i = 0; $i -lt 60; $i++) {
  if (Try-Wp core version) {
    $ready = $true
    break
  }
  Start-Sleep -Seconds 3
}
if (-not $ready) { throw "WordPress nao respondeu a tempo." }

$installed = Try-Wp core is-installed
if (-not $installed) {
  Write-Host "==> Instalando WordPress..."
  Invoke-Wp core install --url="$SiteUrl" --title="Kra de Rica" --admin_user="$AdminUser" --admin_password="$AdminPass" --admin_email="$AdminEmail" --skip-email
}

Write-Host "==> Idioma pt_BR..."
Try-Wp language core install pt_BR --activate | Out-Null
Try-Wp site switch-language pt_BR | Out-Null

Write-Host "==> Permalinks..."
Invoke-Wp rewrite structure "/%postname%/" --hard
Invoke-Wp rewrite flush --hard

Write-Host "==> WooCommerce e Mercado Pago..."
# Pin: WooCommerce mais novo exige WP >= 6.8; imagem/volume local fica em 6.7.x
Invoke-Wp plugin install woocommerce --version=9.8.5 --activate --force
if (-not (Try-Wp plugin install woocommerce-mercadopago --activate)) {
  Write-Host "Aviso: Mercado Pago nao instalado automaticamente. Instale pelo wp-admin se necessario."
}
Try-Wp language plugin install woocommerce pt_BR | Out-Null

# Credenciais: MP_PUBLIC_KEY / MP_ACCESS_TOKEN ou MERCADOPAGO_* do .env do projeto.
$mpPk = if ($env:MP_PUBLIC_KEY) { $env:MP_PUBLIC_KEY } else { $env:MERCADOPAGO_PUBLIC_KEY }
$mpAt = if ($env:MP_ACCESS_TOKEN) { $env:MP_ACCESS_TOKEN } else { $env:MERCADOPAGO_ACCESS_TOKEN }
$mpTest = if ($env:MP_TEST_MODE) { $env:MP_TEST_MODE } elseif ($env:MERCADOPAGO_SANDBOX -eq "false") { "no" } else { "yes" }
if ($mpPk -and $mpAt) {
  Write-Host "==> Configurando Mercado Pago (Checkout Pro)..."
  $env:MP_PUBLIC_KEY = $mpPk
  $env:MP_ACCESS_TOKEN = $mpAt
  $env:MP_TEST_MODE = $mpTest
  Try-Wp eval-file /scripts/configure-mercadopago.php | Out-Null
} else {
  Write-Host "Aviso: sem credenciais MP_*. Configure em WooCommerce > Pagamentos > Mercado Pago."
}

Write-Host "==> Ativando tema e plugins..."
Invoke-Wp plugin activate vesta-core
Invoke-Wp theme activate vesta

Write-Host "==> Ajustes WooCommerce BR..."
Invoke-Wp option update woocommerce_store_address "Rua Voluntarios da Patria, 233, Loja 05"
Invoke-Wp option update woocommerce_store_city "Curitiba"
Invoke-Wp option update woocommerce_store_postcode "80020310"
Invoke-Wp option update woocommerce_default_country "BR:PR"
Invoke-Wp option update woocommerce_currency "BRL"
Invoke-Wp option update woocommerce_weight_unit "kg"
Invoke-Wp option update woocommerce_dimension_unit "cm"
Invoke-Wp option update woocommerce_enable_guest_checkout "yes"
Invoke-Wp option update woocommerce_enable_signup_and_login_from_checkout "yes"
Invoke-Wp option update woocommerce_enable_myaccount_registration "yes"
Try-Wp wc tool run install_pages --user=1 | Out-Null

function Set-PageSlug([string]$optionKey, [string]$slug, [string]$title) {
  $id = (& docker compose run --rm --entrypoint wp wpcli option get $optionKey --allow-root 2>$null)
  if ($LASTEXITCODE -eq 0 -and $id) {
    $id = $id.ToString().Trim()
    if ($id -ne "" -and $id -ne "0") {
      Invoke-Wp post update $id --post_name=$slug --post_title="$title"
    }
  }
}

Set-PageSlug "woocommerce_shop_page_id" "produtos" "Pecas"
Set-PageSlug "woocommerce_cart_page_id" "carrinho" "Sacola"
Set-PageSlug "woocommerce_checkout_page_id" "finalizar-compra" "Finalizar compra"
Set-PageSlug "woocommerce_myaccount_page_id" "minha-conta" "Minha conta"

# Shortcodes classicos (tema nao usa blocos de cart/checkout)
$cartId = (& docker compose run --rm --entrypoint wp wpcli option get woocommerce_cart_page_id --allow-root 2>$null)
$checkoutId = (& docker compose run --rm --entrypoint wp wpcli option get woocommerce_checkout_page_id --allow-root 2>$null)
if ($cartId) { Invoke-Wp post update $cartId.ToString().Trim() --post_content="[woocommerce_cart]" }
if ($checkoutId) { Invoke-Wp post update $checkoutId.ToString().Trim() --post_content="[woocommerce_checkout]" }
Invoke-Wp option update woocommerce_cart_redirect_after_add no
Invoke-Wp option update woocommerce_enable_ajax_add_to_cart yes
Invoke-Wp option update woocommerce_coming_soon no
Invoke-Wp option update woocommerce_store_pages_only no

function Ensure-Page([string]$slug, [string]$title, [string]$content = "") {
  $existing = (& docker compose run --rm --entrypoint wp wpcli post list --post_type=page --name=$slug --field=ID --allow-root 2>$null)
  if ($LASTEXITCODE -eq 0 -and $existing -and $existing.ToString().Trim() -ne "") {
    return
  }
  if ($content) {
    Invoke-Wp post create --post_type=page --post_title="$title" --post_name=$slug --post_status=publish --post_content="$content"
  } else {
    Invoke-Wp post create --post_type=page --post_title="$title" --post_name=$slug --post_status=publish
  }
}

Ensure-Page "a-loja" "A Loja"
Ensure-Page "contato" "Contato"
Ensure-Page "diario" "Diario"
Ensure-Page "login" "Entrar"
Ensure-Page "cadastro" "Cadastro"
Ensure-Page "recuperar-senha" "Recuperar senha"

$front = (& docker compose run --rm --entrypoint wp wpcli post list --post_type=page --name=inicio --field=ID --allow-root 2>$null)
if (-not $front -or $front.ToString().Trim() -eq "") {
  $front = (& docker compose run --rm --entrypoint wp wpcli post create --post_type=page --post_title="Inicio" --post_name=inicio --post_status=publish --porcelain --allow-root)
}
$front = $front.ToString().Trim()
Invoke-Wp option update show_on_front page
Invoke-Wp option update page_on_front $front

Write-Host "==> Zona de frete Brasil (retirada + a combinar)..."
$zoneId = (& docker compose run --rm --entrypoint wp wpcli wc shipping_zone create --user=1 --name="Brasil" --porcelain --allow-root 2>$null)
if ($LASTEXITCODE -eq 0 -and $zoneId) {
  $zoneId = $zoneId.ToString().Trim()
  Try-Wp wc shipping_zone_location create $zoneId --user=1 --code=BR --type=country | Out-Null
  Try-Wp wc shipping_zone_method create $zoneId --user=1 --method_id=local_pickup | Out-Null
  Try-Wp wc shipping_zone_method create $zoneId --user=1 --method_id=flat_rate | Out-Null
} else {
  Write-Host "Aviso: configure a zona de frete em WooCommerce > Configuracoes > Entrega."
}

Write-Host "==> Seed demo..."
if (-not (Try-Wp vesta seed)) {
  Write-Host "Aviso: seed falhou. Rode depois: docker compose run --rm --entrypoint wp wpcli vesta seed --allow-root"
}

Invoke-Wp rewrite flush --hard

Write-Host ""
Write-Host "============================================"
Write-Host " Kra de Rica pronto em $SiteUrl"
Write-Host " Admin:   $SiteUrl/wp-admin/?wp=1"
Write-Host " Atelier: $SiteUrl/admin/"
Write-Host " Usuario: $AdminUser"
Write-Host "============================================"
