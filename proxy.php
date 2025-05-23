<?php
// Configuration de base
error_reporting(0);
ini_set('display_errors', 0);

// Fonction pour gérer le proxy
function proxyRequest($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate',
            'X-Forwarded-For: 104.28.42.1',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1'
        ]
    ]);

    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    // Injecter le bouton flottant si c'est du HTML
    if (stripos($contentType, 'text/html') !== false) {
        $button = <<<HTML
        <div id="hmb-proxy-ui" style="position: fixed; bottom: 20px; right: 20px; z-index: 2147483647;">
            <button onclick="document.getElementById('hmb-proxy-form').style.display = document.getElementById('hmb-proxy-form').style.display === 'none' ? 'block' : 'none'"
                    style="width: 50px; height: 50px; border-radius: 25px; background: gold; border: none; cursor: pointer; font-size: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                🌐
            </button>
            <form id="hmb-proxy-form" onsubmit="event.preventDefault(); window.location.href='/proxy.php/' + (document.getElementById('hmb-proxy-url').value.startsWith('http') ? '' : 'https://') + document.getElementById('hmb-proxy-url').value;" 
                  style="display: none; position: absolute; bottom: 60px; right: 0; background: #0D1C40; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                <input type="text" id="hmb-proxy-url" placeholder="Entrez une URL" 
                       style="width: 200px; padding: 5px; margin-bottom: 5px; border: 1px solid gold; background: #0D1C40; color: gold;">
                <button type="submit" style="width: 100%; padding: 5px; background: gold; border: none; cursor: pointer;">Charger</button>
            </form>
        </div>
HTML;
        $body = str_ireplace('</body>', $button . '</body>', $body);
    }

    // Transmettre les en-têtes pertinents
    $headerLines = explode("\n", $headers);
    foreach ($headerLines as $line) {
        if (preg_match('/^(?:Content-Type|Content-Language|Cache-Control|Expires|Last-Modified)/i', $line)) {
            header(trim($line));
        }
    }

    return $body;
}

// Traitement de la requête
$requestUri = $_SERVER['REQUEST_URI'];
$proxyPrefix = '/proxy.php/';

if (strpos($requestUri, $proxyPrefix) === 0) {
    $targetUrl = substr($requestUri, strlen($proxyPrefix));
    
    if (empty($targetUrl)) {
        header('Location: /proxy.php');
        exit;
    }

    if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
        $targetUrl = 'https://' . $targetUrl;
    }

    try {
        echo proxyRequest($targetUrl);
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo "Erreur: " . $e->getMessage();
    }
    exit;
}

// Page d'accueil
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMB Tech - Proxy</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #0D1C40;
            color: gold;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 600px;
        }
        h1 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>HMB Tech - Proxy</h1>
        <p>Cliquez sur le bouton 🌐 pour commencer</p>
    </div>
    <div id="hmb-proxy-ui" style="position: fixed; bottom: 20px; right: 20px; z-index: 2147483647;">
        <button onclick="document.getElementById('hmb-proxy-form').style.display = document.getElementById('hmb-proxy-form').style.display === 'none' ? 'block' : 'none'"
                style="width: 50px; height: 50px; border-radius: 25px; background: gold; border: none; cursor: pointer; font-size: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
            🌐
        </button>
        <form id="hmb-proxy-form" onsubmit="event.preventDefault(); window.location.href='/proxy.php/' + (document.getElementById('hmb-proxy-url').value.startsWith('http') ? '' : 'https://') + document.getElementById('hmb-proxy-url').value;"
              style="display: none; position: absolute; bottom: 60px; right: 0; background: #0D1C40; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
            <input type="text" id="hmb-proxy-url" placeholder="Entrez une URL"
                   style="width: 200px; padding: 5px; margin-bottom: 5px; border: 1px solid gold; background: #0D1C40; color: gold;">
            <button type="submit" style="width: 100%; padding: 5px; background: gold; border: none; cursor: pointer;">Charger</button>
        </form>
    </div>
</body>
</html>