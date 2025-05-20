<?php
$menu = [
    "ACCEUIL" => "https://d-c-hmb-tech.pages.dev/acceuil",
    "DÉCRYPTEUR & CRYPTEUR" => "https://d-c-hmb-tech.pages.dev",
    "PDF PRINTER" => "https://d-c-hmb-tech.pages.dev/print",
    "CODE SOURCE VIEWERS" => "https://v-s-hmb-tech.pages.dev",
    "FAMILY REGISTER" => "https://d-c-hmb-tech.pages.dev/family%20forms",
    "LIEN SHORTCUT" => "https://d-c-hmb-tech.pages.dev/shortcut",
    "HTML CHIFFRÉ" => "https://d-c-hmb-tech.pages.dev/obst",
    "DÉCHIFFRER HTML" => "https://prepa-h.pages.dev/fuc",
    "PRENDRE RDV" => "https://d-c-hmb-tech.pages.dev/rdv",
    "NEWSLETTER" => "https://d-c-hmb-tech.pages.dev/newsletter",
];

$current = $_GET['page'] ?? array_key_first($menu);
$current = $menu[$current] ?? reset($menu);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Portail HMB</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Underdog&display=swap">
  <style>
    body {
      margin: 0;
      padding-top: 110px;
      font-family: 'Underdog', serif;
      background: #0D1C49 url('https://images.unsplash.com/photo-1553095066-5014bc7b7f2d?q=80&w=2070&auto=format&fit=crop') center/cover fixed;
      color: gold;
    }
    .header {
      position: fixed;
      top: 10px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #0D1C40;
      padding: 15px;
      border-radius: 10px;
      border: 2px solid white;
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 90%;
    }
    .menu {
      position: fixed;
      top: 130px;
      left: 10px;
      background: #0D1C40;
      padding: 20px;
      border: 2px solid white;
      border-radius: 10px;
      z-index: 999;
    }
    .menu a {
      display: block;
      color: gold;
      text-decoration: none;
      margin: 10px 0;
    }
    .menu a:hover {
      text-decoration: underline;
    }
    iframe {
      border: none;
      width: 100%;
      height: 90vh;
      position: relative;
      z-index: 1;
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="https://github.com/HansHugoHMB/Images/blob/main/HMB-TECH%20LOGO.svg?raw=true" alt="HMB Logo" height="60">
    <h2>Portail HMB</h2>
  </div>

  <div class="menu">
    <?php foreach ($menu as $name => $link): ?>
      <a href="?page=<?= urlencode($name) ?>"><?= htmlspecialchars($name) ?></a>
    <?php endforeach; ?>
  </div>

  <iframe src="<?= htmlspecialchars($current) ?>" loading="eager"></iframe>
</body>
</html>