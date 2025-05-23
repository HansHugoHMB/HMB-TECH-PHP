<?php
// ------- PROXY + CACHE -------

define('CACHE_FILE', __DIR__ . '/cache.json');

function saveCache($url, $content) {
    $cache = [];
    if (file_exists(CACHE_FILE)) {
        $cache = json_decode(file_get_contents(CACHE_FILE), true);
        if (!is_array($cache)) $cache = [];
    }
    $cache[] = [
        "url" => $url,
        "timestamp" => date("c"),
        "content" => base64_encode($content) // encode pour stocker proprement
    ];
    file_put_contents(CACHE_FILE, json_encode($cache, JSON_PRETTY_PRINT));
}

function fetchUrl($url) {
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    return @file_get_contents($url, false, $context);
}

if (isset($_GET['url'])) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: text/html; charset=UTF-8");

    $url = $_GET['url'];

    // Valide URL simple (évite les injections)
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        echo "URL invalide.";
        exit;
    }

    $content = fetchUrl($url);
    if ($content === false) {
        echo "Erreur lors de la récupération.";
        exit;
    }

    saveCache($url, $content);

    // Nettoyage minimal : on extrait body uniquement
    if (preg_match("/<body[^>]*>(.*?)<\/body>/is", $content, $matches)) {
        echo $matches[1];
    } else {
        echo $content;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Navigateur Mobile Proxy</title>
  <style>
    body {
      background-color: #0D1C40;
      color: gold;
      font-family: sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 2em;
    }
    input {
      padding: 10px;
      width: 80%;
      font-size: 16px;
      border-radius: 5px;
      border: none;
      margin-bottom: 1em;
      color: #000;
    }
    .mobile-frame {
      width: 375px;
      height: 667px;
      border-radius: 30px;
      overflow: auto;
      background: #1e2a5a;
      border: 8px solid #444;
      box-shadow: 0 0 20px black;
      padding: 10px;
      color: gold;
    }
  </style>
</head>
<body>
  <input
    type="text"
    id="urlInput"
    placeholder="Colle un lien ici (https://...) puis appuie sur Entrée"
    spellcheck="false"
  />
  <div class="mobile-frame" id="result">Les données vont apparaître ici...</div>

  <script>
    const input = document.getElementById("urlInput");
    const result = document.getElementById("result");

    input.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        const url = input.value.trim();
        if (!url.startsWith("http")) {
          alert("L'URL doit commencer par http:// ou https://");
          return;
        }
        result.innerHTML = "Chargement...";

        fetch("?url=" + encodeURIComponent(url))
          .then((res) => res.text())
          .then((html) => {
            result.innerHTML = html;
            // Scroll en haut après chargement
            result.scrollTop = 0;
          })
          .catch((err) => {
            result.innerHTML = "Erreur : " + err;
          });
      }
    });
  </script>
</body>
</html>