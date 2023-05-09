<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
  // Redireciona para a página de login
  header("Location: login.php");
  exit;
}

$sql = "SELECT apiKey, secretKey FROM users WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$apiKey = $row['apiKey'];
$secretKey = $row['secretKey'];

require '../vendor/autoload.php';

use Binance\API;
use Binance\API\Futures;

// Buscar saldo spot
$binance = new API($apiKey, $secretKey);
$spot_balance = $binance->account();

// Buscar saldo futuros
$binanceFutures = new Futures($apiKey, $secretKey);
$binanceFutures->useServerTime();
$futures_balance = $binanceFutures->account();



$url = "https://api.alternative.me/fng/?limit=1";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($curl);
$fear_and_greed_index = null;

if ($response === false) {
    echo "Erro ao acessar a API: " . curl_error($curl);
} else {
    $data = json_decode($response, true);
    if (isset($data['data'][0]['value'])) {
        $fear_and_greed_index = $data['data'][0]['value'];
    } else {
        echo "Não foi possível extrair o índice de Medo e Ganância.";
    }
}

curl_close($curl);

//$fear_and_greed_index = 10;
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Página Inicial</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

  <style>
    body {
      background-color: #f8f9fa;
    }

    .container {
      max-width: 960px;
      margin: 20px auto;
      padding: 20px;
      background-color: #ffffff;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
      border-radius: 5px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .welcome {
      font-size: 24px;
      font-weight: bold;
    }

    .logout {
      font-size: 16px;
      color: #007bff;
      cursor: pointer;
    }

    .logout:hover {
      text-decoration: underline;
    }

    .chart-container {
      position: relative;
      height: 60vh;
      width: 80vw;
    }

    .legend {
      margin-top: 20px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="header">
    <div class="welcome">
      Bem-vindo, <?php echo $_SESSION['user_name']; ?>!
    </div>
    <div class="logout" onclick="location.href='logout.php';">
      Sair
    </div>
  </div>

  <div>
  <h3>Saldo Spot</h3>
  <pre><?php print_r($spot_balance); ?></pre>
  </div>

  <div>
    <h3>Saldo Futuros</h3>
    <pre><?php print_r($futures_balance); ?></pre>
  </div>

  <br><br><br>

  <div id="gauge-chart" style="width:400px; height:160px"></div>
<div id="info-chart" style="width:400px; height:140px; font-family: Arial; font-size: 14px"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/justgage/1.3.2/justgage.min.js"></script>
<script>
  // Cria o gráfico usando a biblioteca JustGage
  var g = new JustGage({
    id: 'gauge-chart',
    value: <?=$fear_and_greed_index;?>, // Usa o valor atual do índice
    min: 0,
    max: 100,
    title: 'Índice de Medo e Ganância',
    label: 'Pontos',
    levelColors: ['#d9534f', '#f0ad4e', '#ffff00', '#5cb85c', '#337ab7'], // Usa as cores do site da CNN
    levelColorsGradient: false,
    pointer: true, // Usa um ponteiro para indicar o valor
    pointerOptions: {
      toplength: -15,
      bottomlength: 10,
      bottomwidth: 12,
      color: '#8e8e93',
      stroke: '#ffffff',
      stroke_width: 3,
      stroke_linecap: 'round'
    }
  });

  // Cria a div com as informações do gráfico
  var info = document.getElementById('info-chart');
  info.innerHTML = `
    <p>Fechamento anterior <span style="float:right">52</span></p>
    <p>1 semana atrás <span style="float:right">52</span></p>
    <p>1 mês atrás <span style="float:right">55</span></p>
    <p>1 ano atrás <span style="float:right">30</span></p>
  `;
</script>

</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>