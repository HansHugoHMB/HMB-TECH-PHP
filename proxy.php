<?php
error_reporting(0);
ini_set('display_errors', 0);

// Configuration des en-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

// Fonction pour gérer le cache
function manageCache($url) {
    try {
        $cacheFile = 'cache.json';
        $cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
        $cache[$url] = ['timestamp' => time()];
        file_put_contents($cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
    } catch (Exception $e) {
        // Silent fail if cache fails
    }
}

// Traitement de la requête proxy
if (isset($_SERVER['PATH_INFO'])) {
    $targetUrl = substr($_SERVER['PATH_INFO'], 1);
    
    if (!empty($targetUrl)) {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $targetUrl,
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

            // Enregistrer dans le cache
            manageCache($targetUrl);

            // Définir le type de contenu
            header('Content-Type: ' . $contentType);

            // Injecter le bouton flottant si c'est du HTML
            if (stripos($contentType, 'text/html') !== false) {
                $button = <<<HTML
                <div id="hmb-proxy-button" style="position: fixed; bottom: 20px; right: 20px; z-index: 2147483647;">
                    <button onclick="document.getElementById('hmb-proxy-form').style.display = document.getElementById('hmb-proxy-form').style.display === 'none' ? 'block' : 'none'"
                            style="width: 50px; height: 50px; border-radius: 25px; background: gold; border: none; cursor: pointer; font-size: 24px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                        🌐
                    </button>
                    <form id="hmb-proxy-form" onsubmit="event.preventDefault(); window.location.href='/proxy.php/' + (this.querySelector('input').value.startsWith('http') ? '' : 'https://') + this.querySelector('input').value;"
                          style="display: none; position: absolute; bottom: 60px; right: 0; background: #0D1C40; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                        <input type="text" placeholder="Entrez une URL"
                               style="width: 200px; padding: 5px; margin-bottom: 5px; border: 1px solid gold; background: #0D1C40; color: gold;">
                        <button type="submit" style="width: 100%; padding: 5px; background: gold; border: none; cursor: pointer;">Charger</button>
                    </form>
                </div>
HTML;
                $response = preg_replace('/<\/body>/', $button . '</body>', $response);
            }

            echo $response;
            exit;

        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo "Erreur: " . $e->getMessage();
            exit;
        }
    }
}

// Page d'accueil si aucune URL n'est spécifiée
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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
    </style>
</head>
<body>
    <div>
        <h1>HMB Tech - Proxy</h1>
        <p>Cliquez sur le bouton 🌐 pour commencer</p>
    </div>
    <div id="hmb-proxy-button" style="position: fixed; bottom: 20px; right: 20px; z-index: 2147483647;">
        <button onclick="document.getElementById('hmb-proxy-form').style.display = document.getElementById('hmb-proxy-form').style.display === 'none' ? 'block' : 'none'"
                style="width: 50px; height: 50px; border-radius: 25px; background: gold; border: none; cursor: pointer; font-size: 24px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
            🌐
        </button>
        <form id="hmb-proxy-form" onsubmit="event.preventDefault(); window.location.href='/proxy.php/' + (this.querySelector('input').value.startsWith('http') ? '' : 'https://') + this.querySelector('input').value;"
              style="display: none; position: absolute; bottom: 60px; right: 0; background: #0D1C40; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
            <input type="text" placeholder="Entrez une URL"
                   style="width: 200px; padding: 5px; margin-bottom: 5px; border: 1px solid gold; background: #0D1C40; color: gold;">
            <button type="submit" style="width: 100%; padding: 5px; background: gold; border: none; cursor: pointer;">Charger</button>
        </form>
    </div>
</body>
</html>