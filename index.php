<?php
// URL brute vers ton fichier CSV (hébergé sur GitHub ou autre)
$url = 'https://raw.githubusercontent.com/HansHugoHMB/HMB-TECH-PHP/main/data.csv';

// Récupère la recherche si elle existe
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// Télécharge le fichier temporairement
$tempFile = tempnam(sys_get_temp_dir(), 'csv_');
file_put_contents($tempFile, file_get_contents($url));

// Lecture du CSV
$data = array_map('str_getcsv', file($tempFile));
unlink($tempFile); // supprime le fichier temporaire

// Filtrage si recherche
if ($search !== '') {
    $filteredData = [$data[0]]; // garder l'en-tête
    foreach (array_slice($data, 1) as $row) {
        if (strpos(strtolower(implode(' ', $row)), $search) !== false) {
            $filteredData[] = $row;
        }
    }
} else {
    $filteredData = $data;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Données CSV</title>
<style>
    body { background-color: #0D1C40; color: gold; font-family: Arial; padding: 20px; }
    input { padding: 8px; width: 300px; border-radius: 5px; font-size: 16px; }
    button { padding: 8px 15px; background: gold; color: #0D1C40; border: none; font-weight: bold; cursor: pointer; border-radius: 5px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid gold; padding: 10px; text-align: left; }
    th { background-color: #092040; }
    tr:nth-child(even) { background-color: #152b60; }
</style>
</head>
<body>

<h1>Affichage CSV</h1>

<form method="GET" action="">
    <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Rechercher</button>
    <?php if ($search !== ''): ?>
        <button type="button" onclick="window.location='analyse_csv.php'">Réinitialiser</button>
    <?php endif; ?>
</form>

<table>
    <thead>
        <tr>
            <?php foreach ($filteredData[0] as $header): ?>
                <th><?php echo htmlspecialchars($header); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php for ($i = 1; $i < count($filteredData); $i++): ?>
            <tr>
                <?php foreach ($filteredData[$i] as $cell): ?>
                    <td><?php echo htmlspecialchars($cell); ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
    </tbody>
</table>

</body>
</html>