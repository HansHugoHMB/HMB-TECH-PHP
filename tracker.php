<?php
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'Mobile_Detect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

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
    private $detect;

    public function __construct() {
        $this->detect = new Mobile_Detect;
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

        $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,message,continent,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,currency,isp,org,as,asname,reverse,mobile,proxy,hosting,query");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        
        if(curl_errno($ch)) {
            error_log('Erreur Curl: ' . curl_error($ch));
        }
        
        curl_close($ch);
        $data = json_decode($response, true);
        
        return $data ?: [];
    }

    private function getDeviceInfo() {
        $deviceInfo = [];
        
        // Type d'appareil avec Mobile_Detect
        if($this->detect->isMobile()) {
            if($this->detect->isTablet()) {
                $deviceInfo['type'] = 'Tablette';
            } else {
                $deviceInfo['type'] = 'Mobile';
            }
        } else {
            $deviceInfo['type'] = 'Desktop';
        }

        // Détection détaillée du device
        if($this->detect->isAndroidOS()) {
            $deviceInfo['platform'] = 'Android';
        } elseif($this->detect->isiOS()) {
            $deviceInfo['platform'] = 'iOS';
        } else {
            $deviceInfo['platform'] = $this->getOS();
        }

        // Marque spécifique pour mobile
        if($this->detect->isMobile()) {
            foreach([
                'iPhone', 'iPad', 'Samsung', 'Huawei', 'Xiaomi', 
                'OnePlus', 'LG', 'Sony', 'Motorola', 'Nokia'
            ] as $brand) {
                if($this->detect->is($brand)) {
                    $deviceInfo['brand'] = $brand;
                    break;
                }
            }
        }

        if(!isset($deviceInfo['brand'])) {
            $deviceInfo['brand'] = 'Non identifié';
        }

        return $deviceInfo;
    }

    private function getOS() {
        $os_array = [
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/macintosh|mac os x/i' => 'macOS',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipod/i'               => 'iPod',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/webos/i'              => 'Mobile'
        ];

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                return $value;
            }
        }
        return 'Système inconnu';
    }

    private function getNetworkInfo() {
        return [
            'isp' => $this->ipInfo['isp'] ?? 'Non disponible',
            'asn' => $this->ipInfo['as'] ?? 'Non disponible',
            'org' => $this->ipInfo['org'] ?? 'Non disponible',
            'proxy' => ($this->ipInfo['proxy'] ?? false) ? 'Oui' : 'Non',
            'hosting' => ($this->ipInfo['hosting'] ?? false) ? 'Oui' : 'Non',
            'mobile_network' => ($this->ipInfo['mobile'] ?? false) ? 'Oui' : 'Non',
            'connection_type' => $this->detect->isMobile() ? 'Mobile' : 'Fixe'
        ];
    }

    private function getBrowserInfo() {
        $browserInfo = [];
        $ua = $_SERVER['HTTP_USER_AGENT'];

        // Détection du navigateur
        if (strpos($ua, 'Firefox') !== false) {
            $browserInfo['name'] = 'Firefox';
        } elseif (strpos($ua, 'Chrome') !== false && strpos($ua, 'Edg') === false) {
            $browserInfo['name'] = 'Chrome';
        } elseif (strpos($ua, 'Safari') !== false && strpos($ua, 'Chrome') === false) {
            $browserInfo['name'] = 'Safari';
        } elseif (strpos($ua, 'Edg') !== false) {
            $browserInfo['name'] = 'Edge';
        } else {
            $browserInfo['name'] = 'Autre';
        }

        // Version du navigateur
        preg_match('/' . $browserInfo['name'] . '\/([0-9.]+)/', $ua, $matches);
        $browserInfo['version'] = isset($matches[1]) ? $matches[1] : 'Version inconnue';

        $browserInfo['language'] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'fr', 0, 2);
        $browserInfo['cookies_enabled'] = isset($_COOKIE) ? 'Oui' : 'Non';
        $browserInfo['user_agent'] = $ua;

        return $browserInfo;
    }

    private function getLocationInfo() {
        return [
            'pays' => $this->ipInfo['country'] ?? 'Non disponible',
            'region' => $this->ipInfo['regionName'] ?? 'Non disponible',
            'ville' => $this->ipInfo['city'] ?? 'Non disponible',
            'code_postal' => $this->ipInfo['zip'] ?? 'Non disponible',
            'latitude' => $this->ipInfo['lat'] ?? 'Non disponible',
            'longitude' => $this->ipInfo['lon'] ?? 'Non disponible',
            'timezone' => $this->ipInfo['timezone'] ?? 'Non disponible',
            'fuseau_horaire' => date('P'),
            'heure_locale' => date('H:i:s')
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
            echo json_encode(['status' => 'success']);

        } catch (Exception $e) {
            error_log("Erreur d'envoi: {$e->getMessage()}");
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function generateEmailBody() {
        // Le même style que précédemment avec les sections pour appareil, localisation, réseau et navigateur
        // [Code du template HTML précédent]
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
                    padding: 5px;
                    background: rgba(255,215,0,0.1);
                    border-radius: 3px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>🌟 Nouvelle Visite Détectée 🌟</h1>
                
                <div class='section'>
                    <div class='section-title'>📱 Appareil</div>
                    <div class='data-grid'>
                        <div class='data-item'>Type: {$this->deviceInfo['type']}</div>
                        <div class='data-item'>Marque: {$this->deviceInfo['brand']}</div>
                        <div class='data-item'>Plateforme: {$this->deviceInfo['platform']}</div>
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
                    <div class='section-title'>🌐 Réseau</div>
                    <div class='data-grid'>
                        <div class='data-item'>FAI: {$this->networkInfo['isp']}</div>
                        <div class='data-item'>Type: {$this->networkInfo['connection_type']}</div>
                        <div class='data-item'>Proxy: {$this->networkInfo['proxy']}</div>
                        <div class='data-item'>Réseau Mobile: {$this->networkInfo['mobile_network']}</div>
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
            </div>
        </body>
        </html>";
    }

    private function logVisit() {
        $logData = [
            'date' => date('Y-m-d H:i:s'),
            'ip' => $this->ipInfo['query'] ?? 'Unknown',
            'device' => $this->deviceInfo['type'],
            'location' => "{$this->locationInfo['ville']}, {$this->locationInfo['pays']}",
            'browser' => $this->browserInfo['name']
        ];

        $logLine = implode(' | ', $logData) . "\n";
        file_put_contents('visits.log', $logLine, FILE_APPEND);
    }
}

// Exécution
$tracker = new VisitorTracker();
$tracker->sendTrackingEmail();
?>