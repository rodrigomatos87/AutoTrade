<?php
/*
Estrutura array ohlcvs:
    0.  Open time
    1.  Open
    2.  High
    3.  Low
    4.  Close
    5.  Volume
    6.  Close time
    7.  Quote asset volume
    8.  Number of trades
    9.  Taker buy base asset volume
    10. Taker buy quote asset volume
    11. Ignore
*/

// Históricos ohlcvs
function download_and_extract_data($symbol, $interval, $candles_count) {
    // Converte o intervalo para segundos
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

    if (!isset($interval_mapping[$interval])) {
        echo "Intervalo inválido.<br>";
        return;
    }

    $interval_seconds = $interval_mapping[$interval];
    $today = date("Y-m-d");

    // Calcule a data de início com base na contagem de candles e no intervalo
    $start_date = date("Y-m-d", strtotime($today . " -" . ceil($candles_count * $interval_seconds / (24 * 60 * 60)) . " days"));

    $current_date = $start_date;

    $data_folder = 'data_historical';
    if (!file_exists($data_folder)) { mkdir($data_folder, 0777, true); }

    // Verifique se a pasta 'data' existe, caso contrário, crie-a
    if (!file_exists($data_folder)) {
        mkdir($data_folder, 0777, true);
    }
    
    $all_data = [];
    while (strtotime($current_date) < strtotime($today)) {
        $zip_filename = "{$symbol}-{$interval}-{$current_date}.zip";
        $csv_filename = "{$symbol}-{$interval}-{$current_date}.csv";

        $url = "https://data.binance.vision/data/spot/daily/klines/{$symbol}/{$interval}/{$zip_filename}";

        if (!file_exists("{$data_folder}/{$zip_filename}")) {
            // Baixe o arquivo .zip se ele não existir
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);

            file_put_contents("{$data_folder}/{$zip_filename}", $data);
            //echo "Arquivo {$zip_filename} baixado com sucesso.<br>";
        } else {
            //echo "Arquivo {$zip_filename} já existe. Ignorando o download.<br>";
        }

        $zip = new ZipArchive();
        if ($zip->open("{$data_folder}/{$zip_filename}") === true) {
            $zip->extractTo($data_folder);
            $zip->close();
            //echo "Arquivo {$zip_filename} extraído com sucesso.<br>";

            if (($handle = fopen("{$data_folder}/{$csv_filename}", "r")) !== false) {
                while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                    $all_data[] = $row;
                }
                fclose($handle);
                // Remova o arquivo CSV após ler os dados
                unlink("{$data_folder}/{$csv_filename}");
            }
        } else {
            echo "Não foi possível extrair o arquivo {$zip_filename}.<br>";
            unlink("{$data_folder}/{$zip_filename}");
        }

        $current_date = date("Y-m-d", strtotime($current_date . " +1 day"));
    }

    // Retorne apenas os últimos candles_count dados
    return array_slice($all_data, -$candles_count);
}

function get_latest_data($symbol, $interval, $candles_count) {
    $api_url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval={$interval}&limit={$candles_count}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    // Converte o formato de resposta da API para o formato CSV usado nos dados históricos
    $latest_data = [];
    foreach ($response_data as $entry) {
        $latest_data[] = [
            $entry[0], $entry[1], $entry[2], $entry[3], $entry[4], $entry[5]
        ];
    }

    return $latest_data;
}

?>