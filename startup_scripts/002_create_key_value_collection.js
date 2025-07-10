import { config } from "dotenv";
import typesense from "typesense";

config();

const typesenseClient = new typesense.Client({
    nodes: [{
        protocol: "http",
        host: process.env.TYPESENSE_HOST || "127.0.0.1",
        port: process.env.TYPESENSE_PORT || 8108
    }],
    apiKey: process.env.TYPESENSE_API_KEY,
});

const COLLECTION_NAME = process.env.TYPESENSE_KEY_VALUE_COLLECTION_NAME || "key_value";

const schema = {
  name: COLLECTION_NAME,
  fields: [
    {
      name: "key",
      type: "string",
      facet: false,
      index: true,
    },
    {
      name: "value",
      type: "string",
      facet: false,
      index: false,
    },
  ],
};

try {
    await typesenseClient.collections().create(schema);
    console.log(`[${COLLECTION_NAME}] Collection created`);
} catch (error) {
    console.log(`[${COLLECTION_NAME}] Collection already exists`);
}
