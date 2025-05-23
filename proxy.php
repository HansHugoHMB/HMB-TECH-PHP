<?php
// 1. Détection de l'URL à proxyfier depuis l'URL complète
$requestUri = $_SERVER['REQUEST_URI'];
$url = ltrim($requestUri, '/'); // On retire le "/" initial

// 2. Si une URL est passée, on la proxy et l'affiche
if (filter_var($url, FILTER_VALIDATE_URL)) {
    // Fake User-Agent mobile
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_5 like Mac OS X)\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $content = @file_get_contents($url, false, $context);

    if ($content === false) {
        echo "<h1 style='color:red;text-align:center'>Erreur de chargement de : $url</h1>";
        exit;
    }

    // Injecte le bouton flottant dans la page cible
    $inject = <<<HTML
<style>
#proxy-btn {
  position: fixed;
  top: 10px;
  right: 10px;
  background: gold;
  color: black;
  border: none;
  padding: 10px 15px;
  border-radius: 8px;
  font-weight: bold;
  z-index: 9999;
}
#proxy-form {
  display: none;
  position: fixed;
  top: 50px;
  right: 10px;
  background: #0D1C40;
  padding: 10px;
  border-radius: 8px;
  z-index: 9999;
}
#proxy-form input {
  width: 250px;
  padding: 5px;
}
</style>
<button id="proxy-btn">Changer lien</button>
<div id="proxy-form">
  <input type="text" id="url-input" placeholder="https://..." />
</div>
<script>
document.getElementById("proxy-btn").onclick = () => {
  const form = document.getElementById("proxy-form");
  form.style.display = form.style.display === "block" ? "none" : "block";
};
document.getElementById("url-input").addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    const newUrl = e.target.value.trim();
    if (newUrl.startsWith("http")) {
      window.location.href = "/" + newUrl;
    } else {
      alert("Lien invalide. Il doit commencer par http(s)://");
    }
  }
});
</script>
HTML;

    // Injecte le code juste après <body>
    $content = preg_replace("/<body[^>]*>/i", "$0" . $inject, $content, 1);
    echo $content;
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mini Navigateur PHP</title>
  <style>
    body {
      background: #0D1C40;
      color: gold;
      font-family: sans-serif;
      display: flex;
      height: 100vh;
      justify-content: center;
      align-items: center;
      text-align: center;
    }
    input {
      padding: 10px;
      width: 80%;
      max-width: 400px;
      font-size: 18px;
      border-radius: 5px;
      border: none;
    }
  </style>
</head>
<body>
  <div>
    <h2>Colle une URL pour commencer</h2>
    <input id="starter" type="text" placeholder="https://meta.ai" />
  </div>
  <script>
    const starter = document.getElementById("starter");
    starter.addEventListener("keydown", function(e) {
      if (e.key === "Enter") {
        const u = starter.value.trim();
        if (u.startsWith("http")) {
          window.location.href = "/" + u;
        } else {
          alert("Lien invalide. Il doit commencer par http:// ou https://");
        }
      }
    });
  </script>
</body>
</html>