<?php
if (isset($_GET['url'])) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: text/html");
    $url = $_GET['url'];
    echo file_get_contents($url);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Navigateur Simulé</title>
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
      width: 60%;
      font-size: 16px;
      border-radius: 5px;
      border: none;
      margin-bottom: 1em;
    }

    .browser-window {
      width: 90%;
      max-width: 1000px;
      background-color: #1e2a5a;
      border-radius: 12px;
      padding: 1em;
      box-shadow: 0 0 10px #000;
    }

    .browser-bar {
      height: 30px;
      background-color: #2b3e6d;
      border-radius: 12px 12px 0 0;
      display: flex;
      align-items: center;
      padding: 0 10px;
      margin-bottom: 1em;
    }

    .browser-bar .dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-right: 8px;
    }

    .dot.red { background-color: #ff5f57; }
    .dot.yellow { background-color: #ffbd2e; }
    .dot.green { background-color: #28c840; }

    #result {
      min-height: 300px;
      color: gold;
    }
  </style>
</head>
<body>

  <input type="text" id="urlInput" placeholder="Colle un lien ici (avec https://)">
  
  <div class="browser-window">
    <div class="browser-bar">
      <div class="dot red"></div>
      <div class="dot yellow"></div>
      <div class="dot green"></div>
    </div>
    <div id="result">Les données vont apparaître ici...</div>
  </div>

  <script>
    const input = document.getElementById("urlInput");
    const result = document.getElementById("result");

    input.addEventListener("keydown", function(e) {
      if (e.key === "Enter") {
        const url = encodeURIComponent(input.value);
        fetch("?url=" + url)
          .then(res => res.text())
          .then(html => {
            result.innerHTML = html;
          })
          .catch(err => {
            result.innerHTML = "Erreur : " + err;
          });
      }
    });
  </script>
</body>
</html>