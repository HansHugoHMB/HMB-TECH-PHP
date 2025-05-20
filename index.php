<?php
require_once __DIR__ . '/lib/SimpleXLSX.php';

$url = 'https://raw.githubusercontent.com/HansHugoHMB/HMB-TECH-PHP/main/php.xlsx';
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

$tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
file_put_contents($tempFile, file_get_contents($url));

$data = [];

if ($xlsx = SimpleXLSX::parse($tempFile)) {
    $rows = $xlsx->rows();
    $headers = $rows[0];
    $data = $rows;

    if ($search !== '') {
        $filteredData = [$headers];
        for ($i = 1; $i < count($rows); $i++) {
            $rowStr = strtolower(implode(' ', $rows[$i]));
            if (strpos($rowStr, $search) !== false) {
                $filteredData[] = $rows[$i];
            }
        }
    } else {
        $filteredData = $data;
    }
} else {
    die('Erreur : ' . SimpleXLSX::parseError());
}

unlink($tempFile);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Affichage Excel</title>
<style>
    body { background-color: #0D1C40; color: gold; font-family: Arial, sans-serif; padding: 20px; }
    input[type="text"] { padding: 8px; width: 300px; border-radius: 5px; border: none; margin-bottom: 20px; font-size: 16px; }
    button { padding: 8px 15px; background-color: gold; color: #0D1C40; border: none; font-weight: bold; cursor: pointer; border-radius: 5px; font-size: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid gold; padding: 10px; text-align: left; }
    th { background-color: #092040; }
    tr:nth-child(even) { background-color: #152b60; }
</style>
</head>
<body>

<h1>Affichage des données Excel</h1>

<form method="GET" action="">
    <input type="text" name="search" placeholder="Rechercher un nom..." value="<?php echo htmlspecialchars($search); ?>" />
    <button type="submit">Rechercher</button>
    <?php if ($search !== ''): ?>
        <button type="button" onclick="window.location='affiche_excel.php'">Réinitialiser</button>
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