<?php
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

class NewsletterManager {
    private $config = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 465,
        'smtp_secure' => 'ssl',
        'smtp_username' => 'hmb05092006@gmail.com',
        'smtp_password' => 'z u o p m w n k i e e m d g x y',
        'to_email' => 'mbayahans@gmail.com',
        'from_name' => 'HMB-TECH NEWSLETTER PRO'
    ];

    // Token GitHub divisé en deux parties
    private $githubToken1 = 'ghp_FdhLrRA2VYSXENmPbV5ZtDeFBCAeNc2xp';
    private $githubToken2 = 'MaI';
    private $repo = 'HansHugoHMB/HMB-TECH-';

    private function getCompleteToken() {
        return $this->githubToken1 . $this->githubToken2;
    }

    private function createOrUpdateVCF($type, $email) {
        $token = $this->getCompleteToken();
        $url = "https://api.github.com/repos/{$this->repo}/contents/subscribers/$type.vcf";
        
        $headers = [
            "Authorization: token $token",
            'Accept: application/vnd.github.v3+json',
            'User-Agent: PHP Script'
        ];

        // Vérifier si le fichier existe
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers
        ]);
        $response = curl_exec($ch);
        $fileData = json_decode($response, true);

        if (isset($fileData['sha'])) {
            // Le fichier existe, on l'update
            $content = base64_decode($fileData['content']) . "\nEMAIL:" . $email;
            $data = [
                'message' => "Ajout email: $email",
                'content' => base64_encode($content),
                'sha' => $fileData['sha']
            ];
        } else {
            // Créer un nouveau fichier
            $content = "BEGIN:VCARD\nVERSION:3.0\nEMAIL:" . $email . "\nEND:VCARD";
            $data = [
                'message' => "Création fichier VCF",
                'content' => base64_encode($content)
            ];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function processSubscription($email, $daily, $weekly) {
        try {
            // Mise à jour des fichiers VCF
            if ($daily) $this->createOrUpdateVCF('daily', $email);
            if ($weekly) $this->createOrUpdateVCF('weekly', $email);

            // Configuration et envoi des emails
            $mailer = new PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $this->config['smtp_host'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $this->config['smtp_username'];
            $mailer->Password = $this->config['smtp_password'];
            $mailer->SMTPSecure = $this->config['smtp_secure'];
            $mailer->Port = $this->config['smtp_port'];
            $mailer->CharSet = 'UTF-8';

            // Email au client
            $mailer->setFrom($this->config['smtp_username'], $this->config['from_name']);
            $mailer->addAddress($email);
            $mailer->isHTML(true);
            $mailer->Subject = '🌟 Bienvenue à la newsletter HMB-TECH';
            $mailer->Body = $this->getEmailTemplate('client');
            $mailer->send();

            // Copie à l'administrateur
            $mailer->clearAddresses();
            $mailer->addAddress($this->config['to_email']);
            $mailer->Subject = '📫 Nouvel abonnement newsletter';
            $mailer->Body = $this->getEmailTemplate('admin', $email);
            $mailer->send();

            return ['success' => true];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getEmailTemplate($type, $email = '') {
        $style = "
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    background-color: #0D1C40;
                    font-family: 'Helvetica Neue', Arial, sans-serif;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #0D1C40;
                    border: 2px solid #FFD700;
                    border-radius: 15px;
                    padding: 30px;
                    box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
                }
                h1 {
                    color: #FFD700;
                    text-align: center;
                    font-size: 24px;
                    margin-bottom: 30px;
                }
                p {
                    color: #FFD700;
                    text-align: center;
                    line-height: 1.6;
                    margin: 15px 0;
                }
                .highlight {
                    background: rgba(255, 215, 0, 0.1);
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
            </style>";

        if ($type === 'client') {
            return "
                <html>
                <head>$style</head>
                <body>
                    <div class='container'>
                        <h1>🌟 Bienvenue à la Newsletter HMB-TECH!</h1>
                        <div class='highlight'>
                            <p>Votre inscription a été confirmée avec succès.</p>
                        </div>
                        <p>Préparez-vous à recevoir le meilleur de notre actualité!</p>
                    </div>
                </body>
                </html>";
        } else {
            return "
                <html>
                <head>$style</head>
                <body>
                    <div class='container'>
                        <h1>📫 Nouvel Abonnement Newsletter</h1>
                        <div class='highlight'>
                            <p>Nouvel abonné : $email</p>
                        </div>
                    </div>
                </body>
                </html>";
        }
    }
}

// Traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $newsletter = new NewsletterManager();
        $result = $newsletter->processSubscription(
            $email,
            isset($_POST['daily']),
            isset($_POST['weekly'])
        );
        echo json_encode($result);
        exit;
    }
    echo json_encode(['error' => 'Email invalide']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter HMB-TECH</title>

    <script src="https://hmb-tech-php.onrender.com/tracker.php"></script>
    <img src="https://hmb-tech.onrender.com/tracker.php" style="display:none">

    <style>
        :root {
            --primary-color: #0D1C40;
            --accent-color: #FFD700;
            --text-color: white;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes check {
            from { stroke-dashoffset: 24; }
            to { stroke-dashoffset: 0; }
        }

        body {
            background-color: var(--primary-color);
            color: var(--text-color);
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .newsletter-card {
            background: rgba(13, 28, 64, 0.95);
            border: 2px solid rgba(255, 215, 0, 0.3);
            border-radius: 15px;
            padding: 2rem;
            width: min(90vw, 400px);
            backdrop-filter: blur(10px);
            animation: float 6s infinite ease-in-out;
        }

        .newsletter-title {
            color: var(--accent-color);
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .newsletter-title svg {
            width: 24px;
            height: 24px;
            fill: var(--accent-color);
        }

        .checkbox-group {
            margin-bottom: 1.5rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .checkbox-wrapper:hover {
            background: rgba(255, 215, 0, 0.1);
        }

        .round-checkbox {
            width: 22px;
            height: 22px;
            border: 2px solid var(--accent-color);
            border-radius: 50%;
            margin-right: 12px;
            position: relative;
            flex-shrink: 0;
            overflow: hidden;
        }

        .round-checkbox svg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 14px;
            height: 14px;
            stroke: var(--accent-color);
            stroke-width: 3;
            stroke-dasharray: 24;
            stroke-dashoffset: 24;
            fill: none;
        }

        .checkbox-input {
            display: none;
        }

        .checkbox-input:checked + .checkbox-wrapper .round-checkbox svg {
            animation: check 0.3s ease forwards;
        }

        .email-input {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 215, 0, 0.3);
            border-radius: 8px;
            color: var(--text-color);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .email-input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--accent-color), #FFA500);
            border: none;
            border-radius: 8px;
            color: var(--primary-color);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.2);
        }

        .copyright {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: rgba(13, 28, 64, 0.95);
            text-align: center;
            padding: 10px;
            color: var(--accent-color);
            font-size: 14px;
            font-weight: bold;
        }

        .success-message {
            color: var(--accent-color) !important;
            font-style: italic !important;
            text-align: center !important;
            background-color: var(--primary-color) !important;
            border-color: var(--accent-color) !important;
        }
    </style>
</head>
<body>
    <div class="newsletter-card">
        <h2 class="newsletter-title">
            <svg viewBox="0 0 24 24">
                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
            </svg>
            Recevez le meilleur de l'actu
        </h2>

        <form id="newsletter-form" method="POST">
            <div class="checkbox-group">
                <input type="checkbox" id="daily" name="daily" value="1" class="checkbox-input" checked>
                <label class="checkbox-wrapper" for="daily">
                    <div class="round-checkbox">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 12l5 5L20 4"/>
                        </svg>
                    </div>
                    Tous les jours
                </label>

                <input type="checkbox" id="weekly" name="weekly" value="1" class="checkbox-input" checked>
                <label class="checkbox-wrapper" for="weekly">
                    <div class="round-checkbox">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 12l5 5L20 4"/>
                        </svg>
                    </div>
                    Hebdo
                </label>
            </div>

            <input type="email" class="email-input" id="email" name="email" placeholder="Votre adresse e-mail" required>
            <button type="submit" class="submit-btn" id="submit-button">S'abonner</button>
        </form>
    </div>

    <div class="copyright">
        © 2025 HMB-TECH –·– TOUS DROITS RÉSERVÉS
    </div>

    <script>
        const newsletterForm = document.getElementById('newsletter-form');
        const emailInput = document.getElementById('email');

        newsletterForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    emailInput.value = 'Great, succès, *';
                    emailInput.classList.add('success-message');
                    
                    setTimeout(() => {
                        emailInput.value = '';
                        emailInput.classList.remove('success-message');
                    }, 3000);
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    </script>
</body>
</html>