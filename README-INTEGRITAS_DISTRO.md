## Create and configure .env files

- create .env files where there are .env.example files. (4 instances)

- copy the content from .env.example to the new .env files. Change sensitive values like password.

## Run app

Terminal commands:

(First time only)

- `docker network create integritas-network`

(Start command)

- `docker compose up --pull always --build -d`

## Additional first time setup tasks:

Enter minima node terminal:

Node 1:

- `docker exec -it integritas-distro-minima-1 minima`

- `mysql action:update`

- `mysqlcoins action:update`

- `getaddress` (& send some Minima tokens to your node address.)

- `send address:your-own-address amount:0.1`

- `send address:your-own-address amount:0.00001 split:10` (After you have received Minima tokens, split them up so you have multiple coins. Do this enough times so that you never run out of available coins)

Node 2:

- `docker exec -it integritas-distro-minima-ts-1 minima`

- `mysql action:update`

- `mysqlcoins action:update`

- `getaddress` (& send some Minima tokens to your node address.)

- `send address:your-own-address amount:0.1`

- `send address:your-own-address amount:0.001 split:10` (After you have received Minima tokens, split them up so you have multiple coins)

Enter MinIO admin panel by going to http://{your-host-ip}:9901 in your browser.

- Enter your MINIO credentials (deafault is minioadmin/minioadmin)

- Create these buckets "uploads", "aiuploads", "proofs", "reports"

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
