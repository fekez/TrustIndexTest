# TrustIndexTest

Cégértékelő minialkalmazás – Trustindex medior PHP fejlesztői tesztfeladat.

**Stack:** PHP 8.2, Symfony 7.4, Doctrine ORM, PostgreSQL 16, Docker Compose, PHPUnit 11

---

## Követelmények

- Docker Desktop
- Git

---

## Telepítés és indítás

```bash
# 1. Repo klónozása
git clone https://github.com/fekez/TrustIndexTest.git
cd TrustIndexTest

# 2. Környezeti változók
cp .env.example .env

# 3. Docker indítása
docker compose up -d

# 4. Függőségek telepítése
docker compose exec php composer install

# 5. Adatbázis migráció
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction

# 6. Teszt adatok betöltése (opcionális)
docker compose exec php bin/console doctrine:fixtures:load --no-interaction
```

Az alkalmazás elérhető: http://localhost:8080

---

## Funkciók

- **Lista oldal** (`GET /`) – vélemények kártyás listája, csillagos értékeléssel, csonkított szöveggel
- **Új vélemény** (`GET/POST /review/new`) – form validációval, flash üzenettel sikeres mentés után
- **Részletező** (`GET /review/{id}`) – egy vélemény teljes szövege, dátumok, email
- **Cégrangsor** (`GET /companies`) – átlagos értékelés és véleményszám cégenként, csökkenő sorrendben
- **Keresés** (`GET /companies?q=...`) – case-insensitive részleges keresés cég neve alapján
- **Health check** (`GET /health`) – `{"status":"ok","db":"ok","timestamp":"..."}` JSON válasz
- **Strukturált logolás** – minden mentett vélemény JSON formátumban naplózva (`var/log/dev.log`)

---

## Tesztek futtatása

```bash
# Összes teszt (26 db)
docker compose exec php bin/phpunit

# Csak unit tesztek (13 db)
docker compose exec php bin/phpunit --testsuite Unit

# Csak integrációs tesztek (8 db)
docker compose exec php bin/phpunit --testsuite Integration

# Csak funkcionális tesztek (9 db)
docker compose exec php bin/phpunit --testsuite Functional
```

---

## Kódminőség ellenőrzés

```bash
docker compose exec php vendor/bin/php-cs-fixer check --diff
docker compose exec php vendor/bin/phpcs
docker compose exec php vendor/bin/phpstan analyse
```

---

## Munkaidő napló

| Feladat | Idő          |
|---------|--------------|
| M1 – Docker + Symfony skeleton + CI | ~1.5 óra     |
| M2 – Entitás + migráció + lista | ~1.5 óra     |
| M3 – Form + validáció + flash + logolás | ~1 óra       |
| M4 – Részletező + statisztika + keresés + health check | ~0.5 óra     |
| **Összesen** | **~4.5 óra** |
