<?php

function executeBinanceFuturesOrder($apiKey, $secretKey, $symbol, $side, $positionSide, $leverage, $stopLossPercentage, $takeProfitPercentage)
{
    function sendRequest($url, $params, $apiKey, $secretKey, $method = 'GET', $signed = true)
    {
        $query = http_build_query($params, '', '&');
    
        if ($signed) {
            $timestamp = time() * 1000;
            $query .= "&timestamp=$timestamp";
            $signature = hash_hmac('sha256', $query, $secretKey);
            $query .= "&signature=$signature";
        }
    
        $ch = curl_init();
    
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        } else {
            $url .= '?' . $query;
        }
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-MBX-APIKEY: $apiKey"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return json_decode($response, true);
    }

    // Altere a alavancagem
    $url = 'https://fapi.binance.com/fapi/v1/leverage';
    $params = ['symbol' => $symbol, 'leverage' => $leverage];
    sendRequest($url, $params, $apiKey, $secretKey, 'POST');

    // Obtenha o preço de mercado atual
    $url = 'https://fapi.binance.com/fapi/v1/premiumIndex';
    $params = ['symbol' => $symbol];
    $priceData = sendRequest($url, $params, $apiKey, $secretKey);
    $price = $priceData['markPrice'];

    // Calcule o stop-loss e take-profit
    $stopLossPrice = $price * (1 - $stopLossPercentage);
    $takeProfitPrice = $price * (1 + $takeProfitPercentage);

    // Abra a posição com ordem de mercado
    $url = 'https://fapi.binance.com/fapi/v1/order';
    $params = [
        'symbol' => $symbol,
        'side' => $side,
        'type' => 'MARKET',
        'positionSide' => $positionSide,
        'marginType' => 'ISOLATED'
    ];
    $order = sendRequest($url, $params, $apiKey, $secretKey, 'POST');

    // Configure o stop-loss e take-profit
    $params = [
        'symbol' => $symbol,
        'side' => $side === 'BUY' ? 'SELL' : 'BUY',
        'type' => 'STOP_MARKET',
        'positionSide' => $positionSide,
        'stopPrice' => $stopLossPrice,
        'reduceOnly' => 'true',
        'marginType' => 'ISOLATED'
    ];
    $stopLossOrder = sendRequest($url, $params, $apiKey, $secretKey, 'POST');

    $params = [
        'symbol' => $symbol,
        'side' => $side === 'BUY' ? 'SELL' : 'BUY',
        'type' => 'TAKE_PROFIT_MARKET',
        'positionSide' => $positionSide,
        'stopPrice' => $takeProfitPrice,
        'reduceOnly' => 'true',
        'marginType' => 'ISOLATED'
    ];
    $takeProfitOrder = sendRequest($url, $params, $apiKey, $secretKey, 'POST');

    return [
        'order' => $order,
        'stopLossOrder' => $stopLossOrder,
        'takeProfitOrder' => $takeProfitOrder
    ];
}

/*
A variável $side e $positionSide são usadas para especificar o tipo de ordem e a direção da posição 
na Binance Futures. Aqui estão as opções disponíveis para cada variável:

$side:          'BUY': Comprar (abrir uma posição longa ou fechar uma posição curta)
                'SELL': Vender (abrir uma posição curta ou fechar uma posição longa)

$positionSide:  'LONG': Posição longa (comprando um ativo com a expectativa de que o valor aumente)
                'SHORT': Posição curta (vendendo um ativo emprestado com a expectativa de que o valor diminua)

A combinação dessas variáveis permite que você abra e feche posições longas e curtas na Binance Futures. 

Por exemplo:    Para abrir uma posição longa: $side = 'BUY' e $positionSide = 'LONG'
                Para fechar uma posição longa: $side = 'SELL' e $positionSide = 'LONG'
                Para abrir uma posição curta: $side = 'SELL' e $positionSide = 'SHORT'
                Para fechar uma posição curta: $side = 'BUY' e $positionSide = 'SHORT'

Modo de margem: ISOLADA
*/ 

?>