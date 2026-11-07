# Kra de Rica — WordPress / WooCommerce

Loja: catálogo, peça única, checkout, Mercado Pago, conta do cliente e Atelier (`/admin`).

## Requisitos

- Docker Desktop
- Variáveis `MYSQL_PASSWORD` e `MYSQL_ROOT_PASSWORD` no `.env` da raiz do repo

## Subir o ambiente

Na raiz do projeto:

```powershell
docker compose up -d mysql wordpress
.\wordpress\bin\setup.ps1
```

Linux/macOS / Git Bash:

```bash
docker compose up -d mysql wordpress
docker compose run --rm --entrypoint bash wpcli /scripts/setup.sh
```

Loja local: http://localhost:8083

## Credenciais (setup)

Defina no `.env` antes do setup:

- `WP_ADMIN_USER` / `WP_ADMIN_PASSWORD` / `WP_ADMIN_EMAIL`

Atelier: http://localhost:8083/admin/  
wp-admin técnico: http://localhost:8083/wp-admin/?wp=1

## O que é instalado

- **Tema** `vesta` — visual da loja (slug interno herdado; marca Kra de Rica)
- **Plugin** `vesta-core` — peça única, looks, Atelier `/admin`, contato, seed
- **WooCommerce** + **Mercado Pago** (ativar com chaves no admin)
- Frete: retirada na loja + frete a combinar
- URLs: `/produtos`, `/carrinho`, `/finalizar-compra`, `/minha-conta`, `/a-loja`, `/contato`

## Mercado Pago

1. WooCommerce → Pagamentos → Mercado Pago  
2. Public Key + Access Token  
3. Ou: `.\wordpress\bin\configure-mercadopago.php` com `MP_PUBLIC_KEY` / `MP_ACCESS_TOKEN` no `.env`

## Seed

```bash
docker compose run --rm --entrypoint wp wpcli vesta seed --allow-root
```

## Estrutura

```
wordpress/
  bin/                 setup, Mercado Pago, utilitários
  docker/              init SQL (vesta_wp)
  wp-content/themes/vesta/
  wp-content/plugins/vesta-core/
  README.md / CHECKLIST.md
```
