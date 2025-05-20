<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// URL brute du fichier Excel sur GitHub
$url = 'https://github.com/HansHugoHMB/HMB-TECH-PHP/blob/main/php.xlsx';

// Récupérer la recherche (si soumise)
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// Télécharger le fichier temporairement
$tempFile = tempnam(sys_get_temp_dir(), 'excel_');
file_put_contents($tempFile, file_get_contents($url));

// Charger le fichier Excel
$spreadsheet = IOFactory::load($tempFile);
$sheet = $spreadsheet->getActiveSheet();

// Récupérer toutes les données dans un tableau
$data = [];
foreach ($sheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $rowData = [];
    foreach ($cellIterator as $cell) {
        $rowData[] = $cell->getValue();
    }
    $data[] = $rowData;
}

unlink($tempFile); // Supprimer fichier temporaire

// Si recherche, filtrer les données sauf la 1ère ligne (en-têtes)
if ($search !== '') {
    $filteredData = [];
    $filteredData[] = $data[0]; // garder les en-têtes
    for ($i = 1; $i < count($data); $i++) {
        $rowStr = strtolower(implode(' ', $data[$i]));
        if (strpos($rowStr, $search) !== false) {
            $filteredData[] = $data[$i];
        }
    }
} else {
    $filteredData = $data;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Affichage Excel</title>
<style>
    body {
        background-color: #0D1C40;
        color: gold;
        font-family: Arial, sans-serif;
        padding: 20px;
    }
    input[type="text"] {
        padding: 8px;
        width: 300px;
        border-radius: 5px;
        border: none;
        margin-bottom: 20px;
        font-size: 16px;
    }
    button {
        padding: 8px 15px;
        background-color: gold;
        color: #0D1C40;
        border: none;
        font-weight: bold;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid gold;
        padding: 10px;
        text-align: left;
    }
    th {
        background-color: #092040;
    }
    tr:nth-child(even) {
        background-color: #152b60;
    }
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
            <?php
            // Affiche la 1ère ligne comme en-têtes
            foreach ($filteredData[0] as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        // Affiche les lignes restantes
        for ($i = 1; $i < count($filteredData); $i++) {
            echo '<tr>';
            foreach ($filteredData[$i] as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
        ?>
    </tbody>
</table>

</body>
</html>