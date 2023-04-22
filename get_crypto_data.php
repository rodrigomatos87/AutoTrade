<?php

function save_data_to_file($data, $filename) {
    file_put_contents($filename, json_encode($data));
}

function load_data_from_file($filename) {
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        return json_decode($content, true);
    }

    return null;
}

function get_crypto_historical_data($coin_id, $interval, $count) {
    $interval_minutes = intval($interval);
    $total_minutes_needed = $count * $interval_minutes;
    $days_needed = ceil($total_minutes_needed / (24 * 60));
    $api_intervals = array();

    echo " dias:  " . $days_needed . "<br>";

    for ($i = $days_needed; $i >= 1; $i--) {
        $start_datetime = new DateTime('now', new DateTimeZone('UTC'));
        $start_datetime->modify('-' . $i . ' days');
        $start_timestamp = $start_datetime->getTimestamp();

        $end_datetime = new DateTime('now', new DateTimeZone('UTC'));
        $end_datetime->modify('-' . ($i - 1) . ' days');
        $end_timestamp = $end_datetime->getTimestamp();

        $api_intervals[] = array(
            'from' => $start_timestamp,
            'to' => $end_timestamp
        );
    }

    echo "<pre>"; print_r($api_intervals); echo "</data>";

    $historical_data = array('prices' => array(), 'market_caps' => array(), 'total_volumes' => array());

    foreach ($api_intervals as $interval) {
        $url = "https://api.coingecko.com/api/v3/coins/{$coin_id}/market_chart/range?vs_currency=usd&from={$interval['from']}&to={$interval['to']}";
        $response = file_get_contents($url);

        if ($response === false) {
            echo "entrou<br>";
            sleep(1); // Adicionar atraso de 1 segundo entre as solicitações
            $response = file_get_contents($url);
        }

        $data = json_decode($response, true);

        echo "<pre>"; print_r($data); echo "</data>";

        if (isset($data['prices'], $data['market_caps'], $data['total_volumes'])) {
            $historical_data['prices'] = array_merge($historical_data['prices'], $data['prices']);
            $historical_data['market_caps'] = array_merge($historical_data['market_caps'], $data['market_caps']);
            $historical_data['total_volumes'] = array_merge($historical_data['total_volumes'], $data['total_volumes']);
        }
    }

    $filtered_data = array('prices' => array(), 'market_caps' => array(), 'total_volumes' => array());
    $data_count = 0;
    $filter_index = floor($interval_minutes / 5);
    
    if ($filter_index < 1) {
        $filter_index = 1;
    }

    for ($i = 0; $i < count($historical_data['prices']); $i++) {
        if ($i % $filter_index == 0) {
            $filtered_data['prices'][] = $historical_data['prices'][$i];
            $filtered_data['market_caps'][] = $historical_data['market_caps'][$i];
            $filtered_data['total_volumes'][] = $historical_data['total_volumes'][$i];
            $data_count++;

            if ($data_count >= $count) {
                break;
            }
        }
    }

    return $filtered_data;
}





/*
function get_crypto_historical_data($coin_id, $interval, $count) {
    // URL base da CoinGecko API gratuita
    $base_url = "https://api.coingecko.com/api/v3/";

    // Configuração das opções do cURL
    $curl_options = array(
        CURLOPT_RETURNTRANSFER => 1,
    );

    // Inicia o cURL e configura as opções
    $curl = curl_init();
    curl_setopt_array($curl, $curl_options);

    $historical_data = array('prices' => array(), 'market_caps' => array(), 'total_volumes' => array());

    while ($count > 0) {
        $days = min($count, 90); // Limita a quantidade de registros por solicitação a 90
        $count -= $days;

        // Endpoint de dados históricos da criptomoeda
        $historical_data_endpoint = "coins/{$coin_id}/market_chart?vs_currency=usd&interval={$interval}&days={$days}";

        // Coleta os dados históricos
        curl_setopt($curl, CURLOPT_URL, $base_url . $historical_data_endpoint);
        $response = curl_exec($curl);

        if ($response === false) {
            die('Erro ao coletar dados históricos: ' . curl_error($curl));
        }

        // Decodifica os dados e combina os resultados
        $partial_data = json_decode($response, true);
        $historical_data['prices'] = array_merge($historical_data['prices'], $partial_data['prices']);
        $historical_data['market_caps'] = array_merge($historical_data['market_caps'], $partial_data['market_caps']);
        $historical_data['total_volumes'] = array_merge($historical_data['total_volumes'], $partial_data['total_volumes']);

        if ($count > 0) {
            sleep(1); // Adiciona uma pausa entre as solicitações para evitar atingir os limites da API
        }
    }

    // Fecha o cURL
    curl_close($curl);

    // Retorna o resultado
    return $historical_data;
}
*/
?>