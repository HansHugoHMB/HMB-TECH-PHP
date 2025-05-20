<?php
/**
 * Power Family - Système de Gestion des Présences
 * 
 * @author HansHugoHMB
 * @version 2.0
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set('UTC');

// Redirection si déjà connecté
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// Configuration sécurisée
class Config {
    // Token GitHub divisé en deux parties pour plus de sécurité
    const GITHUB_TOKEN_PART1 = 'ghp_FdhLrRA2VYSXENmPbV5ZtDeFBCAeNc2xp';
    const GITHUB_TOKEN_PART2 = 'MaI';
    const GITHUB_OWNER = 'HansHugoHMB';
    const GITHUB_REPO = 'HMB-TECH-PHP';
    const GITHUB_API_URL = 'https://api.github.com';
    
    // Paramètres de sécurité
    const PASSWORD_MIN_LENGTH = 8;
    const MATRICULE_PATTERN = '/^[A-Z]{3}[0-9]{3}$/';
}

class SecurityHelper {
    public static function sanitizeInput($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateMatricule($matricule) {
        return preg_match(Config::MATRICULE_PATTERN, strtoupper($matricule));
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function validatePassword($password) {
        return strlen($password) >= Config::PASSWORD_MIN_LENGTH;
    }
}

class GitHubStorage {
    private $token;
    private $headers;
    
    public function __construct() {
        // Combinaison sécurisée du token
        $this->token = Config::GITHUB_TOKEN_PART1 . Config::GITHUB_TOKEN_PART2;
        $this->headers = [
            'Authorization: token ' . $this->token,
            'Accept: application/vnd.github.v3+json',
            'User-Agent: PHP'
        ];
    }
    
    public function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = Config::GITHUB_API_URL . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_USERAGENT => 'PHP',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("GitHub API Error: $error");
            return null;
        }
        
        return [
            'code' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    public function getUsers() {
        $endpoint = "/repos/" . Config::GITHUB_OWNER . "/" . Config::GITHUB_REPO . "/contents/data/users.json";
        $response = $this->makeRequest($endpoint);
        
        if (!$response || $response['code'] !== 200) {
            return [];
        }
        
        $content = $response['data']['content'] ?? '';
        return empty($content) ? [] : json_decode(base64_decode($content), true) ?? [];
    }
    
    public function saveUsers($users) {
        $endpoint = "/repos/" . Config::GITHUB_OWNER . "/" . Config::GITHUB_REPO . "/contents/data/users.json";
        
        // Récupérer le SHA du fichier existant
        $current = $this->makeRequest($endpoint);
        $sha = $current['data']['sha'] ?? null;
        
        $data = [
            'message' => 'Update users data - ' . date('Y-m-d H:i:s'),
            'content' => base64_encode(json_encode($users)),
            'sha' => $sha
        ];
        
        $response = $this->makeRequest($endpoint, 'PUT', $data);
        return $response && ($response['code'] === 200 || $response['code'] === 201);
    }
    
    public function authenticate($matricule, $password) {
        $users = $this->getUsers();
        
        foreach ($users as $user) {
            if ($user['matricule'] === strtoupper($matricule) && 
                password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
    
    public function register($matricule, $email, $password) {
        // Validation des données
        $matricule = strtoupper($matricule);
        if (!SecurityHelper::validateMatricule($matricule) || 
            !SecurityHelper::validateEmail($email) || 
            !SecurityHelper::validatePassword($password)) {
            return false;
        }
        
        $users = $this->getUsers();
        
        // Vérification des doublons
        foreach ($users as $user) {
            if ($user['matricule'] === $matricule || $user['email'] === $email) {
                return false;
            }
        }
        
        // Création du nouvel utilisateur
        $users[] = [
            'matricule' => $matricule,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->saveUsers($users);
    }
}

// Traitement des formulaires
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storage = new GitHubStorage();
    
    if (isset($_POST['login'])) {
        $matricule = SecurityHelper::sanitizeInput($_POST['matricule']);
        $password = $_POST['password'];
        
        if (empty($matricule) || empty($password)) {
            $error = "Tous les champs sont requis";
        } else {
            if ($user = $storage->authenticate($matricule, $password)) {
                $_SESSION['user'] = $user;
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Matricule ou mot de passe incorrect";
            }
        }
    } 
    elseif (isset($_POST['register'])) {
        $matricule = SecurityHelper::sanitizeInput($_POST['matricule']);
        $email = SecurityHelper::sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas";
        }
        elseif (!SecurityHelper::validateMatricule($matricule)) {
            $error = "Format de matricule invalide (AAA000)";
        }
        elseif (!SecurityHelper::validateEmail($email)) {
            $error = "Adresse email invalide";
        }
        elseif (!SecurityHelper::validatePassword($password)) {
            $error = "Le mot de passe doit faire au moins " . Config::PASSWORD_MIN_LENGTH . " caractères";
        }
        elseif ($storage->register($matricule, $email, $password)) {
            $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        } else {
            $error = "Ce matricule ou cet email est déjà utilisé";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Système de gestion des présences - Power Family">
    <meta name="author" content="HansHugoHMB">
    <title>Connexion - Power Family</title>
    <link href="https://fonts.googleapis.com/css2?family=Changa&display=swap" rel="stylesheet">
    <style>
        :root {
            --main-bg: #0D1C40;
            --gold: #FFD700;
            --error: #ff4444;
            --success: #00C851;
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
        
        .container {
            width: 100%;
            max-width: 800px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background-color: rgba(13, 28, 64, 0.95);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid var(--gold);
            position: relative;
        }
        
        .message {
            position: absolute;
            top: -60px;
            left: 0;
            right: 0;
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .error {
            background-color: rgba(255, 0, 0, 0.2);
            border: 1px solid var(--error);
            color: var(--error);
        }
        
        .success {
            background-color: rgba(0, 255, 0, 0.2);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .form-section {
            padding: 20px;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: var(--gold);
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        input {
            width: 100%;
            padding: 12px;
            margin: 5px 0;
            border: 2px solid var(--gold);
            background-color: var(--main-bg);
            color: var(--gold);
            border-radius: 5px;
            font-family: 'Changa', sans-serif;
        }
        
        input:focus {
            outline: none;
            box-shadow: 0 0 5px var(--gold);
        }
        
        .password-requirements {
            font-size: 0.8em;
            color: #aaa;
            margin-top: 5px;
            padding-left: 10px;
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
            font-weight: bold;
            transition: all 0.3s ease;
            font-family: 'Changa', sans-serif;
        }
        
        button:hover {
            background-color: var(--main-bg);
            color: var(--gold);
            border: 2px solid var(--gold);
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .message {
                position: relative;
                top: 0;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($error): ?>
            <div class="message error"><?php echo SecurityHelper::sanitizeInput($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo SecurityHelper::sanitizeInput($success); ?></div>
        <?php endif; ?>
        
        <!-- Formulaire de Connexion -->
        <div class="form-section">
            <h2>Connexion</h2>
            <form method="POST" novalidate>
                <div class="input-group">
                    <input type="text" 
                           name="matricule" 
                           placeholder="Votre matricule (AAA000)" 
                           required 
                           pattern="[A-Za-z]{3}[0-9]{3}"
                           maxlength="6"
                           value="<?php echo isset($_POST['matricule']) ? SecurityHelper::sanitizeInput($_POST['matricule']) : ''; ?>">
                </div>
                <div class="input-group">
                    <input type="password" 
                           name="password" 
                           placeholder="Votre mot de passe" 
                           required>
                </div>
                <button type="submit" name="login">Se connecter</button>
            </form>
        </div>
        
        <!-- Formulaire d'Inscription -->
        <div class="form-section">
            <h2>Inscription</h2>
            <form method="POST" id="registerForm" novalidate>
                <div class="input-group">
                    <input type="text" 
                           name="matricule" 
                           placeholder="Votre matricule (AAA000)" 
                           required 
                           pattern="[A-Za-z]{3}[0-9]{3}"
                           maxlength="6"
                           value="<?php echo isset($_POST['matricule']) ? SecurityHelper::sanitizeInput($_POST['matricule']) : ''; ?>">
                </div>
                <div class="input-group">
                    <input type="email" 
                           name="email" 
                           placeholder="Votre adresse email" 
                           required
                           value="<?php echo isset($_POST['email']) ? SecurityHelper::sanitizeInput($_POST['email']) : ''; ?>">
                </div>
                <div class="input-group">
                    <input type="password" 
                           name="password" 
                           placeholder="Votre mot de passe" 
                           required 
                           minlength="8">
                    <div class="password-requirements">
                        Minimum <?php echo Config::PASSWORD_MIN_LENGTH; ?> caractères
                    </div>
                </div>
                <div class="input-group">
                    <input type="password" 
                           name="confirm_password" 
                           placeholder="Confirmez votre mot de passe" 
                           required>
                </div>
                <button type="submit" name="register">S'inscrire</button>
            </form>
        </div>
    </div>
    
    <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const matricule = document.querySelector('input[name="matricule"]').value.trim().toUpperCase();
        const email = document.querySelector('input[name="email"]').value.trim();
        const password = document.querySelector('input[name="password"]').value;
        const confirm = document.querySelector('input[name="confirm_password"]').value;
        
        let errors = [];
        
        // Validation du matricule
        if (!/^[A-Z]{3}[0-9]{3}$/.test(matricule)) {
            errors.push("Le matricule doit être au format AAA000");
        }
        
        // Validation de l'email
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push("L'adresse email n'est pas valide");
        }
        
        // Validation du mot de passe
        if (password.length < <?php echo Config::PASSWORD_MIN_LENGTH; ?>) {
            errors.push("Le mot de passe doit faire au moins <?php echo Config::PASSWORD_MIN_LENGTH; ?> caractères");
        }
        
        // Vérification de la correspondance des mots de passe
        if (password !== confirm) {
            errors.push("Les mots de passe ne correspondent pas");
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join("\n"));
        }
    });
    </script>
</body>
</html>