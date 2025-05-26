<?php
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'Mobile_Detect.php';
require 'anti_bot.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// En-têtes CORS pour permettre l'accès depuis vos domaines
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

class HMBTechTracker {
    private $config = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 465,
        'smtp_secure' => 'ssl',
        'smtp_username' => 'hmb05092006@gmail.com',
        'smtp_password' => 'z u o p m w n k i e e m d g x y',
        'to_email' => 'mbayahans@gmail.com',
        'from_name' => 'HMB Tech Tracker',
        'allowed_domains' => [
            'hmb-tech-x.pages.dev',
            'hmb-tech',
            'localhost'
        ]
    ];

    private $ipInfo;
    private $deviceInfo;
    private $networkInfo;
    private $browserInfo;
    private $locationInfo;
    private $detect;
    private $visitTime;

    public function __construct() {
        $this->detect = new Mobile_Detect;
        $this->visitTime = date('Y-m-d H:i:s');
        $this->initializeData();
    }

    private function initializeData() {
        $this->ipInfo = $this->getIpInfo();
        $this->deviceInfo = $this->getDeviceInfo();
        $this->networkInfo = $this->getNetworkInfo();
        $this->browserInfo = $this->getBrowserInfo();
        $this->locationInfo = $this->getLocationInfo();
    }

    private function getIP() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (isset($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'IP inconnue';
    }

    private function getIpInfo() {
        $ip = $this->getIP();
        
        // Premier essai avec ip-api.com
        $url = "http://ip-api.com/json/{$ip}?fields=status,message,continent,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,currency,isp,org,as,asname,reverse,mobile,proxy,hosting,query";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200 && $response) {
            $data = json_decode($response, true);
            if ($data && isset($data['status']) && $data['status'] === 'success') {
                $data['detected_ip'] = $ip;
                return $data;
            }
        }

        // Fallback avec ipapi.co
        return [
            'detected_ip' => $ip,
            'country' => 'Non disponible',
            'city' => 'Non disponible',
            'isp' => 'Non disponible'
        ];
    }

    private function getDeviceInfo() {
        $device = [
            'type' => $this->detect->isMobile() ? 
                     ($this->detect->isTablet() ? 'Tablette' : 'Mobile') : 
                     'Desktop',
            'platform' => $this->getPlatform(),
            'brand' => $this->getBrand(),
            'model' => $this->getModel(),
            'screen_resolution' => $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] ?? 'Non disponible'
        ];

        error_log("Device Info: " . print_r($device, true));
        return $device;
    }

    private function getPlatform() {
        if ($this->detect->isiOS()) return 'iOS';
        if ($this->detect->isAndroidOS()) return 'Android';
        
        $platforms = [
            '/windows/i' => 'Windows',
            '/macintosh|mac os x/i' => 'macOS',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu'
        ];

        foreach ($platforms as $regex => $platform) {
            if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
                return $platform;
            }
        }
        
        return 'Plateforme inconnue';
    }

    private function getBrand() {
        $brands = [
            'iPhone' => 'Apple',
            'iPad' => 'Apple',
            'Samsung' => 'Samsung',
            'Huawei' => 'Huawei',
            'Xiaomi' => 'Xiaomi',
            'OPPO' => 'OPPO',
            'OnePlus' => 'OnePlus',
            'LG' => 'LG',
            'Sony' => 'Sony',
            'Nokia' => 'Nokia'
        ];

        foreach ($brands as $key => $brand) {
            if ($this->detect->is($key)) {
                return $brand;
            }
        }

        return $this->detect->isMobile() ? 'Autre mobile' : 'PC/Mac';
    }

    private function getModel() {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        preg_match('/(iPhone|iPad|Android|Windows Phone|Tablet|Mobile)[\s\/]+([\d.]+)/', $ua, $matches);
        return $matches[1] ?? ($this->detect->isMobile() ? 'Mobile inconnu' : 'Desktop');
    }

    private function getNetworkInfo() {
        return [
            'isp' => $this->ipInfo['isp'] ?? 'Non disponible',
            'as' => $this->ipInfo['as'] ?? 'Non disponible',
            'organization' => $this->ipInfo['org'] ?? 'Non disponible',
            'connection_type' => $this->detect->isMobile() ? 'Mobile' : 'Fixe',
            'proxy_detected' => $this->ipInfo['proxy'] ?? false ? 'Oui' : 'Non',
            'vpn_detected' => $this->ipInfo['hosting'] ?? false ? 'Possible' : 'Non détecté'
        ];
    }

    private function getBrowserInfo() {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $browser_name = 'Inconnu';
        $version = '';

        if (preg_match('/Firefox\/([0-9.]+)/', $ua, $matches)) {
            $browser_name = 'Firefox';
            $version = $matches[1];
        } elseif (preg_match('/Chrome\/([0-9.]+)/', $ua, $matches)) {
            $browser_name = 'Chrome';
            $version = $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $ua, $matches)) {
            $browser_name = 'Safari';
            $version = $matches[1];
        } elseif (preg_match('/Edge\/([0-9.]+)/', $ua, $matches)) {
            $browser_name = 'Edge';
            $version = $matches[1];
        }

        return [
            'name' => $browser_name,
            'version' => $version,
            'language' => substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'fr', 0, 2),
            'user_agent' => $ua,
            'cookies_enabled' => isset($_COOKIE) ? 'Oui' : 'Non'
        ];
    }

    private function getLocationInfo() {
        return [
            'pays' => $this->ipInfo['country'] ?? 'Non disponible',
            'region' => $this->ipInfo['regionName'] ?? 'Non disponible',
            'ville' => $this->ipInfo['city'] ?? 'Non disponible',
            'code_postal' => $this->ipInfo['zip'] ?? 'Non disponible',
            'latitude' => $this->ipInfo['lat'] ?? 'Non disponible',
            'longitude' => $this->ipInfo['lon'] ?? 'Non disponible',
            'timezone' => $this->ipInfo['timezone'] ?? 'Non disponible'
        ];
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

            $referer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : 'Accès Direct';
            $mail->Subject = "🌟 Nouvelle visite sur {$referer}";
            $mail->Body = $this->generateEmailBody();

            $mail->send();
            $this->logVisit();

            return ['status' => 'success', 'message' => 'Visite enregistrée'];

        } catch (Exception $e) {
            error_log("Erreur d'envoi email: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function generateEmailBody() {
        return "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 20px; 
                    background-color: #f5f5f5;
                }
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
                    padding: 8px;
                    background: rgba(255,215,0,0.1);
                    border-radius: 3px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding: 20px;
                    border-bottom: 2px solid gold;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    padding: 10px;
                    border-top: 2px solid gold;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🌟 Nouvelle Visite HMB Tech 🌟</h1>
                    <p>Détectée le {$this->visitTime}</p>
                </div>

                <div class='section'>
                    <div class='section-title'>🌐 Informations IP</div>
                    <div class='data-grid'>
                        <div class='data-item'>
                            <strong>IP:</strong> {$this->ipInfo['detected_ip']}
                        </div>
                        <div class='data-item'>
                            <strong>FAI:</strong> {$this->networkInfo['isp']}
                        </div>
                        <div class='data-item'>
                            <strong>Proxy:</strong> {$this->networkInfo['proxy_detected']}
                        </div>
                        <div class='data-item'>
                            <strong>VPN:</strong> {$this->networkInfo['vpn_detected']}
                        </div>
                    </div>
                </div>

                <div class='section'>
                    <div class='section-title'>📱 Appareil</div>
                    <div class='data-grid'>
                        <div class='data-item'>Type: {$this->deviceInfo['type']}</div>
                        <div class='data-item'>Marque: {$this->deviceInfo['brand']}</div>
                        <div class='data-item'>Modèle: {$this->deviceInfo['model']}</div>
                        <div class='data-item'>OS: {$this->deviceInfo['platform']}</div>
                    </div>
                </div>

                <div class='section'>
                    <div class='section-title'>📍 Localisation</div>
                    <div class='data-grid'>
                        <div class='data-item'>Pays: {$this->locationInfo['pays']}</div>
                        <div class='data-item'>Ville: {$this->locationInfo['ville']}</div>
                        <div class='data-item'>Région: {$this->locationInfo['region']}</div>
                        <div class='data-item'>Code Postal: {$this->locationInfo['code_postal']}</div>
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
                    © " . date('Y') . " HMB Tech - Système de tracking avancé<br>
                    Développé par Hans Hugo
                </div>
            </div>
        </body>
        </html>";
    }

    private function logVisit() {
        $log_data = [
            'timestamp' => $this->visitTime,
            'ip' => $this->ipInfo['detected_ip'],
            'pays' => $this->locationInfo['pays'],
            'ville' => $this->locationInfo['ville'],
            'appareil' => $this->deviceInfo['type'],
            'navigateur' => $this->browserInfo['name']
        ];

        $log_line = implode(' | ', $log_data) . "\n";
        file_put_contents('visits.log', $log_line, FILE_APPEND);
    }
}

// Exécution
$tracker = new HMBTechTracker();
$result = $tracker->sendTrackingEmail();

// Réponse JSON
echo json_encode($result);
?>