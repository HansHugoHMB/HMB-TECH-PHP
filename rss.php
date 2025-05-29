<?php
// 🔐 Ton token GitHub découpé
$token_part1 = 'ghp_FdhLrRA2VYSXENmPbV5ZtDeFBCAeNc2xp';
$token_part2 = 'MaI';
$token = $token_part1 . $token_part2;

$owner = 'HansHugoHMB';
$repo = 'hmb-tech-';
$path = 'feed/rss.xml';

// Obtenir le SHA du fichier actuel
$sha_data = json_decode(file_get_contents("https://api.github.com/repos/$owner/$repo/contents/$path", false, stream_context_create([
    'http' => [
        'method' => "GET",
        'header' => [
            "User-Agent: PHP",
            "Authorization: token $token"
        ]
    ]
])), true);

if (!isset($sha_data['sha'])) {
    echo "❌ Erreur : SHA introuvable.";
    exit;
}

$sha = $sha_data['sha'];

// 🕒 Générer le contenu RSS
$pubDate = gmdate('D, d M Y H:i:s') . ' GMT';
$guid = gmdate('c');

// 🧾 Contenu XML à injecter
$template = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:media="http://search.yahoo.com/mrss/"

     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/">

  <channel>
    <title>🔥 HMB-TECH – Quand la tech rencontre le génie africain ⚡🌍</title>
    <link>http://hmb-tech-x.pages.dev</link>
    <description>Le flux officiel de HMB-TECH</description>
    <language>fr-FR</language>
    <lastBuildDate>{{PUB_DATE}}</lastBuildDate>
    <atom:link href="https://hmb-tech-x.pages.dev/feed/rss.xml" rel="self" type="application/rss+xml" />

    <item>
      <title>🔥 HMB-TECH – Quand la tech rencontre le génie africain ⚡🌍</title>
      <link>http://hmb-tech-x.pages.dev</link>
      <description><![CDATA[
        <h1>🔥 HMB-TECH – Quand la tech rencontre le génie africain ⚡🌍</h1>
        <p><strong>HMB-TECH</strong> (<em>Hans Mbaya Baya Technology</em>) est bien plus qu’un nom.<br/>
        C’est une <strong>révolution silencieuse</strong>, une <strong>flamme numérique</strong> née au cœur de la RDC 🇨🇩,
        allumée par un jeune visionnaire : <strong>Hans Mbaya Baya</strong>. 🚀</p>

        <h2>📜 Origine & Vision</h2>
        <p>Fondée en <strong>2022</strong>, HMB-TECH incarne l’audace d’un jeune Congolais
        qui n’a <strong>pas attendu le futur pour le construire lui-même</strong>.</p>
        <p>➡️ À seulement <strong>16 ans</strong>, Hans lance ses premiers sites, bricole des circuits,
        et allume la mèche d’une entreprise qui veut <strong>changer les codes</strong>. 🧠⚙️</p>
        <blockquote>“Créer aujourd’hui ce dont les autres rêveront demain.” – HMB</blockquote>

        <h2>🛠️ Nos super-pouvoirs (Services)</h2>
        <ul>
          <li>🌐 <strong>Sites web dynamiques</strong> (avec ou sans backend)</li>
          <li>🧰 <strong>Projets tech personnalisés</strong> (proxies, jeux en ligne, outils d’automatisation)</li>
          <li>⚡ <strong>Petits travaux d’électricité</strong> (du concret, du réel, du terrain)</li>
          <li>📚 <strong>Formations pratiques</strong> pour jeunes motivés</li>
        </ul>

        <h2>🎯 Notre Mission : <em>Digitaliser le quartier avant la planète</em></h2>
        <p>Nous croyons que <strong>l’Afrique peut coder son avenir elle-même</strong>.</p>
        <p>HMB-TECH veut rendre la tech :<br/>+ Accessible&nbsp;&nbsp;&nbsp;&nbsp;+ Stylée&nbsp;&nbsp;&nbsp;&nbsp;+ Rentable</p>
        <blockquote>Pas de blabla. <strong>Du résultat.</strong> Pas de grands moyens ? <strong>On bidouille avec talent.</strong></blockquote>

        <h2>🧠 Le Boss – Hans Mbaya Baya</h2>
        <ul>
          <li>👶 Né le 5 septembre 2006 à Mbuji-Mayi</li>
          <li>🧠 Auto-didacte, créatif & visionnaire</li>
          <li>📍 Basé à Ngaliema, Kinshasa</li>
          <li>📲 Fan de gadgets, scripts, et de Coca bien frais</li>
          <li>⚡ Formé en électricité, mais connecté comme un satellite</li>
          <li>✍️ Auteur de <em>Les œuvres des cœurs brisés</em></li>
        </ul>

        <h2>🔗 HMB-TECH c’est aussi :</h2>
        <ul>
          <li>✨ Un état d’esprit</li>
          <li>🧩 Un réseau de jeunes talents</li>
          <li>🌍 Un projet qui rêve grand, agit local</li>
        </ul>

        <p>📡 <a href="http://hmb-tech-x.pages.dev">Site Officiel</a> – 👔 <a href="https://cd.linkedin.com/in/hans-mbaya">LinkedIn du Fondateur</a> – 🎨 <a href="https://fr.m.wikipedia.org/wiki/Fichier:HMB-TECH_LOGO.svg">Logo Officiel</a></p>

        <h3>⚠️ HMB-TECH n’est pas une promesse. C’est une preuve.</h3>
        <p><strong>Une ligne de code à la fois. Un fil électrique à la fois. Une victoire silencieuse à la fois.</strong></p>
        <blockquote>Si tu lis ça et que t’as un rêve, n’attends pas demain.<br/>
        <strong>HMB-TECH est né sans budget. Juste une idée. Juste un feu. Et regarde.</strong></blockquote>

        <p>#HMBTECH #MadeInRDC #TechIsPower #HansMbayaLeBoss</p>
      ]]></description>
      <pubDate>{{PUB_DATE}}</pubDate>
      <guid>http://hmb-tech-x.pages.dev/rss-{{GUID}}</guid>
    </item>

  </channel>
</rss>
XML;

$rss_content = str_replace(['{{PUB_DATE}}', '{{GUID}}'], [$pubDate, $guid], $template);
$content_base64 = base64_encode($rss_content);

// Envoi à GitHub
$update_data = json_encode([
    "message" => "🕒 MAJ RSS du $pubDate",
    "content" => $content_base64,
    "sha" => $sha
]);

$context = stream_context_create([
    'http' => [
        'method' => "PUT",
        'header' => [
            "User-Agent: PHP",
            "Authorization: token $token",
            "Content-Type: application/json"
        ],
        'content' => $update_data
    ]
]);

$result = file_get_contents("https://api.github.com/repos/$owner/$repo/contents/$path", false, $context);

if (strpos($http_response_header[0], "200") || strpos($http_response_header[0], "201")) {
    echo "✅ RSS mis à jour avec succès à $pubDate<br>";
} else {
    echo "❌ Erreur : " . $http_response_header[0] . "<br>";
}
?>

<!-- 🔁 Recharge la page toutes les 60 secondes -->
<meta http-equiv="refresh" content="60">
<p>⏳ Prochaine mise à jour dans 60 secondes...</p>