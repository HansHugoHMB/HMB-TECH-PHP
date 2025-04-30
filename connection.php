<?php
// Traitement du formulaire
session_start(); 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Identifiants valides
    $valid_email = "test@exemple.com";
    $valid_password = "123456";

    // Vérification de l'email et du mot de passe
    if ($email === $valid_email && $password === $valid_password) {
        $_SESSION['connecte'] = true;
        $_SESSION['email'] = $email;
        
        echo json_encode(['success' => true, 'message' => 'Connexion réussie']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
        exit;
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
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        .modal {
            display: flex;
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
            color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }

        .modal-content h2 {
            text-align: center;
            margin-top: 0;
        }

        .modal-content input,
        .modal-content button {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            border: none;
            box-sizing: border-box;
        }

        .modal-content input {
            background-color: #333;
            color: white;
        }

        .modal-content button {
            background-color: #1E90FF;
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .modal-content button:hover {
            background-color: #1570CD;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .message.error {
            color: #ff4444;
            background-color: rgba(255, 68, 68, 0.1);
        }

        .message.success {
            color: #00C851;
            background-color: rgba(0, 200, 81, 0.1);
        }
    </style>
</head>
<body>

<div class="modal" id="loginModal">
    <div class="modal-content">
        <h2>Connexion</h2>
        <form id="loginForm" onsubmit="return handleLogin(event)">
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <div id="message" class="message"></div>
    </div>
</div>

<script>
function handleLogin(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const messageDiv = document.getElementById('message');
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        messageDiv.textContent = data.message;
        messageDiv.className = 'message ' + (data.success ? 'success' : 'error');
        
        if (data.success) {
            setTimeout(() => {
                window.location.reload(); // ou rediriger vers une autre page
            }, 1500);
        }
    })
    .catch(error => {
        messageDiv.textContent = "Une erreur est survenue";
        messageDiv.className = 'message error';
    });

    return false;
}

// Empêcher la fermeture du modal en cliquant en dehors
document.getElementById('loginModal').addEventListener('click', function(event) {
    event.stopPropagation();
});
</script>

</body>
</html>