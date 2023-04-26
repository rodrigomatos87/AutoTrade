<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

require_once 'get_crypto_data.php';
require_once 'technical_indicators.php';

$symbol = "BTCUSDT";
$interval = "1m";
$candles_count = 30000;

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

// Calcule os indicadores
$ema_50 = exponential_moving_average($data, 50);
$ema_200 = exponential_moving_average($data, 200);
$rsi = relative_strength_index($data, 14);
$macd = moving_average_convergence_divergence($data, 12, 26, 9);    // (data, short_period, long_period, signal_period)
$bollinger_bands = bollinger_bands($data, 20, 2);                   // (data, period, num_standard_deviations)
$stochastic = stochastic_oscillator($data, 14, 3);                  // (data, k_period, d_period)

//echo '<pre>';
//print_r($sma);
//print_r($ema_50);
//print_r($bollinger_bands);
//print_r($macd);
//print_r($stoch);
//print_r($rsi);
//echo '</pre>';

$last_index = count($data) - 1;

for ($i = max(200, 14); $i < $last_index; $i++) {
    $is_up_trend = $ema_50[$i] > $ema_200[$i];
    $is_down_trend = $ema_50[$i] < $ema_200[$i];
    $rsi_above_50 = $rsi[$i] > 50;
    $rsi_below_50 = $rsi[$i] < 50;
    $macd_cross_above_signal = $macd['macd'][$i] > $macd['signal'][$i] && $macd['macd'][$i - 1] <= $macd['signal'][$i - 1];
    $macd_cross_below_signal = $macd['macd'][$i] < $macd['signal'][$i] && $macd['macd'][$i - 1] >= $macd['signal'][$i - 1];
    $price_near_lower_band = $data[$i][4] <= $bollinger_bands[$i]['lower'];
    $price_near_upper_band = $data[$i][4] >= $bollinger_bands[$i]['upper'];
    $stoch_k_cross_above_d = $stochastic[$i]['k'] > $stochastic[$i]['d'] && $stochastic[$i - 1]['k'] <= $stochastic[$i - 1]['d'];
    $stoch_k_cross_below_d = $stochastic[$i]['k'] < $stochastic[$i]['d'] && $stochastic[$i - 1]['k'] >= $stochastic[$i - 1]['d'];  

    if ($is_up_trend && $rsi_above_50 && $macd_cross_above_signal && $price_near_lower_band && $stoch_k_cross_above_d) {
        // Sinal de compra (CALL)
        save_opportunity_to_file($symbol, $interval, "CALL", $data[$i][4], $data[$i][6]);
    } elseif ($is_down_trend && $rsi_below_50 && $macd_cross_below_signal && $price_near_upper_band && $stoch_k_cross_below_d) {
        // Sinal de venda (PUT)
        save_opportunity_to_file($symbol, $interval, "PUT", $data[$i][4], $data[$i][6]);
    } else {
        //echo "$symbol, $interval, \"teste\", {$data[$i][4]}<br>";
    }
}

function save_opportunity_to_file($symbol, $interval, $signal_type, $candle_price, $candle_date) {
    $filename = "opportunities.txt";
    $opportunity = "Symbol: {$symbol} | Interval: {$interval} | Signal: {$signal_type} | Time: " . date("Y-m-d H:i:s", $candle_date / 1000) . " | Price: {$candle_price}" . PHP_EOL;
    file_put_contents($filename, $opportunity, FILE_APPEND);
}

?>