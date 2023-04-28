<?php

$url = "https://api.alternative.me/fng/?limit=1";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($curl);

if ($response === false) {
    echo "Erro ao acessar a API: " . curl_error($curl);
} else {
    $data = json_decode($response, true);
    if (isset($data['data'][0]['value'])) {
        $fear_and_greed_index = $data['data'][0]['value'];
        echo "Índice de Medo e Ganância: " . $fear_and_greed_index;
    } else {
        echo "Não foi possível extrair o índice de Medo e Ganância.";
    }
}

curl_close($curl);
?>
