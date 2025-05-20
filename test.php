<?php
$csvUrl = 'https://raw.githubusercontent.com/HansHugoHMB/d-c/main/data.csv';
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
<meta charset="UTF-8" />
<title>Données CSV</title>
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
        background-color: #152b60;
        color: gold;
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
        margin-top: 10px;
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

<h2>Recherche dans le CSV</h2>
<form method="GET">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tape ta recherche..." />
    <button type="submit">Chercher</button>
    <?php if ($search !== ''): ?>
        <button type="button" onclick="window.location='<?= basename($_SERVER['PHP_SELF']) ?>'">Réinitialiser</button>
    <?php endif; ?>
</form>

<table>
    <thead>
        <tr>
            <?php foreach ($filtered[0] as $header): ?>
                <th><?= htmlspecialchars($header) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php for ($i=1; $i < count($filtered); $i++): ?>
            <tr>
                <?php foreach ($filtered[$i] as $cell): ?>
                    <td><?= htmlspecialchars($cell) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
    </tbody>
</table>

</body>
</html>