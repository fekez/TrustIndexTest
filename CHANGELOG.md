# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [0.4.0] - 2026-05-27

### Added (M4 – Részletező + statisztika + keresés + health check)
- `GET /review/{id}` – részletező oldal, 404 ha nem létezik (`requirements: ['\d+']`)
- `GET /companies` – táblázatos cégrangsor: véleményszám + átlagos értékelés, csökkenő sorrend
- `GET /companies?q=...` – case-insensitive ILIKE keresés cég neve alapján
- `GET /health` – `{"status":"ok","db":"ok","timestamp":"..."}` JSON válasz; 503 ha DB error
- `ReviewRepository::getCompanyStats(?string $search)` – keresési paraméter támogatás hozzáadva
- `templates/review/show.html.twig` – Bootstrap kártyás részletező, csillagok, email, dátumok
- `templates/companies/index.html.twig` – táblázatos nézet, keresőmező, üres/találat nélküli állapot
- `templates/bundles/TwigBundle/Exception/error404.html.twig` – egyedi 404 oldal, Bootstrap stílusban
- `templates/bundles/TwigBundle/Exception/error.html.twig` – egyedi általános hibaoldal (5xx)
- `tests/Functional/ReviewFormTest` – 5 új funkcionális teszt M4 route-okhoz:
    - `testShowReviewReturns200` – részletező 200 + cégnév látszik
    - `testShowReviewReturns404ForUnknownId` – 999999 → 404
    - `testCompaniesPageReturns200` – /companies 200 + táblázat
    - `testCompaniesSearchReturnsFilteredResults` – ?q=filter szűr
    - `testHealthCheckReturnsOk` – JSON struktúra + status=ok

## [0.3.0] - 2026-05-27

### Added (M3 – Form + validáció + flash + strukturált logolás)
- `src/Form/ReviewType.php` – Symfony form osztály (TextType, ChoiceType, EmailType, TextareaType)
- `POST /review/new` – form feldolgozás, perzisztálás, redirect + flash üzenet
- Validációs constraint-ek a `Review` entitáson: `NotBlank`, `Length`, `Range`, `Email`
- Flash üzenet sikeres mentés után (`alert-success`)
- Monolog JSON formatter dev és prod környezetben; `review.created` strukturált log bejegyzés
- `templates/base.html.twig` – Bootstrap 5.3 layout, navbar, flash megjelenítés
- `templates/review/index.html.twig` – végleges Bootstrap kártyás lista, csillagok, csonkított szöveg
- `templates/review/new.html.twig` – form oldal Bootstrap stílussal, inline validációs hibák
- `tests/Unit/ReviewValidationTest` – 8 unit teszt: boundary értékek, email formátum, kötelező mezők
- `tests/Functional/ReviewFormTest` – 4 funkcionális teszt: GET /, GET /review/new, valid submit, invalid submit

### Changed
- `src/Entity/Review.php` – property típusok `?string`-re lazítva a form null-kezeléséhez
- `config/packages/monolog.yaml` – JSON formatter hozzáadva dev handler-hez

## [0.2.0] - 2026-05-27

### Added (M2 – Entitás + migráció + primitív lista)
- `Review` entitás – PHP attribute Doctrine mapping, lifecycle callbacks (`created_at`, `updated_at`)
- `ReviewRepository` – `findAllOrderedByDate()`, `getCompanyStats()`
- Doctrine migration generálva és futtatva
- `AppFixtures` – 10 teszt review, 4 cégnév (Acme Corp, Beta Solutions, Gamma Tech, Delta Services)
- `GET /` – Twig lista, csillagok, csonkított szöveg, dátum
- `tests/Unit/Repository/ReviewRepositoryTest` – 3 unit teszt a normalizációs logikára
- `tests/Integration/Entity/ReviewPersistenceTest` – 2 integrációs teszt valódi DB-vel
- `README.md` – telepítési útmutató, tesztfuttatás, munkaidő napló
- `phpstan/phpstan-doctrine` extension hozzáadva

### Changed
- `phpunit.xml.dist` – bootstrap `tests/bootstrap.php`-ra javítva, `KERNEL_CLASS` hozzáadva, `APP_ENV` `<env>` tagra cserélve
- `phpcs.xml.dist` – `DataFixtures` könyvtár kizárva a line length rule alól
- `docker-compose.yml` – Adminer eltávolítva
- `scripts/pre-commit` – PHPUnit futtatás hozzáadva 5. lépésként

## [0.1.0] - 2026-05-26

### Added (M1 – Docker + Symfony skeleton + CI alap + projekt konfig)
- Docker Compose stack: PHP 8.2-fpm, Nginx 1.25, PostgreSQL 16, Adminer
- Xdebug konfiguráció fejlesztői környezethez
- `.editorconfig` – egységes indentáció és charset minden IDE-ben
- `.gitignore` – `.env` gitignore-ban, `.env.example` és `.env.test` gitben
- `.env.example` – minden környezeti változó dokumentálva, példa értékekkel
- `.env.test` – test környezet alapértelmezett értékei
- `.php-cs-fixer.php` – `@Symfony` ruleset + `declare_strict_types`
- `phpstan.neon` – level 6, phpstan-symfony extension
- `phpunit.xml.dist` – Unit / Integration / Functional testsuite szeparáció
- `tests/Unit`, `tests/Integration`, `tests/Functional` – könyvtárstruktúra `.gitkeep` fájlokkal
- `Makefile` – fejlesztői parancsok rövidítve
- `.github/workflows/ci.yml` – lint, cs-fixer, phpcs, security párhuzamosan; phpstan és tests sorban utánuk
- `CHANGELOG.md` – Keep a Changelog formátum
