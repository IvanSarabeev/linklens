# LinkLens

> Bulk URL import, shortening and link enrichment — a hands-on project for learning async jobs, queues and workers in Symfony 8.

![PHP 8.5](https://img.shields.io/badge/PHP-8.5-777BB4)
![Symfony 8.1](https://img.shields.io/badge/Symfony-8.1-000000)
![License: MIT](https://img.shields.io/badge/license-MIT-green)

## What it does

- Upload a CSV/XLSX with up to **10,000 URLs**; validation and processing run in the background.
- Every URL gets a **short link** and is **enriched**: status code, redirects, title, description, preview image, response time.
- **Exports** (CSV/XLSX) are generated asynchronously and **resume from their last checkpoint** when something fails.
- Short links collect **click stats**; dead links are **re-checked** on a schedule.

## What this project practises

Fan-out / fan-in · idempotent handlers · retries with backoff · failure transport and replay ·
per-domain rate limiting · checkpointed, resumable jobs · scheduled jobs · distributed locks ·
Doctrine vs RabbitMQ transports · OpenTelemetry tracing across queues · chaos testing

## Tech stack

| Area | Tools |
| --- | --- |
| App | PHP 8.5, Symfony 8.1 (Messenger, Scheduler, HttpClient, RateLimiter, Lock), Doctrine ORM 3 |
| Data | PostgreSQL 17, Redis 8, RabbitMQ 4 (from phase 7) |
| Files | Flysystem (local / SeaweedFS S3), league/csv, OpenSpout |
| Observability | OpenTelemetry → Grafana, Tempo, Loki, Prometheus (`grafana/otel-lgtm`), JSON log files |
| Testing | PHPUnit, zenstruck/messenger-test, Foundry, Playwright, k6, PHPStan |
| Tooling | Docker Compose (infrastructure only), Symfony CLI, GitHub Actions |

## How it works

```
POST /api/imports ─202─► ValidateImport ─► ShortenChunk ×N ─► FinalizeImport ─► EnrichLink ×N
POST /api/exports ─202─► StartExport ─► ExportBatch ×N (checkpoint after each) ─► AssembleExport
GET  /{code}      ─302─► RecordClick ─► batch handler ─► daily stats
```

Job state and progress live in PostgreSQL; messages carry only IDs.

## Getting started

**Prerequisites:** PHP 8.5 (intl, pdo_pgsql, zip, sodium), Composer, Symfony CLI, Docker Desktop.

```bash
git clone https://github.com/<your-user>/linklens.git && cd linklens
cp .env.local.dist .env.local
docker compose up -d
composer install
symfony console doctrine:database:create --if-not-exists
symfony console doctrine:migrations:migrate -n
symfony console messenger:setup-transports
symfony server:start -d && symfony open:local
```

| Service | URL |
| --- | --- |
| Grafana (traces, logs, metrics) | http://localhost:3000 |
| Mailpit (test inbox) | http://127.0.0.1:8025 |
| Chaos target (failure injection) | http://127.0.0.1:8090 |

## Useful commands

```bash
make qa      # code style + static analysis + tests
make stats   # queue depth per transport
make logs    # follow the error log
make chaos   # watch requests hitting the chaos target
```

## Roadmap

- [ ] P0 Foundations
- [ ] P1 File storage
- [ ] P2 First async job: validate import
- [ ] P3 Fan-out / fan-in: shorten
- [ ] P4 Slow lane: link enrichment
- [ ] P5 Resumable exports
- [ ] P6 Scheduler and lifecycle jobs
- [ ] P7 RabbitMQ and worker operations
- [ ] P8 Click tracking
- [ ] P9 Hardening, real-time progress, load tests

## License

MIT — see [LICENSE](LICENSE).
