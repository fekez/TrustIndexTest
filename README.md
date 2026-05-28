# TrustIndexTest

Cégértékelő minialkalmazás – Trustindex medior PHP fejlesztői tesztfeladat.

**Stack:** PHP 8.2, Symfony 7.4, Doctrine ORM, PostgreSQL 16, Docker Compose, PHPUnit 11

![CI](https://github.com/fekez/TrustIndexTest/actions/workflows/ci.yml/badge.svg)

---

## Követelmények

- Docker Desktop
- Git

---

## Telepítés

### 1. Repo klónozása

```bash
git clone https://github.com/fekez/TrustIndexTest.git
cd TrustIndexTest
```

### 2. Környezeti változók

```bash
cp .env.prod.example .env
```

### 3. Konténerek indítása

```bash
docker compose up -d --build
```

### 4. Függőségek telepítése

```bash
docker compose exec php composer install --no-dev --optimize-autoloader --no-scripts
```

### 5. Adatbázis migráció

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### 6. Cache

```bash
docker compose exec php php bin/console cache:clear
docker compose exec php php bin/console cache:warmup
```

Az alkalmazás elérhető: **http://localhost:8080**
Grafana elérhető: **http://localhost:3000** (admin / admin)

> **Gyors telepítés `make`-kel PARANCSSORBÓL ** (Linux / Mac, vagy Windows + `winget install GnuWin32.Make` + `$env:PATH += ";C:\Program Files (x86)\GnuWin32\bin"`):
> ```bash
> make install
> ```

---

## Elérhető végpontok

| Végpont | Leírás |
|---------|--------|
| `GET /` | Vélemények kártyás listája, lapozással |
| `GET /review/new` | Új vélemény form |
| `POST /review/new` | Vélemény mentése |
| `GET /review/{id}` | Vélemény részletező |
| `POST /review/{id}/delete` | Logikai törlés (trash) |
| `GET /companies` | Cégrangsor átlag értékeléssel |
| `GET /companies?q=...` | Cégkeresés (case-insensitive) |
| `GET /health` | `{"status":"ok","db":"ok","timestamp":"..."}` |

---

## Funkciók

- **Lista oldal** – kártyás nézet, csillagos értékelés, csonkított szöveg, lapozás (10/oldal)
- **Új vélemény form** – validáció, flash üzenet, interaktív csillag widget
- **Részletező oldal** – teljes szöveg, dátumok, email
- **Logikai törlés** – `object_state` ENUM, az adat megmarad a DB-ben
- **Cégrangsor** – átlagos értékelés + véleményszám, csökkenő sorrend, lapozás (15/oldal), keresés
- **Health check** – DB kapcsolat ellenőrzés JSON válaszban
- **Rate limiting** – IP-nként max 5 beküldés és törlés / 10 perc
- **Symfony Cache** – cégstatisztika 5 percig gyorsítótárazva, invalidálás mentéskor/törlésekor
- **Strukturált logolás** – Monolog JSON → Loki → Grafana pipeline
- **Grafana** – http://localhost:3000 · Explore → label: `job` → `symfony`

---

## Tesztek futtatása

> A tesztek futtatásához először telepítsd a dev függőségeket:
> ```bash
> docker compose exec php composer install
> ```

```bash
# Összes teszt
docker compose exec php php bin/phpunit

# Rétegenként
docker compose exec php php bin/phpunit --testsuite Unit
docker compose exec php php bin/phpunit --testsuite Integration
docker compose exec php php bin/phpunit --testsuite Functional
```

---

## Kódminőség

> A kódminőség eszközök futtatásához először telepítsd a dev függőségeket:
> ```bash
> docker compose exec php composer install
> ```

```bash
docker compose exec php vendor/bin/php-cs-fixer check --diff
docker compose exec php vendor/bin/phpcs
docker compose exec php vendor/bin/phpstan analyse
```

A CI pipeline minden push-on lefut: lint → cs-fixer → phpcs → security audit → phpstan → tests.

---

## Munkaidő napló

| Feladat | Idő |
|---------|-----|
| M1 – Docker + Symfony skeleton + CI | ~1.5 óra |
| M2 – Entitás + migráció + lista | ~1.5 óra |
| M3 – Form + validáció + flash + logolás | ~1 óra |
| M4 – Részletező + statisztika + keresés + health check | ~0.5 óra |
| M5 – Bónuszok (state, rate limit, pagination, widget, cache) | ~0.75 óra |
| M6 – Loki + Grafana + favicon | ~0.5 óra |
| M7 – Kódminőség + README + prod beállítás | ~0.5 óra |
| **Összesen** | **~6.25 óra** |

---

## Megjegyzések

- CSRF védelem a törlés formnál a `symfony/security-csrf` telepítésével adható hozzá.
- Az `APP_SECRET` értékét éles környezetben cseréld le: `php -r "echo bin2hex(random_bytes(16));"`
