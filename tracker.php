<?php
// Désactive l'affichage des erreurs
error_reporting(0);
ini_set('display_errors', 0);

// Démarre la mise en mémoire tampon
ob_start();

// Headers CORS et content type en premier
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'Mobile_Detect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

[Le reste de votre code actuel de la classe HMBSecurityTracker...]

// Ajoutez cette méthode qui manque pour la notification bot
private function sendBotNotification($ip, $userAgent) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->config['smtp_username'];
        $mail->Password = $this->config['smtp_password'];
        $mail->SMTPSecure = $this->config['smtp_secure'];
        $mail->Port = $this->config['smtp_port'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($this->config['smtp_username'], $this->config['from_name']);
        $mail->addAddress($this->config['to_email']);
        $mail->isHTML(true);

        $mail->Subject = "🤖 Bot Détecté sur HMB Tech";
        $mail->Body = $this->generateBotEmailBody($ip, $userAgent);

        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur d'envoi email bot: " . $e->getMessage());
    }
}

private function generateBotEmailBody($ip, $userAgent) {
    return "
    <html>
    <head>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 0; 
                padding: 20px; 
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: #0D1C49;
                color: gold;
                padding: 20px;
                border-radius: 10px;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
                padding: 10px;
                border-bottom: 2px solid gold;
            }
            .content {
                padding: 15px;
                border: 1px solid rgba(255,215,0,0.3);
                border-radius: 5px;
                margin: 10px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🤖 Bot Détecté</h1>
                <p>Détection le {$this->visitTime}</p>
            </div>
            <div class='content'>
                <p><strong>IP:</strong> {$ip}</p>
                <p><strong>User-Agent:</strong> {$userAgent}</p>
            </div>
        </div>
    </body>
    </html>";
}

// Ajoutez la notification VPN
private function sendVPNNotification($ip, $userAgent) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->config['smtp_username'];
        $mail->Password = $this->config['smtp_password'];
        $mail->SMTPSecure = $this->config['smtp_secure'];
        $mail->Port = $this->config['smtp_port'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($this->config['smtp_username'], $this->config['from_name']);
        $mail->addAddress($this->config['to_email']);
        $mail->isHTML(true);

        $mail->Subject = "⚠️ Tentative d'accès VPN/Proxy bloquée";
        $mail->Body = $this->generateVPNEmailBody($ip, $userAgent);

        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur d'envoi email VPN: " . $e->getMessage());
    }
}

private function generateVPNEmailBody($ip, $userAgent) {
    return "
    <html>
    <head>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 0; 
                padding: 20px; 
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: #0D1C49;
                color: gold;
                padding: 20px;
                border-radius: 10px;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
                padding: 10px;
                border-bottom: 2px solid gold;
            }
            .content {
                padding: 15px;
                border: 1px solid rgba(255,215,0,0.3);
                border-radius: 5px;
                margin: 10px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>⚠️ VPN/Proxy Bloqué</h1>
                <p>Détection le {$this->visitTime}</p>
            </div>
            <div class='content'>
                <p><strong>IP:</strong> {$ip}</p>
                <p><strong>User-Agent:</strong> {$userAgent}</p>
            </div>
        </div>
    </body>
    </html>";
}

// Modifications dans handleVPN pour inclure l'envoi d'email
private function handleVPN() {
    $ip = $this->getIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Log de la tentative
    $logEntry = sprintf(
        "[%s] VPN/PROXY BLOCKED | IP: %s | User-Agent: %s\n",
        date('Y-m-d H:i:s'),
        $ip,
        $userAgent
    );
    file_put_contents($this->config['vpn_log_file'], $logEntry, FILE_APPEND);
    
    // Envoi de la notification par email
    $this->sendVPNNotification($ip, $userAgent);
    
    // Nettoyage du buffer avant d'envoyer la page de blocage
    ob_clean();
    
    // Réponse avec la page de blocage
    header('HTTP/1.0 403 Forbidden');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accès Refusé</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                background-color: #0D1C49;
                font-family: Arial, sans-serif;
                color: gold;
            }
            .container {
                text-align: center;
                padding: 2rem;
                border: 2px solid gold;
                border-radius: 10px;
                background-color: rgba(13, 28, 73, 0.9);
                max-width: 80%;
                margin: 20px;
            }
            h1 {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }
            p {
                font-size: 1.2rem;
                margin: 1rem 0;
            }
            .icon {
                font-size: 4rem;
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon">🚫</div>
            <h1>Accès Refusé</h1>
            <p>L\'utilisation de VPN ou proxy n\'est pas autorisée.</p>
            <p>Veuillez désactiver votre VPN ou proxy et réessayer.</p>
        </div>
    </body>
    </html>';
    exit();
}

[Le reste de votre code...]

// Initialisation et exécution
$securityTracker = new HMBSecurityTracker();
$securityTracker->process();
?>