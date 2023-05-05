<?php

function get_realtime_data($outputFile) {
    if (file_exists($outputFile)) {
        $current_time = time();
        $file_modified_time = filemtime($outputFile);

        if ($current_time === $file_modified_time) {
            $json_data = file_get_contents($outputFile);
            $data = json_decode($json_data, true);
            return $data;
        }
    }
    return null;
}

function create_process_file($symbol, $outputfile) {
    // Verifique se o diretório 'processos' existe e, caso contrário, crie-o
    if (!file_exists('processos')) {
        mkdir('processos', 0777, true);
    }

    $process_data = [
        'symbol' => $symbol,
        'outputfile' => $outputfile
    ];
    $process_file = fopen("processos/{$symbol}.json", 'w');
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