# NEX OSS/BSS - Local Development

## Start Docker

```bash
docker compose up -d --build
```

## Enter API Container

```bash
docker exec -it nex_api bash
```

## Install and Prepare

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
php artisan serve --host=0.0.0.0 --port=8000
```

## Health

```bash
curl http://localhost:8000/api/v1/health
```

## Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@nex.local","password":"password"}'
```

## Local Services

- API: `http://localhost:8000`
- PostgreSQL: `localhost:5432`
- Redis: `localhost:6379`
- pgAdmin: `http://localhost:8080`

## RND Targets

- App/API RND private IP: `10.20.1.32`
- FreeRadius RND private IP: `10.20.1.19`
- MikroTik testing public IP: `103.142.202.226`
