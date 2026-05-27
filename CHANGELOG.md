# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

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
