<?php
parse_str(implode('&', array_slice($argv, 1)), $_GET);

$symbol = $_GET["s"];
$interval = $_GET["i"];
$candles_count = $_GET["c"];
$leverage = $_GET["l"];
$stopLossPercentage = $_GET["sl"];
$takeProfitPercentage = $_GET["tp"];
$min_time_between_opportunities = $_GET["t"];

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

require_once 'get_crypto_data.php';
require_once 'technical_indicators.php';
require_once 'funcoes.php';

$apiKey = 'YOUR_API_KEY';
$secretKey = 'YOUR_SECRET_KEY';

$outputfile = "node_output_{$symbol}_{$interval}.log";

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

/*
echo "historical_data: " . count($historical_data) . "<br>";
echo "latest_data: " . count($latest_data) . "<br>";
echo "total: " . count($data) . "<br>";

foreach ($data as $row) {
    list($timestamp, $open, $high, $low, $close, $volume) = $row;
    echo timestamp_to_readable_date($timestamp) . ", Open: {$open}, High: {$high}, Low: {$low}, Close: {$close}, Volume: {$volume}<br>";
}
*/

// Calcule os indicadores
$ema_50 = exponential_moving_average($data, 50);
$ema_200 = exponential_moving_average($data, 200);
$rsi = relative_strength_index($data, 14);
$macd = moving_average_convergence_divergence($data, 12, 26, 9);    // (data, short_period, long_period, signal_period)
$bollinger_bands = bollinger_bands($data, 20, 2);                   // (data, period, num_standard_deviations)
$stochastic = stochastic_oscillator($data, 14, 3);                  // (data, k_period, d_period)

create_process_file($symbol, $interval);

$current_time = time();
$next_update_time = $current_time + $interval_mapping[$interval];

$last_buy_opportunity_time = 0;
$last_sell_opportunity_time = 0;

while (true) {
    $current_time = time();
    
    if ($current_time >= $next_update_time) {
        $next_update_time += $interval_mapping[$interval];
        
        // Atualize os dados históricos
        $latest_data = get_latest_data($symbol, $interval, $candles_today);
        $all_data = array_merge($historical_data, $latest_data);
        $data = array_slice($all_data, -$candles_count);

        // Recalcule os indicadores
        $ema_50 = exponential_moving_average($data, 50);
        $ema_200 = exponential_moving_average($data, 200);
        $rsi = relative_strength_index($data, 14);
        $macd = moving_average_convergence_divergence($data, 12, 26, 9);
        $bollinger_bands = bollinger_bands($data, 20, 2);
        $stochastic = stochastic_oscillator($data, 14, 3);
    }

    $realtime_data = get_realtime_data($outputfile);
    if ($realtime_data !== null) {
        // Verifique as oportunidades de compra e venda com base nos indicadores atualizados
        $i = count($data) - 1;

        $is_up_trend = $ema_50[$i] > $ema_200[$i];
        $is_down_trend = $ema_50[$i] < $ema_200[$i];
        $rsi_above_50 = $rsi[$i] > 50;
        $rsi_below_50 = $rsi[$i] < 50;
        $macd_cross_above_signal = $macd['macd'][$i] > $macd['signal'][$i] && $macd['macd'][$i - 1] <= $macd['signal'][$i - 1];
        $macd_cross_below_signal = $macd['macd'][$i] < $macd['signal'][$i] && $macd['macd'][$i - 1] >= $macd['signal'][$i - 1];
        $price_near_lower_band = $realtime_data['c'] <= $bollinger_bands[$i]['lower'];
        $price_near_upper_band = $realtime_data['c'] >= $bollinger_bands[$i]['upper'];
        $stoch_k_cross_above_d = $stochastic[$i]['k'] > $stochastic[$i]['d'] && $stochastic[$i - 1]['k'] <= $stochastic[$i - 1]['d'];
        $stoch_k_cross_below_d = $stochastic[$i]['k'] < $stochastic[$i]['d'] && $stochastic[$i - 1]['k'] >= $stochastic[$i - 1]['d'];

        if ($is_up_trend && $rsi_above_50 && $macd_cross_above_signal && $price_near_lower_band && $stoch_k_cross_above_d && ($current_time - $last_buy_opportunity_time) >= $min_time_between_opportunities) {
            // Sinal de compra (CALL)
            $last_buy_opportunity_time = $current_time;
            save_opportunity_to_file($symbol, $interval, "CALL", $realtime_data['c'], $realtime_data['E']);
            // $result = executeBinanceFuturesOrder($apiKey, $secretKey, $symbol, 'BUY', 'LONG', $leverage, $stopLossPercentage, $takeProfitPercentage);
        } elseif ($is_down_trend && $rsi_below_50 && $macd_cross_below_signal && $price_near_upper_band && $stoch_k_cross_below_d && ($current_time - $last_sell_opportunity_time) >= $min_time_between_opportunities) {
            // Sinal de venda (PUT)
            $last_sell_opportunity_time = $current_time;
            save_opportunity_to_file($symbol, $interval, "PUT", $realtime_data['c'], $realtime_data['E']);
            // $result = executeBinanceFuturesOrder($apiKey, $secretKey, $symbol, 'SELL', 'SHORT', $leverage, $stopLossPercentage, $takeProfitPercentage);
        }
    }

    // Adicione um pequeno atraso para evitar sobrecarregar o servidor
    usleep(1000000); // 1 segundo
}


/*
// Calculando o maior valor mínimo para configurar o 'for'
$ema_50_min_index = 50 - 1; // 49
$ema_200_min_index = 200 - 1; // 199
$rsi_min_index = 14 - 1; // 13
$macd_min_index = max(12, 26, 9) - 1; // 25
$bollinger_bands_min_index = 20 - 1; // 19
$stochastic_min_index = 14 - 1; // 13

$highest_min_index = max($ema_50_min_index, $ema_200_min_index, $rsi_min_index, $macd_min_index, $bollinger_bands_min_index, $stochastic_min_index);

$max_size = min(
    count($ema_50),
    count($ema_200),
    count($rsi),
    count($macd['macd']),
    count($bollinger_bands),
    count($stochastic)
);

$result = '';

for ($i = $max_size - 1; $i >= $highest_min_index; $i--) {
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
        save_opportunity_to_file($symbol, $interval, "CALL", $data[$i][4], $data[$i][0]);
        // $result = executeBinanceFuturesOrder($apiKey, $secretKey, $symbol, 'BUY', 'LONG', $leverage, $stopLossPercentage, $takeProfitPercentage);
    } elseif ($is_down_trend && $rsi_below_50 && $macd_cross_below_signal && $price_near_upper_band && $stoch_k_cross_below_d) {
        // Sinal de venda (PUT)
        save_opportunity_to_file($symbol, $interval, "PUT", $data[$i][4], $data[$i][0]);
        // $result = executeBinanceFuturesOrder($apiKey, $secretKey, $symbol, 'SELL', 'SHORT', $leverage, $stopLossPercentage, $takeProfitPercentage);
    }
}

if($result) { 
    echo "Ordem de mercado executada e protegida com stop-loss e take-profit.<br>";
    echo "Detalhes da ordem:<br>";
    echo "Ordem: " . json_encode($result['order']) . "<br>";
    echo "Stop-Loss: " . json_encode($result['stopLossOrder']) . "<br>";
    echo "Take-Profit: " . json_encode($result['takeProfitOrder']) . "<br>";
}
*/

?>