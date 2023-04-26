<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

require_once 'get_crypto_data.php';
require_once 'technical_indicators.php';

$symbol = "BTCUSDT";
$interval = "1m";
$candles_count = 5000;

$current_time = time();
$current_hour = intval(date('H', $current_time));
$current_minute = intval(date('i', $current_time));

$interval_mapping = [
    '1m' => 60,
    '3m' => 180,
    '5m' => 300,
    '15m' => 900,
    '30m' => 1800,
    '1h' => 3600,
    '2h' => 7200,
    '4h' => 14400,
    '6h' => 21600,
    '8h' => 28800,
    '12h' => 43200,
    '1d' => 86400,
];

// Calcule o número total de minutos desde o início do dia
$total_minutes_today = $current_hour * 60 + $current_minute;

// Calcule o número de candles no dia atual com base no intervalo
$interval_minutes = $interval_mapping[$interval] / 60;
$candles_today = floor($total_minutes_today / $interval_minutes);

$historical_candles_count = $candles_count - $candles_today;

$historical_data = download_and_extract_data($symbol, $interval, $historical_candles_count);
$latest_data = get_latest_data($symbol, $interval, $candles_today);

// Combine os dados históricos e os mais recentes
$all_data = array_merge($historical_data, $latest_data);

// Retorne apenas os últimos candles_count dados
$data = array_slice($all_data, -$candles_count);

echo "historical_data: " . count($historical_data) . "<br>";
echo "latest_data: " . count($latest_data) . "<br>";
echo "total: " . count($data) . "<br>";

/*
foreach ($data as $row) {
    list($timestamp, $open, $high, $low, $close, $volume) = $row;
    echo "Timestamp: {$timestamp}, Open: {$open}, High: {$high}, Low: {$low}, Close: {$close}, Volume: {$volume}<br>";
}
*/

function save_opportunity_to_file($opportunity, $filename) {
    $file = fopen($filename, 'a');
    fwrite($file, $opportunity . PHP_EOL);
    fclose($file);
}

// Execute a função MACD com os períodos desejados (por exemplo, 12, 26 e 9)
$short_period = 12;
$long_period = 26;
$signal_period = 9;

// Calcule os indicadores
$sma = simple_moving_average($data, 20);
$ema = exponential_moving_average($data, 20);
$macd = moving_average_convergence_divergence($data, $short_period, $long_period, $signal_period);

// Exiba os resultados
//echo "MACD: " . json_encode($macd['macd']) . "<br>";
//echo "Signal Line: " . json_encode($macd['signal_line']) . "<br>";
//echo "Histogram: " . json_encode($macd['histogram']) . "<br>";

// Calcule as Bandas de Bollinger
$period = 20;
$num_standard_deviations = 2;
$bollinger_bands = bollinger_bands($data, $period, $num_standard_deviations);

$k_period = 14;
$d_period = 3;
$stoch = stochastic_oscillator($data, $k_period, $d_period);

$rsi = relative_strength_index($data, 14);

//echo '<pre>';
//print_r($sma);
//print_r($ema);
//print_r($bollinger_bands);
//print_r($macd);
//print_r($stoch);
//print_r($rsi);
//echo '</pre>';

?>