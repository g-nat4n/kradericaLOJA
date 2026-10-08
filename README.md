# Vesta Moda Pre-Owned

E-commerce full-stack em Next.js para uma loja de moda pre-owned. Cada peça pode ser única: ao ser vendida, deixa de ficar disponível.

## Stack

Next.js · TypeScript · Tailwind CSS · MySQL · Prisma · Auth.js · Mercado Pago · Cloudinary

## Desenvolvimento

1. Copie `.env.example` para `.env` e ajuste as chaves.
2. Suba o MySQL: `docker compose up -d` (porta `3307` para não conflitar com um MySQL local)
3. Instale as dependências: `npm install`
4. Aplique o schema: `npx prisma db push`
5. Popule dados de demonstração: `npm run db:seed`
6. Rode o app: `npm run dev`

`DATABASE_URL` local de exemplo:

`mysql://vesta:SENHA@localhost:3307/vesta`

Conta administrativa do seed:

- e-mail: `admin@vestamoda.com`
- senha: valor de `ADMIN_PASSWORD` no `.env`

Sem credenciais do Mercado Pago o pedido é criado como pendente. Sem token do Melhor Envio, o checkout oferece retirada combinada — cotações de envio não são inventadas.
