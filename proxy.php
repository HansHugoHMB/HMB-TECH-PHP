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
    try {
        $cacheFile = 'cache.json';
        $cache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
        $cache[$url] = ['timestamp' => time()];
        file_put_contents($cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
    } catch (Exception $e) {
        // Silently fail if cache management fails
    }
}

// Fonction pour suivre les redirections
function followRedirects($url, $maxRedirects = 5) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => $maxRedirects,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'X-Forwarded-For: 104.28.42.1'
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

    return [
        'content' => $response,
        'httpCode' => $httpCode,
        'contentType' => $contentType,
        'error' => $error
    ];
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

            // Récupérer le contenu avec cURL
            $result = followRedirects($targetUrl);

            if ($result['error']) {
                throw new Exception("Erreur cURL: " . $result['error']);
            }

            if ($result['httpCode'] >= 400) {
                throw new Exception("Erreur HTTP " . $result['httpCode']);
            }

            // Définir les en-têtes de réponse
            if ($result['contentType']) {
                header('Content-Type: ' . $result['contentType']);
            }

            // Enregistrer dans le cache
            manageCache($targetUrl);

            // Retourner le contenu
            echo $result['content'];
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
            min-height: 100vh;
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
            display: flex;
            align-items: center;
            justify-content: center;
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
            backdrop-filter: blur(10px);
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
            transition: all 0.3s ease;
        }

        #submitUrl:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        #error {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #ff4444;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            display: none;
            z-index: 1001;
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
    <button id="proxyButton" title="Ouvrir le proxy">🌐</button>
    <form id="urlForm">
        <input type="url" 
               id="urlInput" 
               placeholder="Entrez l'URL à charger... (ex: https://meta.ai)" 
               required 
               pattern="https?://.+"
               title="L'URL doit commencer par http:// ou https://">
        <button type="submit" id="submitUrl">Charger</button>
    </form>
    <div id="error"></div>

    <script>
        const button = document.getElementById('proxyButton');
        const form = document.getElementById('urlForm');
        const input = document.getElementById('urlInput');
        const error = document.getElementById('error');
        let isFormVisible = false;

        function showError(message) {
            error.textContent = message;
            error.style.display = 'block';
            setTimeout(() => {
                error.style.display = 'none';
            }, 5000);
        }

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
            
            try {
                // Valider l'URL
                if (!/^https?:\/\//i.test(url)) {
                    url = 'https://' + url;
                }
                
                new URL(url); // Valider le format de l'URL
                
                // Rediriger vers l'URL via le proxy
                window.location.href = `/proxy.php/${url}`;
            } catch (err) {
                showError('URL invalide. Veuillez entrer une URL valide.');
            }
        });

        // Gestion des erreurs globales
        window.onerror = function(msg, url, line) {
            showError('Une erreur est survenue. Veuillez réessayer.');
            return false;
        };
    </script>
</body>
</html>