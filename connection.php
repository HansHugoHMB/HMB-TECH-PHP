<?php
header("Content-Type: text/html; charset=UTF-8");

// --- Connexion DB ---
$host = 'localhost';
$dbname = 'nom_de_ta_db'; // à modifier
$user = 'root';
$pass = '';
session_start();

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

// --- Traitement form ---
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt->execute([$username, $hash])) {
            $message = "Inscription réussie.";
        } else {
            $message = "Erreur d'inscription.";
        }
    }

    if ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $username;
            $message = "Connexion réussie.";
        } else {
            $message = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <style>
        body {
            margin: 0;
            background-color: #0D1C40;
            font-family: 'Arial', sans-serif;
        }
        #popup {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .form-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            width: 90%;
            max-width: 300px;
            box-shadow: 0 0 15px #000;
        }
        .form-box h3 {
            margin: 0;
            text-align: center;
            color: #0D1C40;
        }
        .form-box input, .form-box button {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-size: 16px;
        }
        .form-box button {
            background: #0D1C40;
            color: white;
            border: none;
            cursor: pointer;
        }
        .message {
            text-align: center;
            font-size: 14px;
            margin-top: 5px;
            color: red;
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
    document.body.innerHTML = ""; // efface tout
    alert("Bienvenue <?= $_SESSION['user'] ?> !");
</script>
<?php endif; ?>
</body>
</html>