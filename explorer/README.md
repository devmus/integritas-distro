## Minima Explorer Lite

Minima Explorer Lite is a lightweight explorer for the Minima blockchain. It allows you to search for transactions on the Minima blockchain.

### Pre-requisites

- Ensure you have docker installed.
- Please run meganode [https://github.com/minima-global/meganode](https://github.com/minima-global/meganode) by cloning the repo and running the docker compose file
  - We recommend editing the docker compose file to include shared-network for each service.
    - Example:
      ```
      minima:
          image: minimaglobal/minima:dev
          # ... blanked out the rest of the minima config
          networks:
              - my-shared-network
      mysql:
          image: mysql:8.4
          # ... blanked out the rest of the mysql config
          networks:
              - my-shared-network
      meg:
          image: minimaglobal/meg:dev
          # ... blanked out the rest of the meg config
          networks:
              - my-shared-network
      ```
  - You will also have to add the shared network at the bottom of the file
    - Example:
      ```
      networks:
          my-shared-network:
              external: true
      ```
  - Finally, we recommend storing the mysql data in a volume to persist the txpow data when having to destroy and recreate the docker container.
    - Example:
      ```
      mysql:
          image: mysql:8.4
          # ... blanked out the rest of the mysql config
          volumes:
              - ./mysql-data:/var/lib/mysql
      ```

### How to run

- Create a shared network by running `docker network create my-shared-network` or run `npm run docker:create:shared-network`
- Fill out the `.env` file with the correct values.
- Run the following `docker compose up -d` command to start the explorer-lite container.

  - Since we're using a shared network, there are some values that need to be named after the name for service:

    - Example:

      ```
          TYPESENSE_PORT=8200 # exposed typesense port
          MYSQL_PORT=8201 # exposed mysql port
          EXPLORER_WEB_API_PORT=3001 # exposed explorer web api port

          DB_MINIMA_HOST=mysql
          DB_MINIMA_PORT=3306
          DB_MINIMA_USER=minimauser
          DB_MINIMA_PASSWORD=
          DB_MINIMA_DATABASE=minimadb

          DB_EXPLORER_ROOT_PASSWORD=
          DB_EXPLORER_USER=user
          DB_EXPLORER_PASSWORD=
          DB_EXPLORER_DATABASE=explorer

          TYPESENSE_API_KEY=xyz
          TYPESENSE_TXPOW_COLLECTION_NAME=txpow

          MINIMA_HOSTS=minima:9005
      ```

- Once everything is running, you can access the explorer-lite api at `http://localhost:3001/api/search?q=your_query`
- Only data that is available in the minima sql database and is validatable will be indexed into typesense
