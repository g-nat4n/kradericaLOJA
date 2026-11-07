# Checklist — Kra de Rica WordPress

Loja local: http://localhost:8083  
Atelier: http://localhost:8083/admin/ — wp-admin só com `?wp=1`

## Storefront

- [ ] Home (hero, novidades, categorias, FAQ, contato/mapa)
- [ ] `/produtos` — catálogo Peças
- [ ] PDP com condição, medidas, marca
- [ ] `/carrinho` — sacola
- [ ] `/finalizar-compra` — frete (retirada / a combinar) + pagamento
- [ ] Mercado Pago (quando houver credenciais)
- [ ] `/minha-conta` — cadastro, login, pedidos
- [ ] `/a-loja`, `/diario`, `/contato`

## Admin

- [ ] Atelier em `/admin`
- [ ] Produtos, pedidos, categorias, clientes
- [ ] Mensagens de contato

## Integrações

- [ ] Mercado Pago configurado
- [ ] Frete a combinar / retirada
- [ ] Peça única esgota após pedido pago

## Comandos

```powershell
docker compose up -d mysql wordpress
.\wordpress\bin\setup.ps1
docker compose run --rm --entrypoint wp wpcli vesta seed --allow-root
```
