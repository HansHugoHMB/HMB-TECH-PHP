<?php
error_reporting(0);
ini_set('display_errors', 0);

// Définition de la fonction de proxy
function proxyURL($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'X-Forwarded-For: 104.28.42.1'
        ]
    ]);

    $response = curl_exec($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    header('Content-Type: ' . $contentType);
    echo $response;
    exit;
}

// Traitement de l'URL du proxy
$prefix = '/proxy/';
if (isset($_SERVER['PATH_INFO'])) {
    $url = substr($_SERVER['PATH_INFO'], 1);
    if (!empty($url)) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $url = 'https://' . $url;
        }
        proxyURL($url);
        exit;
    }
}

// Page d'accueil avec le formulaire
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
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .url-form {
            background: rgba(255, 215, 0, 0.1);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid gold;
            margin-bottom: 30px;
        }

        input[type="url"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            background: #0D1C40;
            border: 1px solid gold;
            color: gold;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: gold;
            color: #0D1C40;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            opacity: 0.9;
        }

        .examples {
            background: rgba(255, 215, 0, 0.1);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid gold;
        }

        .examples h2 {
            margin-top: 0;
        }

        .examples a {
            color: gold;
            text-decoration: none;
            display: block;
            margin: 10px 0;
        }

        .examples a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>HMB Tech - Proxy</h1>
        
        <form class="url-form" action="/proxy.php" method="get" onsubmit="event.preventDefault(); window.location.href='/proxy.php/' + (document.getElementById('url').value.startsWith('http') ? '' : 'https://') + document.getElementById('url').value;">
            <input type="text" id="url" placeholder="Entrez l'URL à charger..." required>
            <button type="submit">Charger</button>
        </form>

        <div class="examples">
            <h2>Exemples d'utilisation :</h2>
            <a href="/proxy.php/meta.ai">meta.ai</a>
            <a href="/proxy.php/chat.openai.com">chat.openai.com</a>
            <a href="/proxy.php/google.com">google.com</a>
        </div>
    </div>
</body>
</html>