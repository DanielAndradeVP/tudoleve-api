## Docker (backend Laravel)

Suba a API com MySQL e Redis:

```bash
cd tudoleve-api
docker compose up --build
```

Depois acesse:

- `http://localhost:8000/api/v1/...`
- Health rápida: `GET /api/v1/catalog/products` (exemplo)

Notas:
- O Docker usa o `.env` do projeto para variáveis da aplicação.
- `DB_HOST` e `REDIS_HOST` são ajustados automaticamente para apontar para os containers (`db` e `redis`).
- Ao iniciar, o container roda `php artisan migrate` (pode demorar na primeira vez).
