<?php

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

?>