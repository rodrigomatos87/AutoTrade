<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

session_start();
require_once 'conexao.php';

$sql = "SELECT apiKey, secretKey FROM users WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$apiKey = $row['apiKey'];
$secretKey = $row['secretKey'];

require '../vendor/autoload.php';

use Binance\API;

$client = new Client(
    [
        'apiKey' => 'SUA_API_KEY',
        'apiSecret' => 'SUA_API_SECRET',
    ]
);

$balances = $client->futuresAccountBalance();

foreach ($balances as $balance) {
    echo "{$balance['asset']}: {$balance['balance']}\n";
}