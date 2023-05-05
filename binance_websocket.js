const fs = require("fs");
const path = require("path");
const WebSocket = require("ws");

const processesDir = "processos";
const sockets = {};

function startWebSocket(symbol, outputfile) {
  if (sockets[symbol]) return;

  const ws = new WebSocket(
    `wss://stream.binance.com:9443/ws/${symbol.toLowerCase()}@ticker`
  );

  ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    fs.writeFileSync(outputfile, JSON.stringify(data));
  };

  ws.onclose = () => {
    delete sockets[symbol];
  };

  sockets[symbol] = ws;
}

function stopWebSocket(symbol) {
  if (!sockets[symbol]) return;

  sockets[symbol].close();
  delete sockets[symbol];
}

function checkProcesses() {
  fs.readdir(processesDir, (err, files) => {
    if (err) {
      console.error(err);
      return;
    }

    const newSymbols = new Set();
    files.forEach((file) => {
      if (path.extname(file) === ".json") {
        const processInfo = JSON.parse(
          fs.readFileSync(path.join(processesDir, file), "utf8")
        );
        const symbol = processInfo.symbol;
        const outputfile = processInfo.outputfile;

        if (!sockets[symbol]) {
          startWebSocket(symbol, outputfile);
        }

        newSymbols.add(symbol);
      }
    });

    // Stop WebSockets for removed symbols
    Object.keys(sockets).forEach((symbol) => {
      if (!newSymbols.has(symbol)) {
        stopWebSocket(symbol);
      }
    });
  });
}

// Check processes every 10 seconds
setInterval(checkProcesses, 10000);
checkProcesses();