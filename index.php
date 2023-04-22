<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

require_once 'get_crypto_data.php';
require_once 'technical_indicators.php';

$coin_id = 'bitcoin';
$interval = '15min';
$count = 500;

$filename = "historical_data_{$coin_id}_{$interval}.json";
if (file_exists($filename)) {
    // Lê os dados históricos do arquivo
    $json = file_get_contents($filename);
    $data = json_decode($json, true);
} else {
    // Obtenha os dados da coingecko
    $data = get_crypto_historical_data($coin_id, $interval, $count);
    // Salva os dados históricos em um arquivo
    file_put_contents($filename, json_encode($data));
}

function save_opportunity_to_file($opportunity, $filename) {
    $file = fopen($filename, 'a');
    fwrite($file, $opportunity . PHP_EOL);
    fclose($file);
}

echo "prices: " . count($data['prices']) . "<br>";
echo "market_caps: " . count($data['market_caps']) . "<br>";
echo "total_volumes: " . count($data['total_volumes']) . "<br>";

exit;

// Calcule os indicadores
$ema50 = ema($data, 50);
$ema200 = ema($data, 200);
$rsi = rsi($data, 14);
$bb = bollingerBands($data, 20, 2);
$macd = macd($data, 12, 26, 9);

echo '<pre>';
print_r($ema50);
print_r($ema200);
print_r($rsi);
print_r($macd);
print_r($bb);
echo '</pre>';

// Verificar sinais de compra e venda
for ($i = max(50, 200, 14, 20); $i < count($data['prices']); $i++) {
    $price = $data['prices'][$i][1];

    // Regras de entrada para compra (CALL)
    if (isset($ema50[$i], $ema200[$i], $rsi[$i], $macd['macd'][$i], $macd['signal'][$i], $bb['lower'][$i]) && $price > $ema50[$i] && $price > $ema200[$i] && $rsi[$i] < 30 && $macd['macd'][$i] > $macd['signal'][$i] && $price <= $bb['lower'][$i]) {
        $opportunity = "Sinal de COMPRA (CALL) na barra $i";
        save_opportunity_to_file($opportunity, 'oportunidades.txt');
    }

    // Regras de entrada para venda (PUT)
    if (isset($ema50[$i], $ema200[$i], $rsi[$i], $macd['macd'][$i], $macd['signal'][$i], $bb['upper'][$i]) && $price < $ema50[$i] && $price < $ema200[$i] && $rsi[$i] > 70 && $macd['macd'][$i] < $macd['signal'][$i] && $price >= $bb['upper'][$i]) {
        $opportunity = "Sinal de VENDA (PUT) na barra $i";
        save_opportunity_to_file($opportunity, 'oportunidades.txt');
    }


    // Implemente as regras de saída de acordo com sua tolerância ao risco e objetivos de lucro
}

?>


