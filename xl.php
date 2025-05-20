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
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #0D1C40;
            color: gold;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .search-box {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #0D1C40;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            z-index: 1000;
        }

        .search-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        input[type="text"] {
            flex: 1;
            padding: 12px;
            border-radius: 25px;
            border: 2px solid gold;
            background-color: #152b60;
            color: gold;
            font-size: 16px;
            min-width: 200px;
        }

        input[type="text"]::placeholder {
            color: rgba(255,215,0,0.5);
        }

        button {
            padding: 12px 25px;
            background-color: gold;
            color: #0D1C40;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s, background-color 0.2s;
        }

        button:hover {
            background-color: #FFE566;
            transform: scale(1.05);
        }

        .results {
            margin-top: 100px;
        }

        .card {
            background-color: #152b60;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .card-content {
            display: grid;
            gap: 10px;
        }

        .field {
            border-bottom: 1px solid rgba(255,215,0,0.2);
            padding-bottom: 8px;
        }

        .field-label {
            font-size: 12px;
            color: rgba(255,215,0,0.7);
            margin-bottom: 3px;
        }

        .field-value {
            font-size: 16px;
        }

        .no-results {
            text-align: center;
            margin-top: 40px;
            color: rgba(255,215,0,0.7);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-box">
            <form method="GET" class="search-form">
                <input type="text" 
                       name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Rechercher..." 
                       autocomplete="off">
                <button type="submit">Rechercher</button>
            </form>
        </div>

        <?php if ($search !== ''): ?>
            <div class="results">
                <?php if (count($filtered) > 1): ?>
                    <?php for ($i = 1; $i < count($filtered); $i++): ?>
                        <div class="card">
                            <div class="card-content">
                                <?php for ($j = 0; $j < count($filtered[$i]); $j++): ?>
                                    <div class="field">
                                        <div class="field-label"><?= htmlspecialchars($filtered[0][$j]) ?></div>
                                        <div class="field-value"><?= htmlspecialchars($filtered[$i][$j]) ?></div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                <?php else: ?>
                    <div class="no-results">
                        Aucun résultat trouvé pour "<?= htmlspecialchars($search) ?>"
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>