# Run app

Terminal commands:

- docker network create integritas-network

- docker compose up --pull always --build

## First time setup tasks

Enter minima node terminal:

- docker exec -it integritas-distro-minima-1 minima

- mysql action:update

- mysqlcoins action:update

Enter MinIO admin panel by going to http://{your-host-ip}:9901 in your browser.

- Enter your MINIO credentials (deafault is minioadmin/minioadmin)

- Create a bucket and name it "uploads"

### Gör klart core-api

### Lägg in timestamp server
