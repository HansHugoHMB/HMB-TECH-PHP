<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Inclure la bibliothèque PHPMailer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $sender_email = $_POST['sender_email'];
    $password = $_POST['password'];  // Cela peut être utilisé pour l'authentification avec SMTP (Gmail, par exemple)
    $recipient_email = $_POST['recipient_email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Créer une instance de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP (par exemple, Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Serveur SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = $sender_email;  // Utiliser l'email de l'expéditeur
        $mail->Password = $password;      // Utiliser le mot de passe de l'expéditeur
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Définir l'adresse de l'expéditeur et du destinataire
        $mail->setFrom($sender_email, 'Ton Nom');
        $mail->addAddress($recipient_email);  // Destinataire
        $mail->addReplyTo($sender_email, 'Ton Nom');

        // Contenu de l'email
        $mail->isHTML(true);  // Permet l'envoi en HTML
        $mail->Subject = $subject;
        $mail->Body    = nl2br($message); // Remplacer les sauts de ligne par <br>

        // Envoi de l'email
        if ($mail->send()) {
            echo 'L\'email a été envoyé avec succès!';
        } else {
            echo 'Échec de l\'envoi de l\'email.';
        }

    } catch (Exception $e) {
        echo "Erreur : {$mail->ErrorInfo}";
    }
}
?>