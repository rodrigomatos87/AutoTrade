<?php

function get_realtime_data($outputFile) {
    $dataRealtimeDir = "data_realtime";
    $outputFilePath = $dataRealtimeDir . DIRECTORY_SEPARATOR . $outputFile;

    if (file_exists($outputFilePath)) {
        $current_time = time();
        $file_modified_time = filemtime($outputFilePath);

        if ($current_time === $file_modified_time) {
            $json_data = file_get_contents($outputFilePath);
            $data = json_decode($json_data, true);
            return $data;
        }
    }
    return null;
}

function create_process_file($symbol, $interval) {
    if (!file_exists('process')) { mkdir('process', 0777, true); }
    if (!file_exists('data_realtime')) { mkdir('data_realtime', 0777, true); }
    $outputfile = "node_output_{$symbol}_{$interval}.log";

    $process_data = [
        'symbol' => $symbol,
        'outputfile' => $outputfile
    ];
    $process_file = fopen("process/{$symbol}_{$interval}.json", 'w');
    fwrite($process_file, json_encode($process_data));
    fclose($process_file);
}

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