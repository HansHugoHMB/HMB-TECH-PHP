<?php
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Diviser les clés PayPal en deux parties pour plus de sécurité
$paypalClientId1 = 'AUynoLXPmJ1DL0ImI8';
$paypalClientId2 = 'wgfHsvC6cSRckvBCk01jh3PUqAVwri1ssFvYXhQCN-a-U-4h40YWPiHBNTlpvA';

$paypalSecret1 = 'EE1jwfq7Hu9cReKQMj-Ci6U';
$paypalSecret2 = 'r0svoD7VkpIiq2bYCeUppz8B46t23V25HUKSQIMA1U75MXGMG34Ka4q7Y';

// Reconstituer les clés
$paypalClientId = $paypalClientId1 . $paypalClientId2;
$paypalSecret = $paypalSecret1 . $paypalSecret2;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if ($email) {
        // Enregistrement dans le CSV
        $file = fopen("subscribers.csv", "a");
        fputcsv($file, [$email]);
        fclose($file);

        // Envoi du mail de confirmation
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'hmb05092006@gmail.com';
            $mail->Password = 'z u o p m w n k i e e m d g x y';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('hmb05092006@gmail.com', 'Hans Mbaya Newsletter');
            $mail->addAddress($email);
            $mail->Subject = "Confirmation d'abonnement";
            $mail->Body = "Merci pour ton inscription à la newsletter de Hans Mbaya !";

            $mail->send();
            echo "Merci ! Tu recevras bientôt nos nouvelles.";
        } catch (Exception $e) {
            echo "Erreur d'envoi : {$mail->ErrorInfo}";
        }
    } else {
        echo "Adresse email invalide.";
    }
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

        .payment-section {
            margin-bottom: 20px;
            text-align: center;
        }

        .price-tag {
            background: rgba(255, 215, 0, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="newsletter-card">
        <h2 class="newsletter-title">
            <svg viewBox="0 0 24 24">
                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
            </svg>
            NEWSLETTER PRO
        </h2>

        <div class="payment-section">
            <div class="price-tag">
                <span style="color: var(--accent-color); font-weight: bold;">Abonnement Premium : 5$ / mois</span>
            </div>
            <div id="paypal-button-container"></div>
        </div>

        <form id="newsletter-form" action="https://docs.google.com/forms/d/e/1FAIpQLSexMU03MDvDv-G1ddC1aBaC9OvUueJ92WvFuTKBh3XMGvRxsQ/formResponse" method="POST" style="display: none;">
            <div class="checkbox-group">
                <input type="checkbox" id="daily" name="entry.1889021612" value="Hebdomadaire" class="checkbox-input" checked>
                <label class="checkbox-wrapper" for="daily">
                    <div class="round-checkbox">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 12l5 5L20 4"/>
                        </svg>
                    </div>
                    Tous les jours
                </label>

                <input type="checkbox" id="weekly" name="entry.1185604264" value="Quotidien" class="checkbox-input" checked>
                <label class="checkbox-wrapper" for="weekly">
                    <div class="round-checkbox">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 12l5 5L20 4"/>
                        </svg>
                    </div>
                    Hebdo
                </label>
            </div>

            <input type="email" class="email-input" id="email" name="entry.1899983470" placeholder="Votre adresse e-mail" required>
            <button type="submit" class="submit-btn" id="submit-button">S'abonner</button>
        </form>
    </div>

    <div class="copyright">
        © 2025 HMB-TECH –·– TOUS DROITS RÉSERVÉS
    </div>

    <!-- PayPal Script -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypalClientId; ?>&currency=USD"></script>
    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '5.00'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Afficher le formulaire après paiement réussi
                    document.getElementById('newsletter-form').style.display = 'block';
                    
                    // Masquer les boutons PayPal
                    document.querySelector('.payment-section').style.display = 'none';
                    
                    // Ajouter un message de succès
                    const successMsg = document.createElement('div');
                    successMsg.style.color = 'var(--accent-color)';
                    successMsg.style.textAlign = 'center';
                    successMsg.style.marginBottom = '20px';
                    successMsg.innerHTML = 'Paiement réussi! Vous pouvez maintenant vous inscrire.';
                    document.getElementById('newsletter-form').prepend(successMsg);
                });
            }
        }).render('#paypal-button-container');

        // Form submission handling
        const newsletterForm = document.getElementById('newsletter-form');
        const emailInput = document.getElementById('email');
        const submitButton = document.getElementById('submit-button');

        newsletterForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const originalPlaceholder = emailInput.placeholder;
            let formData = new FormData(this);

            fetch(this.action, {
                method: "POST",
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (!response.ok) {
                    console.error("Erreur lors de l'envoi du formulaire.");
                }
            }).catch(error => {
                console.error("Erreur de connexion.", error);
            });

            emailInput.value = 'Great, succès, *';
            emailInput.style.color = 'var(--accent-color)';
            emailInput.style.fontStyle = 'italic';

            setTimeout(() => {
                emailInput.value = '';
                emailInput.style.color = '';
                emailInput.style.fontStyle = '';
                emailInput.placeholder = originalPlaceholder;
            }, 3000);
        });
    </script>
</body>
</html>