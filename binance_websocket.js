const WebSocket = require("ws");
const fs = require("fs");

if (process.argv.length < 4) {
  console.error("Usage: node binance_websocket.js <symbol> <outputFile>");
  process.exit(1);
}

const symbol = process.argv[2];
const outputFile = process.argv[3];

const ws = new WebSocket(`wss://stream.binance.com:9443/ws/${symbol.toLowerCase()}@ticker`);

ws.onmessage = (event) => {
  const data = JSON.parse(event.data);

  fs.writeFileSync(outputFile, JSON.stringify(data));
};
