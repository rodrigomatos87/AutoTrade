<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Pega dados do formulário
  $username = $_POST['username'];
  $password = $_POST['password'];
  $name = $_POST['name'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $apiKey = $_POST['apiKey'];
  $secretKey = $_POST['secretKey'];

  // Sanitiza os dados de entrada
  $username = mysqli_real_escape_string($conn, $username);
  $password = mysqli_real_escape_string($conn, $password);
  $name = mysqli_real_escape_string($conn, $name);
  $phone = mysqli_real_escape_string($conn, $phone);
  $email = mysqli_real_escape_string($conn, $email);
  $apiKey = mysqli_real_escape_string($conn, $apiKey);
  $secretKey = mysqli_real_escape_string($conn, $secretKey);

  // Valida os dados de entrada
  if (empty($username) || empty($password) || empty($name)) {
    die("Usuário, senha e nome são campos obrigatórios.");
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email inválido.");
  }

  // Criptografa a senha
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Prepara e executa a query SQL usando prepared statement
  $stmt = $conn->prepare("INSERT INTO users (user, senha, nome, telefone, email, apiKey, secretKey) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssss", $username, $hashed_password, $name, $phone, $email, $apiKey, $secretKey);

  if ($stmt->execute() === TRUE) {
    echo "Usuário criado com sucesso!";
  } else {
    echo "Erro: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Usuário</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f8f9fa;
    }

    .container {
      max-width: 500px;
      margin: 50px auto;
      padding: 20px;
      background-color: #ffffff;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
      border-radius: 5px;
    }

    .user-form {
      width: 100%;
    }

    .user-form h2 {
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-control {
      display: block;
      width: 100%;
      padding: 6px 12px;
      font-size: 14px;
      line-height: 1.42857143;
      color: #555;
      background-color: #fff;
      background-image: none;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .btn {
      display: inline-block;
      padding: 6px 12px;
      margin-bottom: 0;
      font-size: 14px;
      font-weight: 400;
      line-height: 1.42857143;
      text-align: center;
      white-space: nowrap;
      vertical-align: middle;
      cursor: pointer;
      border: 1px solid transparent;
      border-radius: 4px;
    }

    .btn-primary {
      color: #fff;
      background-color: #007bff;
      border-color: #007bff;
    }
  </style>
</head>
<body>

<div class="container">
  <form action="" method="post" class="user-form">
    <h2>Cadastro de Usuário</h2>
    <div class="form-group">
      <label for="username">Usuário:</label>
      <input type="text" id="username" name="username" class="form-control" required>
    </div>
    <div class="form-group">
      <label for="password">Senha:</label>
      <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
      <label for="name">Nome:</label>
      <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <div class="form-group">
      <label for="phone">Telefone:</label>
      <input type="text" id="phone" name="phone" class="form-control">
    </div>
    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" class="form-control">
    </div>
    <div class="form-group">
      <label for="apiKey">API Key:</label>
      <input type="text" id="apiKey" name="apiKey" class="form-control">
    </div>
    <div class="form-group">
      <label for="secretKey">Secret Key:</label>
      <input type="text" id="secretKey" name="secretKey" class="form-control">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </div>

  </form>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>





