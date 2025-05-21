<?php
/**
 * Power Family - Système de Gestion des Présences
 * * @author HansHugoHMB
 * @version 2.0
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Masquer les erreurs en production, mais les logger.
date_default_timezone_set('UTC');

// Redirection si déjà connecté
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
} // Ligne 16 (fin du bloc if)

// Configuration sécurisée
class Config {
    // Token GitHub divisé en deux parties pour plus de sécurité (Note: cette méthode d'obscurcissement offre une sécurité limitée)
    const GITHUB_TOKEN_PART1 = 'ghp_FdhLrRA2VYSXENmPbV5ZtDeFBCAeNc2xp'; // Exemple de token, devrait être gardé secret et hors du code source
    const GITHUB_TOKEN_PART2 = 'MaI'; // Exemple de token
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
            CURLOPT_USERAGENT => 'PHP', // User-Agent est déjà dans $this->headers mais le répéter ici ne nuit pas
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($method === 'PUT' || $method === 'POST' || $method === 'DELETE' || $method === 'PATCH') { // Plus générique pour d'autres méthodes
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                // S'assurer que Content-Type est correct pour JSON si GitHub l'exige spécifiquement pour PUT
                // $this->headers[] = 'Content-Type: application/json'; // Potentiellement à ajouter si requis
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("GitHub API Error: $error for $method $url");
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
            error_log("Failed to get users. HTTP Code: " . ($response['code'] ?? 'N/A'));
            return [];
        }
        
        $content = $response['data']['content'] ?? '';
        $decoded_content = base64_decode($content);
        if ($decoded_content === false) {
            error_log("Failed to decode base64 content for users.json.");
            return [];
        }
        $users_array = json_decode($decoded_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error for users.json: " . json_last_error_msg());
            return []; // Retourner un tableau vide en cas d'erreur de décodage JSON
        }
        return $users_array ?? [];
    }
    
    public function saveUsers($users) {
        $endpoint = "/repos/" . Config::GITHUB_OWNER . "/" . Config::GITHUB_REPO . "/contents/data/users.json";
        
        // Récupérer le SHA du fichier existant
        $current_file_data = $this->makeRequest($endpoint, 'GET');
        $sha = null;
        if ($current_file_data && $current_file_data['code'] === 200 && isset($current_file_data['data']['sha'])) {
            $sha = $current_file_data['data']['sha'];
        } elseif ($current_file_data && $current_file_data['code'] === 404) {
            // Le fichier n'existe pas, c'est une création, SHA n'est pas requis.
            error_log("users.json not found. Will create a new file.");
        } elseif ($current_file_data) {
            error_log("Could not get SHA for users.json. HTTP code: " . $current_file_data['code']);
            // Continuer sans SHA peut causer un échec si le fichier existe, mais si on ne peut pas l'obtenir, c'est un problème.
        } else {
            error_log("Could not get SHA for users.json. Request failed.");
        }
        
        $dataToSave = [
            'message' => 'Update users data - ' . date('Y-m-d H:i:s'),
            'content' => base64_encode(json_encode($users, JSON_PRETTY_PRINT)), // JSON_PRETTY_PRINT pour lisibilité
        ];

        if ($sha) { // N'inclure le SHA que s'il a été obtenu
            $dataToSave['sha'] = $sha;
        }
        
        $response = $this->makeRequest($endpoint, 'PUT', $dataToSave);
        if (!$response) {
            error_log("Failed to save users: request failed.");
            return false;
        }
        if (!($response['code'] === 200 || $response['code'] === 201)) {
            error_log("Failed to save users. HTTP Code: " . $response['code'] . " - Response: " . json_encode($response['data']));
        }
        return $response && ($response['code'] === 200 || $response['code'] === 201); // 200 for update, 201 for creation
    }
    
    public function authenticate($matricule, $password) {
        $users = $this->getUsers();
        
        foreach ($users as $user) {
            if (isset($user['matricule']) && isset($user['password']) &&
                $user['matricule'] === strtoupper($matricule) && 
                password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
    
    public function register($matricule, $email, $password) {
        // Validation des données (le matricule et l'email sont déjà nettoyés/validés en partie avant d'appeler cette fonction)
        $matricule_upper = strtoupper($matricule); // Assurer que le matricule est en majuscules pour la comparaison et le stockage
        if (!SecurityHelper::validateMatricule($matricule_upper) || 
            !SecurityHelper::validateEmail($email) || 
            !SecurityHelper::validatePassword($password)) { // Le mot de passe est déjà trimé à ce stade
            error_log("Registration validation failed: Matricule: $matricule_upper, Email: $email, PW Length: " . strlen($password));
            return false;
        }
        
        $users = $this->getUsers();
        
        // Vérification des doublons
        foreach ($users as $user) {
            if ((isset($user['matricule']) && $user['matricule'] === $matricule_upper) || 
                (isset($user['email']) && $user['email'] === $email)) {
                error_log("Registration failed: Matricule ($matricule_upper) or Email ($email) already exists.");
                return false; // Indique un duplicata
            }
        }
        
        // Création du nouvel utilisateur
        $users[] = [
            'matricule' => $matricule_upper,
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
        $password = $_POST['password']; // Le mot de passe n'est pas trimé ici pour la connexion, ce qui est standard.
        
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
        // **MODIFICATION ICI: trim des mots de passe avant toute opération**
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        if ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas";
        }
        elseif (!SecurityHelper::validateMatricule($matricule)) {
            $error = "Format de matricule invalide (AAA000)";
        }
        elseif (!SecurityHelper::validateEmail($email)) {
            $error = "Adresse email invalide";
        }
        elseif (!SecurityHelper::validatePassword($password)) { // Valide maintenant le mot de passe trimé
            $error = "Le mot de passe doit faire au moins " . Config::PASSWORD_MIN_LENGTH . " caractères";
        }
        elseif ($storage->register($matricule, $email, $password)) { // Passe le mot de passe trimé
            $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        } else {
            // L'erreur "Ce matricule ou cet email est déjà utilisé" peut aussi être retournée par register() si une validation interne échoue.
            // Pour plus de clarté, register() pourrait retourner des messages d'erreur plus spécifiques ou des codes.
            $error = "Inscription échouée. Ce matricule ou cet email est peut-être déjà utilisé, ou les données sont invalides.";
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
            /* Changement pour que les messages soient dans le flux normal en haut du conteneur */
            grid-column: 1 / -1; /* S'étend sur toutes les colonnes */
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px; /* Espace avant les formulaires */
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
            box-sizing: border-box; /* Empêche le padding d'augmenter la largeur totale */
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
                /* Pas besoin de changer la position si elle est déjà dans le flux */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo SecurityHelper::sanitizeInput($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo SecurityHelper::sanitizeInput($success); ?></div>
        <?php endif; ?>
        
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
                           value="<?php echo isset($_POST['matricule']) && isset($_POST['login']) ? SecurityHelper::sanitizeInput($_POST['matricule']) : ''; ?>">
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
                           value="<?php echo isset($_POST['matricule']) && isset($_POST['register']) ? SecurityHelper::sanitizeInput($_POST['matricule']) : ''; ?>">
                </div>
                <div class="input-group">
                    <input type="email" 
                           name="email" 
                           placeholder="Votre adresse email" 
                           required
                           value="<?php echo isset($_POST['email']) && isset($_POST['register']) ? SecurityHelper::sanitizeInput($_POST['email']) : ''; ?>">
                </div>
                <div class="input-group">
                    <input type="password" 
                           name="password" 
                           id="register_password"
                           placeholder="Votre mot de passe" 
                           required 
                           minlength="<?php echo Config::PASSWORD_MIN_LENGTH; ?>">
                    <div class="password-requirements">
                        Minimum <?php echo Config::PASSWORD_MIN_LENGTH; ?> caractères
                    </div>
                </div>
                <div class="input-group">
                    <input type="password" 
                           name="confirm_password" 
                           id="register_confirm_password"
                           placeholder="Confirmez votre mot de passe" 
                           required>
                </div>
                <button type="submit" name="register">S'inscrire</button>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                // Cibler par ID ou des sélecteurs plus spécifiques si les noms sont partagés entre formulaires
                const matriculeInput = registerForm.querySelector('input[name="matricule"]');
                const emailInput = registerForm.querySelector('input[name="email"]');
                const passwordInput = registerForm.querySelector('input[name="password"]#register_password'); // Utiliser l'ID pour spécificité
                const confirmInput = registerForm.querySelector('input[name="confirm_password"]#register_confirm_password'); // Utiliser l'ID

                // S'assurer que les éléments existent avant de lire .value
                const matricule = matriculeInput ? matriculeInput.value.trim().toUpperCase() : "";
                const email = emailInput ? emailInput.value.trim() : "";
                const password = passwordInput ? passwordInput.value : ""; // Ne pas trimmer ici pour que la validation de longueur soit sur la valeur brute
                const confirmPass = confirmInput ? confirmInput.value : "";
                
                let errors = [];
                
                // Validation du matricule
                if (!/^[A-Z]{3}[0-9]{3}$/.test(matricule)) {
                    errors.push("Le matricule doit être au format AAA000.");
                }
                
                // Validation de l'email
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    errors.push("L'adresse email n'est pas valide.");
                }
                
                // Validation du mot de passe (longueur)
                if (password.length < <?php echo Config::PASSWORD_MIN_LENGTH; ?>) {
                    errors.push("Le mot de passe doit faire au moins <?php echo Config::PASSWORD_MIN_LENGTH; ?> caractères.");
                }
                
                // Vérification de la correspondance des mots de passe
                if (password !== confirmPass) {
                    errors.push("Les mots de passe ne correspondent pas.");
                }
                
                if (errors.length > 0) {
                    e.preventDefault(); // Empêche la soumission du formulaire
                    // Afficher les erreurs d'une manière plus conviviale que alert() serait mieux,
                    // par exemple, en injectant les erreurs dans le DOM près des champs concernés ou dans le .message div
                    // Pour l'instant, on garde alert() comme dans le code original.
                    alert(errors.join("\n"));
                }
            });
        }
    });
    </script>
</body>
</html>