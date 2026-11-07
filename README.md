# Kra de Rica — WordPress / WooCommerce

Loja WordPress com identidade **Kra de Rica** (tema + plugins).

## Stack

WordPress · WooCommerce · PHP 8.2 · MySQL 8 · Docker · Mercado Pago

## Subir local

1. Copie [`.env.example`](.env.example) para `.env` e defina `MYSQL_PASSWORD` e `MYSQL_ROOT_PASSWORD`.
2. Suba os containers:

```powershell
docker compose up -d mysql wordpress
.\wordpress\bin\setup.ps1
```

3. Abra http://localhost:8083  
   - Admin WP: http://localhost:8083/wp-admin/?wp=1  
   - **Atelier**: http://localhost:8083/admin/

Detalhes: [wordpress/README.md](wordpress/README.md).

## Estrutura

```
docker-compose.yml
.env.example
wordpress/
  bin/                 # setup local, Mercado Pago, utilitários
  docker/              # init SQL
  wp-content/
    themes/vesta/      # tema (fonte da verdade)
    plugins/vesta-core/
```

Secrets locais (não versionar): `.env`, `.deploy-infinity.env`.
