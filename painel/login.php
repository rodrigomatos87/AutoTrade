<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Pega dados do formulário
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Sanitiza os dados de entrada
  $username = mysqli_real_escape_string($conn, $username);
  $password = mysqli_real_escape_string($conn, $password);

  // Valida os dados de entrada
  if (empty($username) || empty($password)) {
    die("Usuário e senha são campos obrigatórios.");
  }

  // Prepara e executa a query SQL usando prepared statement
  $stmt = $conn->prepare("SELECT id, user, senha, nome FROM users WHERE user = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $user, $hashed_password, $name);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
      // Senha correta, cria a sessão
      $_SESSION['user_id'] = $id;
      $_SESSION['user_name'] = $name;

      // Redireciona para a página index.php
      header("Location: index.php");
      exit;
    } else {
      // Senha incorreta
      echo "Senha incorreta.";
    }
  } else {
    // Usuário não encontrado
    echo "Usuário não encontrado.";
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
  <title>Login</title>
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

    .login-form {
      width: 100%;
    }

    .login-form h2 {
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
  <form action="" method="post" class="login-form">
    <h2>Login</h2>
    <div class="form-group">
      <label for="username">Usuário:</label>
      <input type="text" id="username" name="username" class="form-control" required>
    </div>
    <div class="form-group">
      <label for="password">Senha:</label>
      <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
      <button type="submit" class="btn btn-primary">Entrar</button>
    </div>
  </form>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>