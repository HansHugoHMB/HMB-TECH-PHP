<?php
header("Content-Type: text/html; charset=UTF-8");
session_start();

// Connexion DB
$host = 'localhost';
$dbname = 'nom_de_ta_db'; // Remplace par ta DB
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

// Traitement formulaire
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt->execute([$username, $hash])) {
            $message = "Inscription réussie. Veuillez vous connecter.";
        } else {
            $message = "Nom déjà utilisé.";
        }
    }

    if ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $username;
        } else {
            $message = "Identifiants incorrects.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Authentification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <style>
        body {
            margin: 0;
            background-color: #0D1C40;
            font-family: Arial, sans-serif;
            color: white;
        }
        #popup {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .form-box {
            background: white;
            color: #0D1C40;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 300px;
            box-shadow: 0 0 10px black;
        }
        .form-box h3 {
            text-align: center;
            margin-top: 0;
        }
        .form-box input, .form-box button {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-size: 15px;
        }
        .form-box button {
            background-color: #0D1C40;
            color: white;
            border: none;
            cursor: pointer;
        }
        .message {
            text-align: center;
            font-size: 14px;
            color: red;
        }
        #main-content {
            padding: 20px;
            display: none;
        }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['user'])): ?>
<div id="popup">
    <div class="form-box">
        <h3>Connexion / Inscription</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="action" value="login">Se connecter</button>
            <button type="submit" name="action" value="register">S'inscrire</button>
        </form>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    </div>
</div>
<?php else: ?>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        document.getElementById('popup')?.remove();
        document.getElementById('main-content').style.display = 'block';
    });
</script>
<?php endif; ?>

<!-- Contenu principal (visible après connexion) -->
<div id="main-content">
    <h1>Bienvenue, <?= htmlspecialchars($_SESSION['user']) ?> !</h1>
    <p>Voici le contenu de ton site web sécurisé.</p>
    <ul>
        <li>Dashboard</li>
        <li>Produits</li>
        <li>Paramètres</li>
    </ul>
    <form method="POST">
        <button name="action" value="logout">Se déconnecter</button>
    </form>
</div>

<?php
// Déconnexion
if ($_POST['action'] ?? '' === 'logout') {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

</body>
</html>