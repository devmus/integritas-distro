import { config } from "dotenv";
import typesense from "typesense";

config();

const typesenseClient = new typesense.Client({
  nodes: [
    {
      protocol: "http",
      host: process.env.TYPESENSE_HOST || "127.0.0.1",
      port: process.env.TYPESENSE_PORT || 8108,
    },
  ],
  apiKey: process.env.TYPESENSE_API_KEY,
});

const COLLECTION_NAME = process.env.TYPESENSE_TXPOW_COLLECTION_NAME || "txpow";

const schema = {
  name: COLLECTION_NAME,
  enable_nested_fields: true,
  fields: [
    {
      name: "txpow_id",
      type: "string",
      index: true,
    },
    {
      name: "block",
      type: "string",
      facet: true,
    },
    {
      name: "block_number",
      type: "int64",
      facet: true,
    },
    {
      name: "is_block",
      type: "bool",
      index: true,
    },
    {
      name: "is_transaction",
      type: "bool",
      index: true,
    },
    {
      name: "transactions_in_this_block",
      type: "string[]",
    },
    {
      name: "token_ids",
      type: "string[]",
    },
    {
      name: "input_addresses",
      type: "string[]",
    },
    {
      name: "output_addresses",
      type: "string[]",
    },
    {
      name: "input_mini_addresses",
      type: "string[]",
    },
    {
      name: "output_mini_addresses",
      type: "string[]",
    },
    {
      name: "public_keys",
      type: "string[]",
    },
    {
      name: "number_of_transactions",
      type: "int32",
      index: false,
    },
    {
      name: "superblock_level",
      type: "int32",
      facet: false,
      index: false,
    },
    {
      name: "output_amounts",
      type: "float[]",
      facet: false,
      index: false,
    },
    {
      name: "burn",
      type: "int32",
      index: false,
      facet: false,
    },
    {
      name: "size",
      type: "int32",
      index: false,
      facet: false,
    },
    {
      name: "superblock",
      type: "int32",
      index: false,
      facet: false,
    },
    {
      name: "header",
      type: "object",
      index: false,
      facet: false,
    },
    {
      name: "body",
      type: "object",
      index: false,
      facet: false,
    },
    {
      name: "state_variables",
      type: "string[]",
      facet: true,
    },
    {
      name: "datetime",
      type: "int64",
      index: true,
    },
  ],
};

try {
  await typesenseClient.collections().create(schema);
  console.log(`[${COLLECTION_NAME}] Collection created`);
} catch (error) {
  console.error(error);
  console.log(`[${COLLECTION_NAME}] Collection already exists`);
}
