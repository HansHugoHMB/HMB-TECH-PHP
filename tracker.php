<?php
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Lire les données envoyées par JavaScript
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) exit('Aucune donnée reçue.');

$message = "
<b>Nouvelle visite détectée :</b><br><br>
<strong>IP :</strong> {$data['ip']}<br>
<strong>Pays :</strong> {$data['country']}<br>
<strong>Région :</strong> {$data['region']}<br>
<strong>Ville :</strong> {$data['city']}<br>
<strong>Fournisseur :</strong> {$data['isp']}<br>
<strong>Latitude :</strong> {$data['lat']}<br>
<strong>Longitude :</strong> {$data['lon']}<br>
<strong>Navigateur :</strong> {$data['ua']}<br>
<strong>Date :</strong> {$data['time']}
";

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'hmb05092006@gmail.com';
    $mail->Password = 'z u o p m w n k i e e m d g x y'; // mot de passe d'application Gmail
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('hmb05092006@gmail.com', 'Tracker Web');
    $mail->addAddress('mbayahans@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Nouvelle visite sur ton site';
    $mail->Body    = $message;

    $mail->send();
    echo 'OK';
} catch (Exception $e) {
    echo "Erreur envoi : {$mail->ErrorInfo}";
}
?>