<?php
// Désactiver le rapport d'erreurs pour la production
error_reporting(0);
ini_set('display_errors', 0);

// Configuration des en-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

// Fonction pour injecter le bouton dans le HTML
function injectButton($html) {
    $buttonHtml = <<<HTML
    <style>
        #hmb-proxy-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: gold;
            color: #0D1C40;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #hmb-proxy-button:hover {
            transform: scale(1.1);
        }
        #hmb-proxy-form {
            position: fixed;
            bottom: 90px;
            right: 20px;
            background: rgba(13, 28, 64, 0.95);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            display: none;
            z-index: 999999;
            backdrop-filter: blur(10px);
        }
        #hmb-proxy-input {
            width: 300px;
            padding: 10px;
            border: 2px solid gold;
            border-radius: 4px;
            background: #0D1C40;
            color: gold;
            font-size: 16px;
            margin-bottom: 10px;
        }
        #hmb-proxy-submit {
            background: gold;
            color: #0D1C40;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s ease;
        }
        @media (max-width: 480px) {
            #hmb-proxy-form {
                right: 10px;
                left: 10px;
                bottom: 80px;
            }
            #hmb-proxy-input {
                width: 100%;
            }
        }
    </style>
    <button id="hmb-proxy-button" title="Ouvrir le proxy">🌐</button>
    <form id="hmb-proxy-form">
        <input type="url" id="hmb-proxy-input" placeholder="Entrez l'URL à charger..." required>
        <button type="submit" id="hmb-proxy-submit">Charger</button>
    </form>
    <script>
        (function() {
            const button = document.getElementById('hmb-proxy-button');
            const form = document.getElementById('hmb-proxy-form');
            const input = document.getElementById('hmb-proxy-input');
            let isFormVisible = false;

            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                isFormVisible = !isFormVisible;
                form.style.display = isFormVisible ? 'block' : 'none';
                if (isFormVisible) input.focus();
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
                if (!/^https?:\\/\\//i.test(url)) {
                    url = 'https://' + url;
                }
                window.location.href = '/proxy.php/' + url;
            });
        })();
    </script>
HTML;

    // Injecter avant la fermeture du body
    return preg_replace('/<\/body>/', $buttonHtml . '</body>', $html);
}

// Fonction pour gérer le cache
function manageCache($url) {
    try {
        $cacheFile = 'cache.json';
        $cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
        $cache[$url] = ['timestamp' => time()];
        file_put_contents($cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
    } catch (Exception $e) {
        // Silently fail if cache management fails
    }
}

// Traitement de la requête proxy
if (isset($_SERVER['PATH_INFO'])) {
    $targetUrl = substr($_SERVER['PATH_INFO'], 1);
    
    if (!empty($targetUrl)) {
        try {
            // Vérifier l'URL
            if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
                throw new Exception("URL invalide");
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $targetUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                    'Accept-Encoding: gzip, deflate, br',
                    'X-Forwarded-For: 104.28.42.1',
                    'Cache-Control: no-cache',
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_ENCODING => '',
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("Erreur cURL: " . $error);
            }

            if ($httpCode >= 400) {
                throw new Exception("Erreur HTTP " . $httpCode);
            }

            // Injecter le bouton si c'est du HTML
            if (stripos($contentType, 'text/html') !== false) {
                $response = injectButton($response);
            }

            // Définir les en-têtes de réponse
            header('Content-Type: ' . $contentType);
            
            // Enregistrer dans le cache
            manageCache($targetUrl);

            // Retourner le contenu
            echo $response;
            exit;

        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
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
        h1 {
            margin-bottom: 30px;
        }
        p {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div>
        <h1>HMB Tech - Proxy</h1>
        <p>Utilisez le bouton 🌐 pour accéder à un site</p>
    </div>
    <button id="hmb-proxy-button" title="Ouvrir le proxy">🌐</button>
    <form id="hmb-proxy-form">
        <input type="url" id="hmb-proxy-input" placeholder="Entrez l'URL à charger..." required>
        <button type="submit" id="hmb-proxy-submit">Charger</button>
    </form>
    <script>
        const button = document.getElementById('hmb-proxy-button');
        const form = document.getElementById('hmb-proxy-form');
        const input = document.getElementById('hmb-proxy-input');
        let isFormVisible = false;

        button.addEventListener('click', () => {
            isFormVisible = !isFormVisible;
            form.style.display = isFormVisible ? 'block' : 'none';
            if (isFormVisible) input.focus();
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
            if (!/^https?:\/\//i.test(url)) {
                url = 'https://' + url;
            }
            window.location.href = '/proxy.php/' + url;
        });
    </script>
</body>
</html>