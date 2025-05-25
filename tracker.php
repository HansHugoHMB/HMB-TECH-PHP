<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration (à mettre dans un fichier .env)
$config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 465,
    'smtp_secure' => 'ssl',
    'smtp_username' => getenv('SMTP_USERNAME') ?: 'hmb05092006@gmail.com',
    'smtp_password' => getenv('SMTP_PASSWORD'), // À définir dans les variables d'environnement
    'to_email' => getenv('TO_EMAIL') ?: 'mbayahans@gmail.com',
    'from_name' => 'HMB Tech Tracker'
];

// Fonction pour valider les données reçues
function validateData($data) {
    $required = ['ip', 'country', 'region', 'city', 'lat', 'lon', 'isp', 'ua', 'time'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            return false;
        }
    }
    return true;
}

// Fonction pour formater le message HTML
function formatMessage($data) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { padding: 20px; }
            .field { margin: 10px 0; }
            .label { font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Nouvelle visite détectée</h2>
            <div class='field'><span class='label'>IP:</span> {$data['ip']}</div>
            <div class='field'><span class='label'>Pays:</span> {$data['country']}</div>
            <div class='field'><span class='label'>Région:</span> {$data['region']}</div>
            <div class='field'><span class='label'>Ville:</span> {$data['city']}</div>
            <div class='field'><span class='label'>Position:</span> {$data['lat']}, {$data['lon']}</div>
            <div class='field'><span class='label'>FAI:</span> {$data['isp']}</div>
            <div class='field'><span class='label'>Navigateur:</span> {$data['ua']}</div>
            <div class='field'><span class='label'>Date:</span> {$data['time']}</div>
        </div>
    </body>
    </html>";
}

try {
    // Lecture et validation des données
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input || !validateData($input)) {
        throw new Exception('Données invalides ou incomplètes');
    }

    // Journalisation des visites (optionnel)
    $logEntry = date('Y-m-d H:i:s') . " - IP: {$input['ip']} - Pays: {$input['country']}\n";
    file_put_contents('visits.log', $logEntry, FILE_APPEND);

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

    // Contenu de l'email
    $mail->Subject = 'Nouvelle visite sur HMB Tech';
    $mail->Body = formatMessage($input);
    $mail->AltBody = strip_tags(str_replace(['<br>', '</div>'], "\n", $mail->Body));

    // Envoi de l'email
    $mail->send();

    // Réponse au client
    echo json_encode([
        'status' => 'success',
        'message' => 'Données de visite enregistrées'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>