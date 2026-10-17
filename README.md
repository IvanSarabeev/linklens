# LinkLens

Bulk URL import, shortening and link enrichment — a hands-on project for learning
async jobs, queues and workers in Symfony 8.

## What it does
- Import CSV/XLSX with up to 10,000 URLs, processed in the background
- Short link + enrichment (status, redirects, title, preview) for every URL
- Async CSV/XLSX exports that resume from their last checkpoint
- Click stats and scheduled re-checks of dead links

## Tech stack
PHP 8.5 · Symfony 8.1 (Messenger, Scheduler) · Doctrine ORM 3 · PostgreSQL 17 · Redis 8 ·
RabbitMQ 4 · Flysystem · OpenTelemetry + Grafana · Docker Compose · PHPUnit · GitHub Actions

## Getting started
    git clone https://github.com/<your-user>/linklens.git && cd linklens
    docker compose up -d && composer install
    symfony console doctrine:database:create --if-not-exists
    symfony console messenger:setup-transports
    symfony server:start -d && symfony open:local

## Roadmap
- [ ] P0 Foundations  - [ ] P1 Storage  - [ ] P2 Validate import  - [ ] P3 Shorten
- [ ] P4 Enrich  - [ ] P5 Resumable exports  - [ ] P6 Scheduler  - [ ] P7 RabbitMQ
- [ ] P8 Click tracking  - [ ] P9 Hardening

## License
MIT — see LICENSE.
