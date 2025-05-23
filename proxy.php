<?php
// Désactiver le rapport d'erreurs pour la production
error_reporting(0);
ini_set('display_errors', 0);

// Configuration des en-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

// Fonction pour gérer le cache
function manageCache($url) {
    $cacheFile = 'cache.json';
    $cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
    $cache[$url] = ['timestamp' => time()];
    file_put_contents($cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
}

// Traitement de la requête proxy
if (isset($_SERVER['PATH_INFO'])) {
    $targetUrl = substr($_SERVER['PATH_INFO'], 1);
    if (!empty($targetUrl)) {
        // Configuration du contexte de la requête
        $opts = [
            'http' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'header' => [
                    'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                    'Accept-Encoding: gzip, deflate, br',
                    'Connection: keep-alive',
                    'Upgrade-Insecure-Requests: 1',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'X-Forwarded-For: 104.28.42.1', // IP américaine
                    'Cache-Control: no-cache',
                ]
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        // Ajouter les en-têtes de la requête originale
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $header => $value) {
                if (!in_array(strtolower($header), ['host', 'connection', 'user-agent'])) {
                    $opts['http']['header'][] = "$header: $value";
                }
            }
        }

        $context = stream_context_create($opts);

        try {
            // Récupérer le contenu
            $content = @file_get_contents($targetUrl, false, $context);
            
            if ($content === false) {
                throw new Exception("Erreur lors de la récupération du contenu");
            }

            // Récupérer les en-têtes de réponse
            $responseHeaders = $http_response_header ?? [];
            
            // Transmettre les en-têtes pertinents
            foreach ($responseHeaders as $header) {
                if (!preg_match('/^(Transfer-Encoding|Connection|Keep-Alive|Host)/i', $header)) {
                    header($header);
                }
            }

            // Enregistrer dans le cache
            manageCache($targetUrl);

            // Retourner le contenu
            echo $content;
            exit;
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => $e->getMessage()]);
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
        :root {
            --primary-bg: #0D1C40;
            --primary-color: gold;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: var(--primary-bg);
            color: var(--primary-color);
            font-family: Arial, sans-serif;
        }

        #proxyButton {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: var(--primary-bg);
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        #proxyButton:hover {
            transform: scale(1.1);
        }

        #urlForm {
            position: fixed;
            bottom: 90px;
            right: 20px;
            background: rgba(13, 28, 64, 0.95);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            display: none;
            z-index: 1000;
        }

        #urlInput {
            width: 300px;
            padding: 10px;
            border: 2px solid var(--primary-color);
            border-radius: 4px;
            background: var(--primary-bg);
            color: var(--primary-color);
            font-size: 16px;
            margin-bottom: 10px;
        }

        #urlInput::placeholder {
            color: rgba(255, 215, 0, 0.5);
        }

        #submitUrl {
            background: var(--primary-color);
            color: var(--primary-bg);
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
        }

        #submitUrl:hover {
            opacity: 0.9;
        }

        @media (max-width: 480px) {
            #urlForm {
                right: 10px;
                left: 10px;
                bottom: 80px;
            }
            
            #urlInput {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <button id="proxyButton">🌐</button>
    <form id="urlForm">
        <input type="url" id="urlInput" placeholder="Entrez l'URL à charger..." required>
        <button type="submit" id="submitUrl">Charger</button>
    </form>

    <script>
        const button = document.getElementById('proxyButton');
        const form = document.getElementById('urlForm');
        const input = document.getElementById('urlInput');
        let isFormVisible = false;

        button.addEventListener('click', () => {
            isFormVisible = !isFormVisible;
            form.style.display = isFormVisible ? 'block' : 'none';
            if (isFormVisible) {
                input.focus();
            }
        });

        document.addEventListener('click', (e) => {
            if (isFormVisible && !form.contains(e.target) && e.target !== button) {
                isFormVisible = false;
                form.style.display = 'none';
            }
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            let url = input.value.trim();
            
            // Ajouter le protocole si manquant
            if (!/^https?:\/\//i.test(url)) {
                url = 'https://' + url;
            }

            // Rediriger vers l'URL via le proxy
            window.location.href = `/proxy.php/${url}`;
        });
    </script>
</body>
</html>