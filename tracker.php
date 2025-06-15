
<?php
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'Mobile_Detect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class HMBSecurityTracker {
    private $config = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 465,
        'smtp_secure' => 'ssl',
        'smtp_username' => 'hmb05092006@gmail.com',
        'smtp_password' => 'z u o p m w n k i e e m d g x y',
        'to_email' => 'mbayahans@gmail.com',
        'from_name' => 'HMB Tech Security Tracker',
        'allowed_domains' => [
            'hmb-tech-x.pages.dev',
            'hmb-tech',
            'localhost'
        ],
        'bot_log_file' => 'bots_log.txt',
        'visit_log_file' => 'visits.log',
        'vpn_log_file' => 'vpn_proxy.log'
    ];

    private $botsSignatures = [
        'googlebot', 'bingbot', 'yandex', 'baiduspider', 'curl',
        'python', 'wget', 'bot', 'spider', 'crawler', 'scripting',
        'headless', 'selenium', 'phantomjs', 'chrome-headless',
        'crawler', 'python-requests', 'apache-httpclient', 'java',
        'wordpress', 'php-curl', 'go-http-client', 'ruby', 'perl'
    ];

    private $ipInfo;
    private $deviceInfo;
    private $networkInfo;
    private $browserInfo;
    private $locationInfo;
    private $detect;
    private $visitTime;
    private $isBot = false;
    private $isVPN = false;

    public function __construct() {
        $this->detect = new Mobile_Detect;
        $this->visitTime = date('Y-m-d H:i:s');
        
        if ($this->checkBot()) {
            $this->handleBot();
            return;
        }
        
        if ($this->checkVPN()) {
            $this->handleVPN();
            return;
        }
        
        $this->initializeData();
    }

    private function initializeData() {
        $this->ipInfo = $this->getIpInfo();
        $this->deviceInfo = $this->getDeviceInfo();
        $this->networkInfo = $this->getNetworkInfo();
        $this->browserInfo = $this->getBrowserInfo();
        $this->locationInfo = $this->getLocationInfo();
    }

    private function checkBot() {
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        
        foreach ($this->botsSignatures as $signature) {
            if (strpos($userAgent, $signature) !== false) {
                $this->isBot = true;
                return true;
            }
        }
        
        if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) || 
            empty($_SERVER['HTTP_USER_AGENT']) ||
            isset($_SERVER['HTTP_X_FORWARDED_FOR']) && count(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])) > 3) {
            $this->isBot = true;
            return true;
        }
        
        return false;
    }

    private function handleBot() {
        $ip = $this->getIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] BOT DETECTED | IP: %s | User-Agent: %s\n",
            date('Y-m-d H:i:s'),
            $ip,
            $userAgent
        );
        file_put_contents($this->config['bot_log_file'], $logEntry, FILE_APPEND);
        
        $this->sendBotNotification($ip, $userAgent);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'bot_detected',
            'message' => 'Bot activity logged'
        ]);
        exit();
    }

    private function checkVPN() {
        $ip = $this->getIP();
        $url = "http://ip-api.com/json/{$ip}?fields=status,proxy,hosting,vpn";
        
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
            if ($data && ($data['proxy'] || $data['hosting'] || ($data['vpn'] ?? false))) {
                $this->isVPN = true;
                return true;
            }
        }
        
        return false;
    }

    private function handleVPN() {
        $ip = $this->getIP();
        
        $logEntry = sprintf(
            "[%s] VPN/PROXY BLOCKED | IP: %s | User-Agent: %s\n",
            date('Y-m-d H:i:s'),
            $ip,
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );
        file_put_contents($this->config['vpn_log_file'], $logEntry, FILE_APPEND);
        
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

    private function getIP() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
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
        $url = "http://ip-api.com/json/{$ip}?fields=status,message,continent,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,currency,isp,org,as,asname,reverse,mobile,proxy,hosting";
        
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

        return [
            'detected_ip' => $ip,
            'country' => 'Non disponible',
            'city' => 'Non disponible',
            'isp' => 'Non disponible'
        ];
    }

    private function getDeviceInfo() {
        return [
            'type' => $this->detect->isMobile() ? 
                     ($this->detect->isTablet() ? 'Tablette' : 'Mobile') : 
                     'Desktop',
            'platform' => $this->getPlatform(),
            'brand' => $this->getBrand(),
            'model' => $this->getModel(),
            'screen_resolution' => $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] ?? 'Non disponible'
        ];
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
        file_put_contents($this->config['visit_log_file'], $log_line, FILE_APPEND);
    }

    public function process() {
        if ($this->isBot || $this->isVPN) {
            return; // Déjà géré par handleBot ou handleVPN
        }

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

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Visite enregistrée']);

        } catch (Exception $e) {
            error_log("Erreur lors du traitement: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ]);
        }
    }
}

// Initialisation et exécution
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$securityTracker = new HMBSecurityTracker();
$securityTracker->process();
?>