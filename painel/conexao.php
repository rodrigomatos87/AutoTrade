<?php
$servername = "localhost";
$username = "root";
$password = "#D0EbUaS702_i8Iniu@yUlgD#";
$dbname = "AutoTrade";

// Cria conexão
$conn = new mysqli($servername, $username, $password, $dbname);
// Checa conexão
if ($conn->connect_error) {
  die("Conexão falhou: " . $conn->connect_error);
}

return $conn;
?>
