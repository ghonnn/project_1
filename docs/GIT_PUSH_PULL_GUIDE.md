# Push GitHub lalu Pull dan Deploy Server

# Data Server
ip server : 10.20.1.32
user : nex
password : cMZQmVoB4jcX6TO/wVlHBA==
## Kata Kunci

```text
UPDATE PRODUCTION
```

Artinya: push perubahan terbaru ke GitHub, lalu pull dan deploy ke server production.

## 1. Push ke GitHub dari Lokal

```bash
cd /path/project/nexbil
git status
git pull origin feat/mvp-phase-1-3-backend
git add .
git commit -m "update"
git push origin feat/mvp-phase-1-3-backend
```

## 2. Pull dan Deploy di Server

```bash
ssh root@IP_SERVER
cd /var/www/nex-oss-bss
git fetch origin
git checkout feat/mvp-phase-1-3-backend
git pull origin feat/mvp-phase-1-3-backend
docker compose up -d --build
docker compose exec api php artisan migrate --force
docker compose exec api php artisan optimize:clear
docker compose exec api php artisan view:clear
```

## 3. Cek Server

```bash
cd /var/www/nex-oss-bss
docker compose ps
docker compose logs -f api
```

## 4. Jika Pull Gagal Karena Ada Perubahan Lokal di Server

```bash
cd /var/www/nex-oss-bss
git stash push -m "backup sebelum pull"
git pull origin feat/mvp-phase-1-3-backend
docker compose up -d --build
docker compose exec api php artisan migrate --force
docker compose exec api php artisan optimize:clear
docker compose exec api php artisan view:clear
```
