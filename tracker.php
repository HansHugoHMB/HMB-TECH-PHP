<?php
// En-têtes de sécurité
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Configuration PHPMailer
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

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
    'from_name' => 'HMB Tech Tracker',
    'allowed_domains' => [
        'hmb-tech-x.pages.dev',
        'hmb-tech'  // Pour matcher tous les domaines contenant hmb-tech
    ]
];

// Vérifier si le domaine est autorisé
$referer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : '';
$isAllowedDomain = false;

foreach ($config['allowed_domains'] as $domain) {
    if (strpos($referer, $domain) !== false) {
        $isAllowedDomain = true;
        break;
    }
}

if (!$isAllowedDomain) {
    exit('Accès non autorisé');
}

// Fonction pour obtenir les informations de localisation
function getIpInfo() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $response = file_get_contents("http://ip-api.com/json/{$ip}");
    $data = json_decode($response, true);
    return $data ?: ['status' => 'fail'];
}

try {
    // Collecter les informations
    $ipInfo = getIpInfo();
    $currentTime = date('Y-m-d H:i:s');
    
    // Créer PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_username'];
    $mail->Password = $config['smtp_password'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port = $config['smtp_port'];
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($config['smtp_username'], $config['from_name']);
    $mail->addAddress($config['to_email']);
    $mail->isHTML(true);
    
    // Sujet personnalisé avec le nom du site
    $siteName = $referer ?: 'Site HMB Tech';
    $mail->Subject = "🌟 Nouvelle visite sur {$siteName}";

    // Corps de l'email
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: #0D1C49;
                color: gold;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid rgba(255,215,0,0.3);
            }
            th { background-color: rgba(255,215,0,0.1); }
            .header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid gold;
                padding-bottom: 10px;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 12px;
                border-top: 2px solid gold;
                padding-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌟 Nouvelle visite sur {$siteName} 🌟</h1>
            </div>
            
            <table>
                <tr>
                    <th>Site</th>
                    <td>" . htmlspecialchars($referer) . "</td>
                </tr>
                <tr>
                    <th>Date et Heure</th>
                    <td>" . htmlspecialchars($currentTime) . "</td>
                </tr>
                <tr>
                    <th>IP</th>
                    <td>" . htmlspecialchars($ipInfo['query'] ?? 'Non disponible') . "</td>
                </tr>
                <tr>
                    <th>Pays</th>
                    <td>" . htmlspecialchars($ipInfo['country'] ?? 'Non disponible') . "</td>
                </tr>
                <tr>
                    <th>Ville</th>
                    <td>" . htmlspecialchars($ipInfo['city'] ?? 'Non disponible') . "</td>
                </tr>
                <tr>
                    <th>FAI</th>
                    <td>" . htmlspecialchars($ipInfo['isp'] ?? 'Non disponible') . "</td>
                </tr>
                <tr>
                    <th>User Agent</th>
                    <td>" . htmlspecialchars($_SERVER['HTTP_USER_AGENT']) . "</td>
                </tr>
            </table>

            <div class='footer'>
                © " . date('Y') . " HMB Tech - Système de tracking
            </div>
        </div>
    </body>
    </html>";

    // Envoyer l'email
    $mail->send();

    // Log la visite
    $logMessage = sprintf(
        "[%s] Visite depuis %s - IP: %s\n",
        $currentTime,
        $referer,
        $ipInfo['query'] ?? 'Unknown'
    );
    error_log($logMessage, 3, 'visits.log');

} catch (Exception $e) {
    error_log("Erreur de tracking: " . $e->getMessage());
}

// Ne rien afficher pour ne pas interférer avec le site d'origine
?>