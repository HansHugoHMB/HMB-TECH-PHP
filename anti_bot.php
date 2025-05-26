<?php
class StrictBotProtection {
    private $botSignatures = [
        // Moteurs de recherche majeurs
        'Googlebot', 'bingbot', 'Yandex', 'DuckDuckBot', 'Baiduspider', 'YahooSeeker',
        'facebookexternalhit', 'Applebot', 'TwitterBot', 'LinkedInBot', 'Slackbot',
        
        // Bots génériques
        'bot', 'spider', 'crawler', 'scraper', 'robot', 'indexer',
        
        // Outils d'analyse
        'GTmetrix', 'Pingdom', 'pagespeed', 'lighthouse', 'chrome-lighthouse',
        
        // Outils de développement
        'Postman', 'curl', 'wget', 'python', 'ruby', 'perl', 'java', 'axios', 'node',
        
        // Navigateurs headless
        'Phantom', 'Selenium', 'Headless', 'puppeteer', 'playwright',
        
        // Frameworks de test
        'cypress', 'webdriver', 'chromedriver',
        
        // Autres bots connus
        'semrush', 'ahrefs', 'majestic', 'moz', 'rogerbot', 'screaming frog',
        'proximic', 'scoutjet', 'yahoo! slurp', 'teoma', 'provideSupport',
        
        // Outils de monitoring
        'pingdom', 'uptimerobot', 'statuscake', 'newrelic', 'datadog',
        
        // Réseaux sociaux
        'pinterest', 'instagram', 'whatsapp', 'telegram', 'discord', 'slack'
    ];

    private $suspiciousHeaders = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_HOST',
        'HTTP_X_FORWARDED_PROTO',
        'HTTP_X_ORIGINAL_URL',
        'HTTP_CLIENT_IP'
    ];

    public function isBot() {
        if ($this->checkUserAgent() || 
            $this->checkHeaders() || 
            $this->checkBehavior() || 
            $this->checkRequestPattern() ||
            $this->checkBrowserFingerprint()) {
            return true;
        }
        return false;
    }

    private function checkUserAgent() {
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        
        // Si pas de User-Agent, c'est suspect
        if (empty($userAgent)) {
            return true;
        }

        // Vérification des signatures de bots
        foreach ($this->botSignatures as $signature) {
            if (strpos($userAgent, strtolower($signature)) !== false) {
                return true;
            }
        }

        // Vérification de la longueur (les bots ont souvent des UA très courts ou très longs)
        if (strlen($userAgent) < 30 || strlen($userAgent) > 500) {
            return true;
        }

        return false;
    }

    private function checkHeaders() {
        // Vérification des en-têtes obligatoires
        $requiredHeaders = ['HTTP_ACCEPT', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_USER_AGENT'];
        foreach ($requiredHeaders as $header) {
            if (!isset($_SERVER[$header])) {
                return true;
            }
        }

        // Vérification des en-têtes suspects
        foreach ($this->suspiciousHeaders as $header) {
            if (isset($_SERVER[$header])) {
                return true;
            }
        }

        return false;
    }

    private function checkBehavior() {
        // Vérification du Referer
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_SERVER['HTTP_REFERER'])) {
            return true;
        }

        // Vérification de la vitesse d'accès
        session_start();
        $currentTime = microtime(true);
        $lastAccess = $_SESSION['last_access'] ?? 0;
        $_SESSION['last_access'] = $currentTime;

        // Si moins de 500ms entre les requêtes
        if (($currentTime - $lastAccess) < 0.5) {
            return true;
        }

        return false;
    }

    private function checkRequestPattern() {
        // Vérification des paramètres de requête suspects
        $suspiciousParams = ['eval', 'exec', 'system', 'cmd', 'payload', 'script'];
        foreach ($suspiciousParams as $param) {
            if (isset($_REQUEST[$param])) {
                return true;
            }
        }

        // Vérification des motifs d'URL suspects
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $suspiciousPatterns = ['/wp-', '/admin', '/login', '/wp-login', '/administrator'];
        foreach ($suspiciousPatterns as $pattern) {
            if (strpos($requestUri, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function checkBrowserFingerprint() {
        // Vérification des caractéristiques du navigateur
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) || 
            !isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return true;
        }

        // Vérification de la cohérence de la plateforme
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        $acceptLanguage = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');

        // Si l'UA indique Windows mais pas de support pour les formats Windows
        if (strpos($userAgent, 'windows') !== false && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/x-ms-application') === false) {
            return true;
        }

        return false;
    }

    public function blockBot() {
        // En-têtes pour empêcher la mise en cache
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        
        // Réponse 403 Forbidden
        http_response_code(403);
        
        // Message d'erreur en JSON
        die(json_encode([
            'status' => 'error',
            'code' => 403,
            'message' => 'Access denied',
            'timestamp' => date('Y-m-d H:i:s')
        ]));
    }
}

// Instanciation et utilisation
$botProtection = new StrictBotProtection();
if ($botProtection->isBot()) {
    $botProtection->blockBot();
}
?>