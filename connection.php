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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
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
            border-radius: 15px;
            width: 90%;
            max-width: 320px;
            color: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }

        .modal-content h2 {
            text-align: center;
            margin-top: 0;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .modal-content input,
        .modal-content button {
            width: 100%;
            padding: 15px;
            margin: 8px 0;
            border-radius: 10px;
            border: none;
            box-sizing: border-box;
            font-size: 16px;
        }

        .modal-content input {
            background-color: #333;
            color: white;
        }

        .modal-content input::placeholder {
            color: #aaa;
        }

        .modal-content button {
            background-color: #1E90FF;
            color: white;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
        }

        .modal-content button:active {
            background-color: #1570CD;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
        }

        .message.error {
            color: #ff4444;
            background-color: rgba(255, 68, 68, 0.1);
        }

        .message.success {
            color: #00C851;
            background-color: rgba(0, 200, 81, 0.1);
        }

        @media (max-height: 600px) {
            .modal-content {
                margin: 20px;
                max-height: 90vh;
                overflow-y: auto;
            }
        }
    </style>
</head>
<body>

<div class="modal" id="loginModal">
    <div class="modal-content">
        <h2>Connexion</h2>
        <form id="loginForm" onsubmit="return handleLogin(event)">
            <input type="email" name="email" placeholder="Adresse e-mail" required autocomplete="email">
            <input type="password" name="password" placeholder="Mot de passe" required autocomplete="current-password">
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
    const modal = document.getElementById('loginModal');
    
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
                modal.style.display = 'none';
            }, 1000);
        }
    })
    .catch(error => {
        messageDiv.textContent = "Une erreur est survenue";
        messageDiv.className = 'message error';
    });

    return false;
}

// Désactiver le zoom sur les inputs pour mobile
document.addEventListener('touchstart', function(event) {
    if (event.touches.length > 1) {
        event.preventDefault();
    }
}, { passive: false });
</script>

</body>
</html>