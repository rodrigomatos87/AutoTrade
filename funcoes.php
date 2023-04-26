<?php

function timestamp_to_readable_date($timestamp) {
    // Se o timestamp estiver em milissegundos, converta-o para segundos
    if (mb_strlen($timestamp) == 13) {
        $timestamp = $timestamp / 1000;
    }

    return date('Y-m-d H:i:s', $timestamp);
}

function save_opportunity_to_file($symbol, $interval, $signal_type, $candle_price, $candle_date) {
    $filename = "opportunities.txt";
    $opportunity = "Symbol: {$symbol} | Interval: {$interval} | Signal: {$signal_type} | Time: " . timestamp_to_readable_date($candle_date) . " | Price: {$candle_price}" . PHP_EOL;
    file_put_contents($filename, $opportunity, FILE_APPEND);
}
?>