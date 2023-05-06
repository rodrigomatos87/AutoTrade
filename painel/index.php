<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
  // Redireciona para a página de login
  header("Location: login.php");
  exit;
}

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

  <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/justgage/1.3.2/justgage.min.js"></script>

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

  <br><br><br>

  <div class="row">
  <div class="col-md-6">
    <div id="fearAndGreedChart" style="width: 400px; height: 200px;"></div>
    <p class="text-center">Índice de Medo e Ganância</p>
  </div>
  <div class="col-md-6">
    <div class="legend">
      <p><span style="color: #d9534f">■</span> 0-25: Medo Extremo</p>
      <p><span style="color: #f0ad4e">■</span> 26-50: Medo</p>
      <p><span style="color: #5cb85c">■</span> 51-75: Ganância</p>
      <p><span style="color: #337ab7">■</span> 76-100: Ganância Extrema</p>
    </div>
  </div>
</div>


</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var fearAndGreedIndex = <?php echo $fear_and_greed_index ? $fear_and_greed_index : 0; ?>;

  var g = new JustGage({
    id: "fearAndGreedChart",
    value: fearAndGreedIndex,
    min: 0,
    max: 100,
    title: "Índice de Medo e Ganância",
    label: "",
    customSectors: [
    {
        color: "#d9534f",
        lo: 75,
        hi: 100
    },
    {
        color: "#f0ad4e",
        lo: 50,
        hi: 75
    },
    {
        color: "#5cb85c",
        lo: 25,
        hi: 50
    },
    {
        color: "#337ab7",
        lo: 0,
        hi: 25
    }
    ],
    counter: true,
    relativeGaugeSize: true
  });
});

</script>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>