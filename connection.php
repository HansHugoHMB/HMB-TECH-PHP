<?php
// Traitement du formulaire
$message = '';
$connecte = false;

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Identifiants valides (stockés directement dans le fichier PHP)
    $valid_email = "test@exemple.com";
    $valid_password = "123456";

    // Vérification de l'email et du mot de passe
    if ($email === $valid_email && $password === $valid_password) {
        $connecte = true;
        $message = "Bienvenue, $email !";
    } else {
        $message = "Adresse e-mail ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body {
            background-color: #0D1C40;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
        }

        .btn-open {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            background-color: #1E90FF;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #222;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
        }

        .modal-content h2 {
            text-align: center;
        }

        .modal-content input,
        .modal-content button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }

        .modal-content button {
            background-color: #1E90FF;
            color: white;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            color: yellow;
        }

        .close {
            text-align: right;
            cursor: pointer;
            color: red;
        }

        .content {
            display: none;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>

<?php if ($connecte): ?>
    <!-- Message de bienvenue ou contenu de la page après connexion -->
    <div class="content">
        <h2><?php echo $message; ?></h2>
        <p>Voici votre contenu exclusif après la connexion !</p>
    </div>
<?php else: ?>
    <!-- Interface de connexion -->
    <button class="btn-open" onclick="document.getElementById('loginModal').style.display='flex'">
        Connexion
    </button>

    <div class="modal" id="loginModal">
        <div class="modal-content">
            <div class="close" onclick="document.getElementById('loginModal').style.display='none'">X</div>
            <h2>Connexion</h2>
            <form method="POST" action="">
                <input type="email" name="email" placeholder="Adresse e-mail" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Se connecter</button>
            </form>
            <?php if ($message && !$connecte): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
// Fermer le modal si on clique en dehors
window.onclick = function(event) {
    let modal = document.getElementById('loginModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

// Afficher le contenu après la connexion réussie
<?php if ($connecte): ?>
    document.querySelector('.content').style.display = 'block';
    document.querySelector('.btn-open').style.display = 'none'; // Cacher le bouton de connexion
    document.getElementById('loginModal').style.display = 'none'; // Fermer le modal
<?php endif; ?>
</script>

</body>
</html>