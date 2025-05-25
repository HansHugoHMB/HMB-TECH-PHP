<?php
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if ($email) {
        // Enregistrement dans le CSV
        $file = fopen("subscribers.csv", "a");
        fputcsv($file, [$email]);
        fclose($file);

        // Envoi du mail de confirmation
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'hmb05092006@gmail.com';
            $mail->Password = 'z u o p m w n k i e e m d g x y'; // mot de passe d’application
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('hmb05092006@gmail.com', 'Hans Mbaya Newsletter');
            $mail->addAddress($email);
            $mail->Subject = "Confirmation d'abonnement";
            $mail->Body = "Merci pour ton inscription à la newsletter de Hans Mbaya !";

            $mail->send();
            echo "Merci ! Tu recevras bientôt nos nouvelles.";
        } catch (Exception $e) {
            echo "Erreur d'envoi : {$mail->ErrorInfo}";
        }
    } else {
        echo "Adresse email invalide.";
    }
}
?>