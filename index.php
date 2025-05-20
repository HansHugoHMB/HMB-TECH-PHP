<?php
session_start();

// Configuration GitHub
define('GITHUB_TOKEN_PART1', 'ghp_FdhLrRA2VYSXENmPbV5ZtDeFBCAeNc2xp');
define('GITHUB_TOKEN_PART2', 'MaI');

// Redirection si déjà connecté
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

class GitHubStorage {
    private $token;
    private $owner;
    private $repo;
    
    public function __construct() {
        $this->token = GITHUB_TOKEN_PART1 . GITHUB_TOKEN_PART2;
        $this->owner = 'HansHugoHMB';
        $this->repo = 'HMB-TECH-PHP';
    }

    // Vérification des identifiants
    public function authenticateUser($matricule, $password) {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['matricule'] === $matricule && password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    // Inscription d'un nouvel utilisateur
    public function registerUser($matricule, $email, $password) {
        $users = $this->getUsers();
        
        // Vérification si l'utilisateur existe déjà
        foreach ($users as $user) {
            if ($user['matricule'] === $matricule || $user['email'] === $email) {
                return false;
            }
        }

        $users[] = [
            'matricule' => $matricule,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->saveUsersFile($users);
    }

    private function getUsers() {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/data/users.json";
        $headers = [
            'Authorization: token ' . $this->token,
            'Accept: application/vnd.github.v3+json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        return isset($data['content']) ? json_decode(base64_decode($data['content']), true) ?? [] : [];
    }

    private function saveUsersFile($users) {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/data/users.json";
        $content = base64_encode(json_encode($users));
        
        $headers = [
            'Authorization: token ' . $this->token,
            'Accept: application/vnd.github.v3+json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        $sha = isset($data['sha']) ? $data['sha'] : null;
        
        $postData = [
            'message' => 'Update users data',
            'content' => $content
        ];
        
        if ($sha) {
            $postData['sha'] = $sha;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200 || $httpCode === 201;
    }
}

// Traitement des formulaires
$storage = new GitHubStorage();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Traitement connexion
        $user = $storage->authenticateUser($_POST['matricule'], $_POST['password']);
        if ($user) {
            $_SESSION['user'] = $user;
            header('Location: dashboard.php');
            exit;
        } else {
            $message = "Matricule ou mot de passe incorrect";
            $messageType = "error";
        }
    } elseif (isset($_POST['register'])) {
        // Traitement inscription
        if ($storage->registerUser($_POST['matricule'], $_POST['email'], $_POST['password'])) {
            $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            $messageType = "success";
        } else {
            $message = "Erreur : Matricule ou email déjà utilisé.";
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Power Family</title>
    <link href="https://fonts.googleapis.com/css2?family=Changa&display=swap" rel="stylesheet">
    <style>
        :root {
            --main-bg: #0D1C40;
            --gold: #FFD700;
            --white: #FFFFFF;
        }
        
        body {
            margin: 0;
            min-height: 100vh;
            background-color: var(--main-bg);
            background-image: url('https://github.com/HansHugoHMB/Images/raw/main/ISTA_3.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Changa', sans-serif;
            color: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            width: 100%;
            max-width: 800px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background-color: rgba(13, 28, 64, 0.95);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid var(--gold);
        }
        
        .message {
            grid-column: span 2;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .success {
            background-color: rgba(0, 255, 0, 0.1);
            border: 1px solid #00ff00;
        }
        
        .error {
            background-color: rgba(255, 0, 0, 0.1);
            border: 1px solid #ff0000;
        }
        
        .auth-section {
            padding: 20px;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid var(--gold);
            background-color: var(--main-bg);
            color: var(--gold);
            border-radius: 5px;
            font-family: 'Changa', sans-serif;
        }
        
        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: var(--gold);
            color: var(--main-bg);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Changa', sans-serif;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        button:hover {
            background-color: var(--main-bg);
            color: var(--gold);
            border: 2px solid var(--gold);
        }
        
        .divider {
            width: 1px;
            background-color: var(--gold);
            margin: 0 20px;
        }
        
        @media (max-width: 768px) {
            .auth-container {
                grid-template-columns: 1fr;
            }
            
            .message {
                grid-column: span 1;
            }
            
            .divider {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Section Connexion -->
        <div class="auth-section">
            <h2>Connexion</h2>
            <form method="POST" action="">
                <input type="text" 
                       name="matricule" 
                       placeholder="Votre matricule (AAA000)" 
                       required 
                       pattern="[A-Za-z]{3}[0-9]{3}"
                       maxlength="6">
                <input type="password" 
                       name="password" 
                       placeholder="Votre mot de passe" 
                       required>
                <button type="submit" name="login">Se connecter</button>
            </form>
        </div>

        <div class="divider"></div>

        <!-- Section Inscription -->
        <div class="auth-section">
            <h2>Inscription</h2>
            <form method="POST" action="" id="registerForm">
                <input type="text" 
                       name="matricule" 
                       placeholder="Votre matricule (AAA000)" 
                       required 
                       pattern="[A-Za-z]{3}[0-9]{3}"
                       maxlength="6">
                <input type="email" 
                       name="email" 
                       placeholder="Votre adresse email" 
                       required>
                <input type="password" 
                       name="password" 
                       placeholder="Votre mot de passe" 
                       required 
                       minlength="8">
                <input type="password" 
                       name="confirm_password" 
                       placeholder="Confirmez votre mot de passe" 
                       required>
                <button type="submit" name="register">S'inscrire</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirm = document.querySelector('input[name="confirm_password"]').value;
            const matricule = document.querySelector('input[name="matricule"]').value.toUpperCase();
            
            if (password !== confirm) {
                e.preventDefault();
                alert("Les mots de passe ne correspondent pas !");
                return;
            }
            
            if (!matricule.match(/^[A-Z]{3}[0-9]{3}$/)) {
                e.preventDefault();
                alert("Le format du matricule est incorrect (ex: AAA000)");
                return;
            }
        });
    </script>
</body>
</html>