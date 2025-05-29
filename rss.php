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