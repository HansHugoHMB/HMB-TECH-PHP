<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json');

// Log pour debug
error_log("Requête reçue: " . file_get_contents("php://input"));

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration
$config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 465,
    'smtp_secure' => 'ssl',
    'smtp_username' => 'hmb05092006@gmail.com',
    'smtp_password' => 'z u o p m w n k i e e m d g x y',
    'to_email' => 'mbayahans@gmail.com',
    'from_name' => 'HMB Tech Tracker'
];

try {
    // Lecture des données
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input) {
        error_log("Erreur de décodage JSON: " . json_last_error_msg());
        throw new Exception('Données JSON invalides: ' . json_last_error_msg());
    }

    // Vérification des champs requis
    $required_fields = ['ip', 'country', 'region', 'city', 'lat', 'lon', 'isp', 'ua', 'time'];
    $missing_fields = array_diff($required_fields, array_keys($input));
    
    if (!empty($missing_fields)) {
        error_log("Champs manquants: " . implode(', ', $missing_fields));
        throw new Exception('Champs manquants: ' . implode(', ', $missing_fields));
    }

    // Configuration de PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_username'];
    $mail->Password = $config['smtp_password'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port = $config['smtp_port'];

    // Configuration de l'email
    $mail->setFrom($config['smtp_username'], $config['from_name']);
    $mail->addAddress($config['to_email']);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    // Contenu de l'email avec le nouveau design
    $mail->Subject = '🌟 Nouvelle Visite - HMB Tech';
    $mail->Body = "
    <html>
    <head>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f5f5f5;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background-color: #0D1C49;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .header {
                background-color: #0D1C49;
                color: gold;
                padding: 20px;
                text-align: center;
                border-bottom: 2px solid gold;
            }
            .content {
                padding: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                background-color: #0D1C49;
                color: gold;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid rgba(255,215,0,0.3);
            }
            th {
                background-color: rgba(255,215,0,0.1);
                font-weight: bold;
            }
            .footer {
                text-align: center;
                padding: 15px;
                color: gold;
                font-size: 12px;
                border-top: 2px solid gold;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌟 HMB Tech Tracker 🌟</h1>
                <p>Nouvelle visite détectée sur votre site</p>
            </div>
            <div class='content'>
                <table>
                    <tr><th>IP</th><td>{$input['ip']}</td></tr>
                    <tr><th>Pays</th><td>{$input['country']}</td></tr>
                    <tr><th>Région</th><td>{$input['region']}</td></tr>
                    <tr><th>Ville</th><td>{$input['city']}</td></tr>
                    <tr><th>Latitude</th><td>{$input['lat']}</td></tr>
                    <tr><th>Longitude</th><td>{$input['lon']}</td></tr>
                    <tr><th>FAI</th><td>{$input['isp']}</td></tr>
                    <tr><th>Navigateur</th><td>{$input['ua']}</td></tr>
                    <tr><th>Date</th><td>{$input['time']}</td></tr>
                </table>
            </div>
            <div class='footer'>
                © " . date('Y') . " HMB Tech - Système de Tracking
            </div>
        </div>
    </body>
    </html>";

    // Envoi de l'email
    $mail->send();

    // Réponse au client
    echo json_encode([
        'status' => 'success',
        'message' => 'Notification envoyée'
    ]);

} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>