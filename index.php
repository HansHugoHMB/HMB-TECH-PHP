<?php
$csvUrl = 'https://raw.githubusercontent.com/HansHugoHMB/HMB-TECH-PHP/main/php.csv';

$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

$tempFile = tempnam(sys_get_temp_dir(), 'csv_');
file_put_contents($tempFile, file_get_contents($csvUrl));

$data = [];
if (($handle = fopen($tempFile, 'r')) !== false) {
    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        $data[] = $row;
    }
    fclose($handle);
}
unlink($tempFile);

// Filtrage
$filtered = [];
if ($search !== '') {
    $filtered[] = $data[0];
    for ($i = 1; $i < count($data); $i++) {
        if (strpos(strtolower(implode(' ', $data[$i])), $search) !== false) {
            $filtered[] = $data[$i];
        }
    }
} else {
    $filtered = $data;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Données CSV</title>
</head>
<body>
<h2>Recherche</h2>
<form method="GET">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Chercher</button>
</form>

<table border="1" cellpadding="8">
    <?php foreach ($filtered as $row): ?>
        <tr>
            <?php foreach ($row as $cell): ?>
                <td><?= htmlspecialchars($cell) ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>