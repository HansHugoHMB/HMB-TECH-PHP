<?php
/**
 * Power Family - Système de Gestion des Présences
 * 
 * @author HansHugoHMB
 * @version 2.1
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
    const GITHUB_TOKEN_PART1 = 'ghp_FdhLrRA2VYSXENmPbV5ZtDeFBCAeNc2xp';
    const GITHUB_TOKEN_PART2 = 'MaI';
    const GITHUB_OWNER = 'HansHugoHMB';
    const GITHUB_REPO = 'HMB-TECH-PHP';
    const GITHUB_API_URL = 'https://api.github.com';
    const PASSWORD_MIN_LENGTH = 8;
    const MATRICULE_PATTERN = '/^[A-Z]{3}[0-9]{3}$/';
}

// Le reste des classes SecurityHelper et GitHubStorage reste identique...

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
        
        if (!SecurityHelper::validateMatricule($matricule)) {
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
    <title>Connexion - Power Family</title>
    <link href="https://fonts.googleapis.com/css2?family=Changa&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --main-bg: #0D1C40;
            --gold: #FFD700;
            --error: #ff4444;
            --success: #00C851;
        }
        
        /* Styles existants... */
        
        .password-group {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            padding: 5px;
            background: transparent;
            border: none;
        }
        
        .password-toggle svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: var(--gold);
            stroke-width: 2;
            transition: all 0.3s ease;
        }
        
        .password-toggle:hover svg {
            stroke-width: 2.5;
        }
        
        /* Style pour SweetAlert2 */
        .swal2-popup {
            background-color: rgba(13, 28, 64, 0.95) !important;
            border: 2px solid var(--gold) !important;
            color: var(--gold) !important;
        }
        
        .swal2-title, .swal2-content {
            color: var(--gold) !important;
        }
        
        .swal2-confirm {
            background-color: var(--gold) !important;
            color: var(--main-bg) !important;
        }
        
        .swal2-confirm:hover {
            background-color: var(--main-bg) !important;
            color: var(--gold) !important;
            border: 2px solid var(--gold) !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Messages d'erreur/succès... -->
        
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
                <div class="input-group password-group">
                    <input type="password" 
                           id="loginPassword"
                           name="password" 
                           placeholder="Votre mot de passe" 
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword('loginPassword')" tabindex="-1">
                        <svg viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
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
                <div class="input-group password-group">
                    <input type="password" 
                           id="registerPassword"
                           name="password" 
                           placeholder="Votre mot de passe" 
                           required 
                           minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword('registerPassword')" tabindex="-1">
                        <svg viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                    <div class="password-requirements">
                        Minimum <?php echo Config::PASSWORD_MIN_LENGTH; ?> caractères
                    </div>
                </div>
                <button type="submit" name="register">S'inscrire</button>
            </form>
        </div>
    </div>
    
    <script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const svg = button.querySelector('svg');
        
        if (input.type === 'password') {
            input.type = 'text';
            svg.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            `;
        } else {
            input.type = 'password';
            svg.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            `;
        }
    }

    // Validation avec SweetAlert2
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const matricule = document.querySelector('input[name="matricule"]').value.trim().toUpperCase();
        const email = document.querySelector('input[name="email"]').value.trim();
        const password = document.querySelector('input[name="password"]').value;
        
        let errors = [];
        
        if (!/^[A-Z]{3}[0-9]{3}$/.test(matricule)) {
            errors.push("Le matricule doit être au format AAA000");
        }
        
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push("L'adresse email n'est pas valide");
        }
        
        if (password.length < <?php echo Config::PASSWORD_MIN_LENGTH; ?>) {
            errors.push("Le mot de passe doit faire au moins <?php echo Config::PASSWORD_MIN_LENGTH; ?> caractères");
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Erreur',
                html: errors.join('<br>'),
                icon: 'error',
                confirmButtonText: 'OK',
                background: 'rgba(13, 28, 64, 0.95)',
                color: '#FFD700',
                confirmButtonColor: '#FFD700'
            });
        }
    });
    </script>
</body>
</html>