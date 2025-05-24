<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$search = $_GET['search'] ?? '';
$url = "https://raw.githubusercontent.com/HansHugoHMB/Accueil/main/data.xlsx"; // Remplace par ton vrai lien
$tempFile = 'temp.xlsx';
file_put_contents($tempFile, file_get_contents($url));

$spreadsheet = IOFactory::load($tempFile);
$worksheet = $spreadsheet->getActiveSheet();

$matchedRow = null;
foreach ($worksheet->getRowIterator() as $row) {
    $rowData = [];
    foreach ($row->getCellIterator() as $cell) {
        $rowData[] = $cell->getValue();
    }

    if ($search && in_array($search, $rowData)) {
        $matchedRow = $rowData;
        break;
    }
}

unlink($tempFile);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recherche Excel</title>
    <style>
        body {
            background-color: #0D1C40;
            color: gold;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        input[type="text"] {
            padding: 10px;
            font-size: 16px;
            width: 300px;
            border: 2px solid gold;
            background: transparent;
            color: gold;
        }
        button {
            padding: 10px;
            background-color: gold;
            color: #0D1C40;
            border: none;
            font-weight: bold;
        }
        .result {
            margin-top: 20px;
            border: 2px solid gold;
            padding: 10px;
            border-radius: 10px;
        }
        .cell {
            padding: 5px;
            border-bottom: 1px solid gold;
        }
    </style>
</head>
<body>
    <h1>Recherche dans le fichier Excel</h1>
    <form method="get">
        <input type="text" name="search" placeholder="Entrer un nom..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Rechercher</button>
    </form>

    <?php if ($matchedRow): ?>
        <div class="result">
            <h2>Ligne trouvée :</h2>
            <?php foreach ($matchedRow as $cell): ?>
                <div class="cell"><?= htmlspecialchars($cell) ?></div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($search): ?>
        <div class="result">Aucune correspondance trouvée.</div>
    <?php endif; ?>
</body>
</html>