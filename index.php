<?php
$heure = date("H:i:s");
$utilisateur = "Papa Hans";
$produits = [
    ["nom" => "Clavier", "prix" => 120],
    ["nom" => "Souris", "prix" => 60],
    ["nom" => "Écran", "prix" => 350],
    ["nom" => "Ordinateur", "prix" => 1000]
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue sur le site de <?php echo $utilisateur; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0D1C49;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #005b96;
        }
        table {
            border-collapse: collapse;
            width: 60%;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #999;
        }
        th {
            background-color: #005b96;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #e6f2ff;
        }
    </style>
</head>
<body>
    <h1>Bonjour <?php echo $utilisateur; ?> !</h1>
    <p>Il est actuellement : <strong><?php echo $heure; ?></strong></p>

    <h2>Liste des produits disponibles :</h2>
    <table>
        <thead>
            <tr>
                <th>Nom du produit</th>
                <th>Prix ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $produit): ?>
                <tr>
                    <td><?php echo $produit["nom"]; ?></td>
                    <td><?php echo $produit["prix"]; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 40px;">Merci de visiter ce site hébergé avec Render !</p>
</body>
</html>