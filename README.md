# 🚀 Integritas Distributed API

The **Integritas Distributed API** is a full-stack Minima-based application suite, including the Core API, Search API, Timestamp Server, Explorer, and supporting infrastructure — all containerized and ready to run via Docker Compose.

---

## 🧱 Prerequisites

Before starting, make sure you have:

- **Docker Desktop** (or Docker Engine) installed and running.
- **Git** installed.
- A **GitHub Personal Access Token (PAT)** with `read:packages` scope to pull private GHCR images.
  - Save it somewhere safe; we’ll call it `GHCR_PAT`.

---

## ⚙️ Setup Guide

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/devmus/integritas-distro.git
cd integritas-distro
docker network create integritas-network
```

---

### 2️⃣ Configure Environment Files

Create `.env` files wherever `.env.example` files exist (there should be **four**):

| Location            | Purpose                          |
| ------------------- | -------------------------------- |
| `./.env`            | Global environment configuration |
| `./core-api/.env`   | Core API service config          |
| `./search-api/.env` | Search API service config        |
| `./timestamp/.env`  | Timestamp service config         |

For each, copy from the example and update sensitive values (e.g., passwords):

```bash
cp .env.example .env
cp core-api/.env.example core-api/.env
cp search-api/.env.example search-api/.env
cp timestamp/.env.example timestamp/.env
```

---

### 3️⃣ Authenticate with GHCR

You need to log in to the GitHub Container Registry to pull private images:

**PowerShell (Windows):**

```powershell
$env:GHCR_PAT="PASTE_YOUR_TOKEN_HERE"
$env:GHCR_PAT | docker login ghcr.io -u devmus --password-stdin
```

**macOS/Linux:**

```bash
echo "$GHCR_PAT" | docker login ghcr.io -u devmus --password-stdin
```

---

### 4️⃣ Start the Application

Run the entire stack with:

```bash
docker compose up --pull always --build -d
```

- Automatically pulls the latest images from GHCR.
- Builds local images (`timestamp-php`, `timestamp-cron`).
- Starts all services in detached mode.

> Exposed ports (key services):  
> **Core API:** 5005  
> **Search API:** 9998

---

## 🧩 First-Time Setup (After Containers Are Running)

### 🪙 Minima Node Configuration

#### Node 1 (Main)

```bash
docker exec -it integritas-distro-minima-1 minima
```

Inside the Minima CLI:

```bash
mysql action:update
mysqlcoins action:update
getaddress
send address:your-own-address amount:0.1
send address:your-own-address amount:0.00001 split:10
```

> 💡 Split your tokens into multiple coins to ensure enough UTXOs are available for NFT/report creation.

#### Node 2 (Timestamp Node)

```bash
docker exec -it integritas-distro-minima-ts-1 minima
```

Inside the Minima CLI:

```bash
getaddress
send address:your-own-address amount:0.1
send address:your-own-address amount:0.001 split:10
```

> The timestamp node only needs ~10 small coins to create timestamp transactions.

---

### 🗄️ Configure MinIO

Open the MinIO console in your browser:  
👉 [http://localhost:9901](http://localhost:9901)

Login with credentials from your `.env` file (default: `minioadmin/minioadmin`).

Create the following buckets:

- `uploads`
- `aiuploads`
- `proofs`
- `reports`

---

## 🧠 Verification

Run:

```bash
docker ps
```

You should see containers such as:

```
integritas-distro-core-api-1
integritas-distro-search-api-1
integritas-distro-mongodb-1
integritas-distro-minio-1
integritas-distro-minima-1
integritas-distro-minima-ts-1
integritas-distro-timestamp-web-1
integritas-distro-timestamp-php-1
integritas-distro-timestamp-mysql-1
integritas-distro-timestamp-cron-1
```

If a service keeps restarting:

```bash
docker compose logs -f <service-name>
```

---

## 🧪 Smoke Tests (Local)

| Service                | URL                                                                                                              | Expected Output                      |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------- | ------------------------------------ |
| **Core API (version)** | [http://localhost:5005/v1/version](http://localhost:5005/v1/version)                                             | JSON with version, gitSha, buildDate |
| **Mongo Express UI**   | [http://localhost:8081](http://localhost:8081)                                                                   | Web admin interface                  |
| **MinIO Console**      | [http://localhost:9901](http://localhost:9901)                                                                   | S3 buckets management                |
| **Timestamp Web UI**   | [http://localhost:3010](http://localhost:3010)                                                                   | HTML front page                      |
| **Timestamp API Test** | [http://localhost:3010/timestampapi.php?tsdata=0xABCDEF](http://localhost:3010/timestampapi.php?tsdata=0xABCDEF) | JSON with `uid` and `data`           |

---

## 🌐 Ports Overview

| Service            | Port(s)            | Description              |
| ------------------ | ------------------ | ------------------------ |
| Core API           | 5005               | Main API                 |
| Search API         | 9998               | Search service           |
| MongoDB            | 27017              | Database                 |
| Mongo Express      | 8081               | Admin UI                 |
| MinIO              | 9900 / 9901        | S3 API / Console         |
| Minima (main)      | 9001 / 9003 / 9005 | Blockchain node          |
| Minima (timestamp) | 9999 / 9905        | Dedicated timestamp node |
| MySQL (MEG)        | 3310               | Database                 |
| MySQL (timestamp)  | 3311               | Timestamp DB             |
| Explorer Typesense | 8200 (default)     | Search index             |
| Explorer MySQL     | 8201 (default)     | Explorer DB              |
| Explorer Lite UI   | 3001 (default)     | Web UI                   |

If ports are already in use, adjust them in `.env` or `docker-compose.yml`.

---

## 🧰 Troubleshooting

### GHCR Pull Fails (`401` / `403`)

Re-authenticate:

```bash
docker login ghcr.io -u devmus --password-stdin
```

Make sure the PAT has `read:packages`.

---

### `timestamp-mysql` unhealthy / missing password

Ensure your `.env` includes:

```
TIMESTAMP_DB_USER
TIMESTAMP_DB_PASSWORD
TIMESTAMP_DB_ROOT_PASSWORD
```

Then reset:

```bash
docker compose down -v
docker compose up --build -d
```

---

### Cron cannot find `bootstrap.php`

Ensure the compose mounts:

```
./timestamp/app:/app:ro
```

and that the cron job runs:

```
php /app/public/runts.php
```

---

### Two Minima nodes conflicting

Main node uses ports `9001/9003/9005`.  
Timestamp node uses `9999/9905`.  
Never expose both on the same port.

---

### Full reset (⚠️ deletes data)

```bash
docker compose down -v
docker network rm integritas-network
docker network create integritas-network
docker compose up --pull always --build -d
```

---

## 🔁 Daily Usage

| Action                      | Command                                       |
| --------------------------- | --------------------------------------------- |
| **Start all services**      | `docker compose up -d`                        |
| **Stop all services**       | `docker compose down`                         |
| **Update to latest images** | `docker compose pull && docker compose up -d` |
| **View logs**               | `docker compose logs -f core-api`             |

---

## 📜 License

© 2025 Integritas — All rights reserved.
