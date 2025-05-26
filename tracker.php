<?php
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class VisitorTracker {
    private $config = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 465,
        'smtp_secure' => 'ssl',
        'smtp_username' => 'hmb05092006@gmail.com',
        'smtp_password' => 'z u o p m w n k i e e m d g x y',
        'to_email' => 'mbayahans@gmail.com',
        'from_name' => 'HMB Tech Tracker'
    ];

    private $ipInfo;
    private $deviceInfo;
    private $networkInfo;
    private $browserInfo;
    private $locationInfo;

    public function __construct() {
        $this->initializeData();
    }

    private function initializeData() {
        $this->ipInfo = $this->getIpInfo();
        $this->deviceInfo = $this->getDeviceInfo();
        $this->networkInfo = $this->getNetworkInfo();
        $this->browserInfo = $this->getBrowserInfo();
        $this->locationInfo = $this->getLocationInfo();
    }

    private function getIpInfo() {
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? 
              explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : 
              $_SERVER['REMOTE_ADDR'];

        $ch = curl_init("https://ipapi.co/{$ip}/json/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?: [];
    }

    private function getDeviceInfo() {
        require_once 'vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';
        $detect = new Mobile_Detect;
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        preg_match('/(iPhone|iPad|iPod|Android|BlackBerry|Windows Phone|webOS)/i', $userAgent, $device);
        preg_match('/\((.*?)\)/', $userAgent, $detailedInfo);

        return [
            'type' => $detect->isMobile() ? ($detect->isTablet() ? 'Tablette' : 'Mobile') : 'Desktop',
            'brand' => $device[0] ?? 'Non détecté',
            'os' => $this->getOS($userAgent),
            'details' => $detailedInfo[1] ?? 'Non disponible',
            'screen' => $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? 'Non disponible'
        ];
    }

    private function getNetworkInfo() {
        return [
            'isp' => $this->ipInfo['org'] ?? 'Non disponible',
            'asn' => $this->ipInfo['asn'] ?? 'Non disponible',
            'connection_type' => $_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'Non disponible',
            'proxy' => $this->isUsingProxy() ? 'Oui' : 'Non',
            'vpn' => $this->isVPN() ? 'Possible' : 'Non détecté'
        ];
    }

    private function getBrowserInfo() {
        $browser = get_browser(null, true);
        return [
            'name' => $browser['browser'] ?? 'Non détecté',
            'version' => $browser['version'] ?? 'Non détecté',
            'language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Non disponible',
            'plugins' => $_SERVER['HTTP_SEC_CH_UA'] ?? 'Non disponible',
            'cookies_enabled' => isset($_COOKIE) ? 'Oui' : 'Non',
            'do_not_track' => $_SERVER['HTTP_DNT'] ?? 'Non spécifié'
        ];
    }

    private function getLocationInfo() {
        return [
            'country' => $this->ipInfo['country_name'] ?? 'Non disponible',
            'region' => $this->ipInfo['region'] ?? 'Non disponible',
            'city' => $this->ipInfo['city'] ?? 'Non disponible',
            'postal' => $this->ipInfo['postal'] ?? 'Non disponible',
            'latitude' => $this->ipInfo['latitude'] ?? 'Non disponible',
            'longitude' => $this->ipInfo['longitude'] ?? 'Non disponible',
            'timezone' => $this->ipInfo['timezone'] ?? 'Non disponible'
        ];
    }

    private function getOS($userAgent) {
        $os_array = [
            '/windows/i' => 'Windows',
            '/macintosh|mac os x/i' => 'macOS',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/webos/i' => 'Mobile'
        ];

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                return $value;
            }
        }
        return 'Unknown';
    }

    private function isUsingProxy() {
        $proxy_headers = [
            'HTTP_VIA',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED_FOR_IP',
            'VIA',
            'X_FORWARDED_FOR',
            'FORWARDED_FOR',
            'X_FORWARDED',
            'FORWARDED',
            'CLIENT_IP',
            'FORWARDED_FOR_IP',
            'HTTP_PROXY_CONNECTION'
        ];

        foreach($proxy_headers as $header) {
            if (isset($_SERVER[$header])) return true;
        }
        return false;
    }

    private function isVPN() {
        $suspiciousPortsCount = 0;
        $commonVPNPorts = [1194, 500, 4500, 1701, 1723];
        
        if (isset($_SERVER['REMOTE_PORT'])) {
            $port = (int)$_SERVER['REMOTE_PORT'];
            if (in_array($port, $commonVPNPorts)) $suspiciousPortsCount++;
        }
        
        return $suspiciousPortsCount > 0;
    }

    public function sendTrackingEmail() {
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

            $siteName = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST) ?: 'Direct';
            $mail->Subject = "🌟 Nouvelle visite sur {$siteName}";

            $mail->Body = $this->generateEmailBody();
            $mail->send();

            $this->logVisit();

        } catch (Exception $e) {
            error_log("Erreur d'envoi: {$e->getMessage()}");
        }
    }

    private function generateEmailBody() {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: #0D1C49;
                    color: gold;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                .section {
                    margin: 20px 0;
                    padding: 15px;
                    border: 1px solid rgba(255,215,0,0.3);
                    border-radius: 5px;
                }
                .section-title {
                    font-size: 1.2em;
                    margin-bottom: 10px;
                    border-bottom: 1px solid gold;
                    padding-bottom: 5px;
                }
                .data-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 10px;
                }
                .data-item {
                    padding: 5px;
                    background: rgba(255,215,0,0.1);
                    border-radius: 3px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
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
                    <h1>🌟 Détails de la visite 🌟</h1>
                    <p>Date : " . date('d/m/Y H:i:s') . "</p>
                </div>

                <div class='section'>
                    <div class='section-title'>📱 Appareil</div>
                    <div class='data-grid'>
                        <div class='data-item'>Type: {$this->deviceInfo['type']}</div>
                        <div class='data-item'>Marque: {$this->deviceInfo['brand']}</div>
                        <div class='data-item'>OS: {$this->deviceInfo['os']}</div>
                        <div class='data-item'>Détails: {$this->deviceInfo['details']}</div>
                    </div>
                </div>

                <div class='section'>
                    <div class='section-title'>🌍 Localisation</div>
                    <div class='data-grid'>
                        <div class='data-item'>Pays: {$this->locationInfo['country']}</div>
                        <div class='data-item'>Ville: {$this->locationInfo['city']}</div>
                        <div class='data-item'>Région: {$this->locationInfo['region']}</div>
                        <div class='data-item'>Code Postal: {$this->locationInfo['postal']}</div>
                        <div class='data-item'>Latitude: {$this->locationInfo['latitude']}</div>
                        <div class='data-item'>Longitude: {$this->locationInfo['longitude']}</div>
                    </div>
                </div>

                <div class='section'>
                    <div class='section-title'>🌐 Réseau</div>
                    <div class='data-grid'>
                        <div class='data-item'>FAI: {$this->networkInfo['isp']}</div>
                        <div class='data-item'>ASN: {$this->networkInfo['asn']}</div>
                        <div class='data-item'>Proxy: {$this->networkInfo['proxy']}</div>
                        <div class='data-item'>VPN: {$this->networkInfo['vpn']}</div>
                    </div>
                </div>

                <div class='section'>
                    <div class='section-title'>🔍 Navigateur</div>
                    <div class='data-grid'>
                        <div class='data-item'>Nom: {$this->browserInfo['name']}</div>
                        <div class='data-item'>Version: {$this->browserInfo['version']}</div>
                        <div class='data-item'>Langue: {$this->browserInfo['language']}</div>
                        <div class='data-item'>Cookies: {$this->browserInfo['cookies_enabled']}</div>
                    </div>
                </div>

                <div class='footer'>
                    © " . date('Y') . " HMB Tech - Système de tracking avancé
                </div>
            </div>
        </body>
        </html>";
    }

    private function logVisit() {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $this->ipInfo['ip'],
            'device' => $this->deviceInfo['type'],
            'location' => "{$this->locationInfo['city']}, {$this->locationInfo['country']}",
            'browser' => $this->browserInfo['name']
        ];

        $logLine = implode(' | ', $logData) . "\n";
        file_put_contents('visits.log', $logLine, FILE_APPEND);
    }
}

// Utilisation
$tracker = new VisitorTracker();
$tracker->sendTrackingEmail();
?>