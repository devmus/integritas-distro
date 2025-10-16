### INTEGRITAS DISTRIBUTED API

# PREREQUISITES

- Docker Desktop (or Docker Engine) installed and running

- Git installed

- A GitHub Personal Access Token (PAT) with read:packages scope to pull your GHCR images.

  - Save it somewhere handy; we’ll call it GHCR_PAT.

1. Clone the distro repo:

- `git clone https://github.com/devmus/integritas-distro.git`
- `cd integritas-distro`
- `docker network create integritas-network`

---

2. Create and configure .env files

- create .env files where there are .env.example files. (4 instances: @., @./core-api, @./search-api, @./timestamp)

- copy the content from .env.example to the new .env files. Change sensitive values like password.

---

4. Authenticate to GHCR

```
$env:GHCR_PAT="PASTE_YOUR_TOKEN_HERE"
$env:GHCR_PAT | docker login ghcr.io -u devmus --password-stdin
```

---

## Run app

(Start command)

- `docker compose up --pull always --build -d`

- Ports exposed: 5005

---

## Additional first time setup tasks after app has started:

Enter minima node terminal:

Node 1:

- `docker exec -it integritas-distro-minima-1 minima`

- `mysql action:update`

- `mysqlcoins action:update`

- `getaddress` (copy miniaddress & send some Minima tokens to your node address.)

- `send address:your-own-address amount:0.1`

- `send address:your-own-address amount:0.00001 split:10` (After you have received Minima tokens, split them up so you have multiple coins. Do this enough times so that you never run out of available coins for NFT report creation.)

Node 2:

- `docker exec -it integritas-distro-minima-ts-1 minima`

- `getaddress` (copy miniaddress & send some Minima tokens to your node address.)

- `send address:your-own-address amount:0.1`

- `send address:your-own-address amount:0.001 split:10` (After you have received Minima tokens, split them up so you have multiple coins. 10 coins should be enough to always have available coins to use for creating timestamp transactions.)

---

Enter MinIO admin panel by going to http://{your-host-ip}:9901 in your browser.

- Enter your MINIO credentials (deafault is minioadmin/minioadmin)

- Create these buckets "uploads", "aiuploads", "proofs", "reports"

---

## If needed: Stop app

- docker compose down

# remove volumes/data ONLY if you want a fresh DB:

# rm -rf ./timestamp/mysql-data

# rm -rf ./explorer/mysql-data

# rm -rf ./explorer/typesense-data

# rm -rf ./minima-meg/mysql-data

# rm -rf ./minima-meg/data

# rm -rf ./minima-meg/backups

# rm -rf ./core-api/... (mongodb)

7. Verify everything is up
   docker ps

You should see containers like:

integritas-distro-core-api-1

integritas-distro-search-api-1

integritas-distro-mongodb-1

integritas-distro-minio-1

integritas-distro-minima-1

integritas-distro-minima-ts-1 (dedicated for timestamp server)

integritas-distro-timestamp-web-1

integritas-distro-timestamp-php-1

integritas-distro-timestamp-mysql-1 (healthy)

integritas-distro-timestamp-cron-1

If something is restarting, check logs:

docker compose logs -f <service-name>

8. Quick smoke tests (local)

Core-API version
http://localhost:5005/v1/version

(Should return JSON with version, gitSha, buildDate.)

Mongo Express
http://localhost:8081
(UI)

MinIO Console
http://localhost:9901
(user/pass in .env)

Timestamp server UI
http://localhost:3010

Timestamp server API (example)
GET http://localhost:3010/timestampapi.php?tsdata=0xABCDEF
(Should return JSON with a uid and stored data.)

9. Ports used (make sure they’re free)

Core-API: 5005

Search-API: 9998

MongoDB: 27017

Mongo Express: 8081

MinIO: 9900 (API), 9901 (console)

Minima (main): 9001/9003/9005

Minima (timestamp): 9999/9905

MySQL (MEG): 3310

MySQL (timestamp): 3311

Explorer Typesense: from .env (default exposed as 8200)

Explorer MySQL: from .env (default exposed as 8201)

Explorer Lite UI: from .env (default 3001)

If a port is taken, stop whatever is using it or adjust in compose/.env and re-up.

10. Common troubleshooting

GHCR pulls fail (401/403)
Re-run the docker login ghcr.io step with a valid PAT (read:packages).
Make sure you’re pulling the correct image tags specified in the repo’s compose.

timestamp-mysql unhealthy or “password not specified”
Your .env is missing TIMESTAMP*DB*\* values. Verify with:

docker compose config | grep TIMESTAMP_DB -n

Then docker compose down -v (wipes volumes) and bring up again.

Cron can’t find bootstrap.php
Ensure the compose mounts ./timestamp/app:/app:ro into the cron container (your compose already does).
The cron job should call /usr/local/bin/php /app/public/runts.php (or test_cron.php if you kept that), not /cron_scripts/bootstrap.php.

Two Minima nodes conflict
You already mapped main Minima to 9001/9003/9005 and timestamp Minima to 9999/9905. Don’t run other apps on these ports.

Reset everything (careful: deletes data)

docker compose down -v
docker network rm integritas-network
docker network create integritas-network
docker compose up --pull always --build -d

11. Daily usage

Start:

docker compose up -d

Stop:

docker compose down

Update to latest images without rebuilding:

docker compose pull
docker compose up -d

Tail logs:

docker compose logs -f core-api
