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
    $signal_line = [];
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
            $signal_line[$i] = $ema_signal[$i];
            $histogram[$i] = $macd[$i] - $signal_line[$i];
        }
    }

    return [
        'macd' => $macd,
        'signal_line' => $signal_line,
        'histogram' => $histogram,
    ];
}


/*
// Média Móvel Exponencial (EMA)
function ema($data, $period) {
    $prices = array_column($data['prices'], 1);
    $ema = array();
    $k = 2 / ($period + 1);

    // Primeira média móvel exponencial
    $ema[] = array_sum(array_slice($prices, 0, $period)) / $period;

    // Cálculo subsequente das médias móveis exponenciais
    for ($i = $period; $i < count($prices); $i++) {
        $ema[] = $prices[$i] * $k + $ema[$i - $period] * (1 - $k);
    }

    return $ema;
}

// Índice de Força Relativa (IFR ou RSI)
function rsi($data, $period) {
    $prices = array_column($data['prices'], 1);
    $gains = array();
    $losses = array();

    // Cálculo dos ganhos e perdas
    for ($i = 1; $i < count($prices); $i++) {
        $change = $prices[$i] - $prices[$i - 1];

        if ($change >= 0) {
            $gains[] = $change;
            $losses[] = 0;
        } else {
            $gains[] = 0;
            $losses[] = abs($change);
        }
    }

    // Médias móveis exponenciais dos ganhos e perdas
    $avg_gains = ema(['prices' => array_map(null, range(1, count($gains)), $gains)], $period);
    $avg_losses = ema(['prices' => array_map(null, range(1, count($losses)), $losses)], $period);

    // Cálculo do RSI
    $rsi = array();
    for ($i = 0; $i < count($avg_gains); $i++) {
        $rs = ($avg_losses[$i] == 0) ? 100 : $avg_gains[$i] / $avg_losses[$i];
        $rsi[] = 100 - (100 / (1 + $rs));
    }

    return $rsi;
}

// Bandas de Bollinger (BB)
function bollingerBands($data, $period, $std_dev) {
    $prices = array_column($data['prices'], 1);
    $sma = array();
    $bb = array('upper' => array(), 'middle' => array(), 'lower' => array());

    // Cálculo da Média Móvel Simples (SMA) e das Bandas de Bollinger
    for ($i = $period - 1; $i < count($prices); $i++) {
        $sma[] = array_sum(array_slice($prices, $i - $period + 1, $period)) / $period;
        $std = standard_deviation(array_slice($prices, $i - $period + 1, $period));
        $bb['upper'][] = $sma[$i - $period + 1] + $std_dev * $std;
        $bb['middle'][] = $sma[$i - $period + 1];
        $bb['lower'][] = $sma[$i - $period + 1] - $std_dev * $std;
    }

    return $bb;
}

// Divergência de Convergência Média Móvel (MACD)
function macd($data, $short_period, $long_period, $signal_period) {
    $prices = array_column($data['prices'], 1);
    $short_ema = ema($data, $short_period);
    $long_ema = ema($data, $long_period);

    // Cálculo do MACD
    $macd_line = array();
    for ($i = max($short_period, $long_period) - 1; $i < count($prices); $i++) {
        $macd_line[] = $short_ema[$i - $short_period + 1] - $long_ema[$i - $long_period + 1];
    }

    // Cálculo da linha de sinal
    $signal_line = ema(['prices' => array_map(null, range(1, count($macd_line)), $macd_line)], $signal_period);

    return ['macd' => $macd_line, 'signal' => $signal_line];
}

// Função auxiliar para calcular o desvio padrão
function standard_deviation($values) {
    $mean = array_sum($values) / count($values);
    $squared_diffs = array_map(function ($value) use ($mean) {
        return pow($value - $mean, 2);
    }, $values);

    return sqrt(array_sum($squared_diffs) / count($values));
}
*/
?>