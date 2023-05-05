<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

$symbol = "BTCUSDT";
$interval = "1m";
$candles_count = 30000;

$leverage = 20;                              // Alavancagem
$stopLossPercentage = 0.005;                 // Aceitamos 0,5% de prejuízo, para deixar em 1% utilize '0.01'
$takeProfitPercentage = 0.2;                 // Objetivo de 20% de lucro
$min_time_between_opportunities = 1800;      // Não pegar mais de uma oportunidade no intervalo de (Em segundos)

$args = array();
$args[] = 's=' . $symbol;
$args[] = 'i=' . $interval;
$args[] = 'c=' . $candles_count;
$args[] = 'l=' . $leverage;
$args[] = 'sl=' . $stopLossPercentage;
$args[] = 'tp=' . $takeProfitPercentage;
$args[] = 't=' . $min_time_between_opportunities;

$command = 'php -f start.php ' . implode(' ', $args);
exec(sprintf('nohup %s > /dev/null 2>&1 &', $command));
echo sprintf('nohup %s > /dev/null 2>&1 &', $command);

?>