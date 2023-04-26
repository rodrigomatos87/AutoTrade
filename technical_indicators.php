<?php

// Calcula a Média Móvel Simples (SMA) dos dados fornecidos
function simple_moving_average($data, $period) {
    $sma = [];
    for ($i = 0; $i < count($data) - $period + 1; $i++) {
        $sum = 0;
        for ($j = $i; $j < $i + $period; $j++) {
            $sum += floatval($data[$j][4]); // Use o índice 4 para o valor de fechamento
        }
        $sma[] = $sum / $period;
    }
    return $sma;
}

// Calcula a Média Móvel Exponencial (EMA)
function exponential_moving_average($data, $period) {
    $ema = [];
    $k = 2 / ($period + 1);
    $ema[] = simple_moving_average(array_slice($data, 0, $period), $period)[0];
    for ($i = $period; $i < count($data); $i++) {
        $ema[] = $data[$i][4] * $k + $ema[$i - $period] * (1 - $k);
    }
    return $ema;
}

// Calcula as Bandas de Bollinger, que são baseadas na SMA e no desvio padrão dos dados
function bollinger_bands($data, $period = 20, $multiplier = 2) {
    $sma = simple_moving_average($data, $period);
    $bbands = [];
    for ($i = 0; $i < count($data) - $period + 1; $i++) {
        $sum_squared_diff = 0;
        for ($j = $i; $j < $i + $period; $j++) {
            $sum_squared_diff += pow($data[$j][4] - $sma[$i], 2);
        }
        $std_dev = sqrt($sum_squared_diff / $period);
        $bbands[] = [
            'lower' => $sma[$i] - $multiplier * $std_dev,
            'middle' => $sma[$i],
            'upper' => $sma[$i] + $multiplier * $std_dev,
        ];
    }
    return $bbands;
}

// Calcula o Índice de Força Relativa (RSI), que é um indicador de momentum que mede a velocidade e a magnitude das mudanças de preço de um ativo. O RSI varia de 0 a 100 e é usado para identificar condições de sobrecompra ou sobrevenda no mercado.
function relative_strength_index($data, $period) {
    $gains = [];
    $losses = [];
    $rsi = [];

    for ($i = 1; $i < count($data); $i++) {
        $change = $data[$i][4] - $data[$i - 1][4];

        if ($change >= 0) {
            $gains[] = $change;
            $losses[] = 0;
        } else {
            $gains[] = 0;
            $losses[] = abs($change);
        }
    }

    $avg_gain = array_sum(array_slice($gains, 0, $period)) / $period;
    $avg_loss = array_sum(array_slice($losses, 0, $period)) / $period;
    $rs = $avg_gain / $avg_loss;
    $rsi[] = 100 - (100 / (1 + $rs));

    for ($i = $period; $i < count($gains); $i++) {
        $avg_gain = (($avg_gain * ($period - 1)) + $gains[$i]) / $period;
        $avg_loss = (($avg_loss * ($period - 1)) + $losses[$i]) / $period;
        $rs = $avg_gain / $avg_loss;
        $rsi[] = 100 - (100 / (1 + $rs));
    }

    return $rsi;
}

// Calcula a Convergência e Divergência de Médias Móveis (MACD), que é a diferença entre a EMA de curto prazo e a EMA de longo prazo
function moving_average_convergence_divergence($data, $short_period = 12, $long_period = 26, $signal_period = 9) {
    $macd = [];
    $signal = [];
    $histogram = [];

    $ema_short = exponential_moving_average($data, $short_period);
    $ema_long = exponential_moving_average($data, $long_period);

    for ($i = 0; $i < count($data); $i++) {
        // Verifique se o índice existe antes de acessar
        if (isset($ema_short[$i]) && isset($ema_long[$i])) {
            $macd[$i] = $ema_short[$i] - $ema_long[$i];
        }
    }

    $ema_signal = exponential_moving_average($macd, $signal_period);

    for ($i = 0; $i < count($data); $i++) {
        // Verifique se o índice existe antes de acessar
        if (isset($macd[$i]) && isset($ema_signal[$i])) {
            $signal[$i] = $ema_signal[$i];
            $histogram[$i] = $macd[$i] - $signal[$i];
        }
    }

    return [
        'macd' => $macd,
        'signal' => $signal,
        'histogram' => $histogram,
    ];
}

// Calculo oscilador estocástico. Indicador de momentum usado na análise técnica para determinar condições de sobrecompra e sobrevenda de um ativo
function stochastic_oscillator($data, $k_period = 14, $d_period = 3) {
    $output = [];
    $high_prices = array_column($data, 2); // High prices
    $low_prices = array_column($data, 3); // Low prices
    $close_prices = array_column($data, 4); // Close prices

    for ($i = $k_period - 1; $i < count($data); $i++) {
        $highest_high = max(array_slice($high_prices, $i - $k_period + 1, $k_period));
        $lowest_low = min(array_slice($low_prices, $i - $k_period + 1, $k_period));
        $k_value = 100 * ($close_prices[$i] - $lowest_low) / ($highest_high - $lowest_low);

        $output[] = [
            'k' => $k_value,
            'd' => 0
        ];
    }

    for ($i = $d_period - 1; $i < count($output); $i++) {
        $sum_k_values = 0;
        for ($j = $i - $d_period + 1; $j <= $i; $j++) {
            $sum_k_values += $output[$j]['k'];
        }
        $output[$i]['d'] = $sum_k_values / $d_period;
    }

    return $output;
}

?>