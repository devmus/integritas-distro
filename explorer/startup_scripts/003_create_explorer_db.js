// import { config } from "dotenv";
import mysql from "mysql2/promise";

// config();

const connection = await mysql.createConnection({
  host: process.env.DB_EXPLORER_HOST,
  port: process.env.DB_EXPLORER_PORT,
  user: "root",
  password: process.env.DB_EXPLORER_PASSWORD,
  database: process.env.DB_EXPLORER_DATABASE,
});

await connection.query(`
    CREATE TABLE IF NOT EXISTS \`info\` (
        \`id\` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        \`key\` VARCHAR(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
        \`value\` VARCHAR(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
        PRIMARY KEY (\`id\`),
        UNIQUE KEY \`key\` (\`key\`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
`);

await connection.query(`
    CREATE TABLE IF NOT EXISTS \`txpow\` (
        \`id\` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        \`block\` INT NOT NULL,
        \`txpow_id\` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
        \`size\` INT DEFAULT NULL,
        \`burn\` INT DEFAULT NULL,
        \`is_block\` TINYINT(1) DEFAULT NULL,
        \`is_transaction\` TINYINT(1) DEFAULT NULL,
        \`superblock\` INT DEFAULT NULL,
        \`header\` JSON DEFAULT NULL,
        \`body\` JSON DEFAULT NULL,
        \`datetime\` TIMESTAMP NOT NULL,
        \`on_chain\` INT DEFAULT NULL,
        PRIMARY KEY (\`id\`),
        UNIQUE KEY \`constraints\` (\`txpow_id\`),
        KEY \`txpowid\` (\`txpow_id\`),
        KEY \`block\` (\`block\`),
        KEY \`is_block\` (\`is_block\`),
        KEY \`is_transaction\` (\`is_transaction\`),
        KEY \`datetime\` (\`datetime\`),
        KEY \`on_chain\` (\`on_chain\`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
`);
console.log("Explorer databases initialised");

try {
  await connection.query(`
        INSERT INTO \`info\` (\`key\`, \`value\`)
        VALUES
            ('LAST_TXPOWID', NULL),
            ('MAINTENANCE_MODE', '0')
    `);
  console.log("Info values inserted");
} catch (error) {
  console.error("Info values already exist!");
}

await connection.end();
