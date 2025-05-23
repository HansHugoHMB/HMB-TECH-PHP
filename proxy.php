<?php
error_reporting(0);
ini_set('display_errors', 0);

// Fonction pour gérer le proxy
function proxyRequest($url) {
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
            'Accept-Encoding: gzip, deflate',
            'X-Forwarded-For: 104.28.42.1'
        ]
    ]);

    $response = curl_exec($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    return ['content' => $response, 'type' => $contentType];
}

// Si c'est une requête AJAX pour charger une URL
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $url = 'https://' . $url;
    }
    try {
        $result = proxyRequest($url);
        header('Content-Type: ' . $result['type']);
        echo $result['content'];
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo "Erreur: " . $e->getMessage();
    }
    exit;
}

// Page principale
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMB Tech - Browser</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0D1C40;
            color: gold;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .browser-container {
            width: 390px;
            height: 844px;
            background: white;
            border-radius: 45px;
            position: relative;
            overflow: hidden;
            border: 3px solid gold;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
        }

        .notch {
            width: 150px;
            height: 30px;
            background: #0D1C40;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
            z-index: 1000;
        }

        .browser-bar {
            position: absolute;
            top: 40px;
            left: 10px;
            right: 10px;
            height: 40px;
            background: rgba(13, 28, 64, 0.9);
            border-radius: 10px;
            display: flex;
            align-items: center;
            padding: 0 10px;
            z-index: 1000;
        }

        .url-input {
            flex: 1;
            height: 30px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid gold;
            border-radius: 5px;
            color: gold;
            padding: 0 10px;
            margin-right: 10px;
        }

        .go-button {
            background: gold;
            color: #0D1C40;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .content-frame {
            position: absolute;
            top: 90px;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            overflow: auto;
        }

        #proxyContent {
            width: 100%;
            height: 100%;
            border: none;
        }

        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #0D1C40;
            font-weight: bold;
            display: none;
        }
    </style>
</head>
<body>
    <div class="browser-container">
        <div class="notch"></div>
        <div class="browser-bar">
            <input type="text" class="url-input" placeholder="Entrez une URL..." value="<?php echo isset($_GET['load']) ? htmlspecialchars($_GET['load']) : ''; ?>">
            <button class="go-button">GO</button>
        </div>
        <div class="content-frame">
            <div id="proxyContent"></div>
            <div class="loading">Chargement...</div>
        </div>
    </div>

    <script>
        const urlInput = document.querySelector('.url-input');
        const goButton = document.querySelector('.go-button');
        const contentDiv = document.getElementById('proxyContent');
        const loading = document.querySelector('.loading');

        async function loadUrl(url) {
            if (!url) return;
            
            if (!url.startsWith('http')) {
                url = 'https://' + url;
            }

            loading.style.display = 'block';
            contentDiv.innerHTML = '';

            try {
                const response = await fetch(`?url=${encodeURIComponent(url)}`);
                const content = await response.text();
                contentDiv.innerHTML = content;
                urlInput.value = url;
                history.pushState({}, '', `?load=${encodeURIComponent(url)}`);
            } catch (error) {
                contentDiv.innerHTML = `<div style="color: red; padding: 20px;">Erreur: ${error.message}</div>`;
            } finally {
                loading.style.display = 'none';
            }
        }

        goButton.addEventListener('click', () => loadUrl(urlInput.value.trim()));
        urlInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                loadUrl(urlInput.value.trim());
            }
        });

        // Charger l'URL initiale si présente
        const initialUrl = new URLSearchParams(window.location.search).get('load');
        if (initialUrl) {
            loadUrl(initialUrl);
        }
    </script>
</body>
</html>