<?php
session_start();

// Vérification de la connexion
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Configuration GitHub
define('GITHUB_TOKEN_PART1', 'ghp_FdhLrRA2VYSXENmPbV5ZtDeFBCAeNc2xp');
define('GITHUB_TOKEN_PART2', 'MaI');

class GitHubStorage {
    private $token;
    private $owner;
    private $repo;
    
    public function __construct() {
        $this->token = GITHUB_TOKEN_PART1 . GITHUB_TOKEN_PART2;
        $this->owner = 'HansHugoHMB';
        $this->repo = 'HMB-TECH-PHP';
    }
    
    // Gestion des présences
    public function signPresence($matricule) {
        $date = date('Y-m-d');
        $presences = $this->getPresences($date);
        
        // Vérifier si déjà présent
        foreach ($presences as $presence) {
            if ($presence['matricule'] === $matricule) {
                return false;
            }
        }
        
        // Ajouter la présence
        $presences[] = [
            'matricule' => $matricule,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Sauvegarder la présence du jour
        $this->savePresencesFile($date, $presences);
        
        // Mettre à jour l'historique personnel
        $this->updateUserHistory($matricule, $date);
        
        return true;
    }
    
    private function getPresences($date) {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/data/presences/{$date}.json";
        $content = $this->getGitHubFile($url);
        return $content ? json_decode($content, true) : [];
    }
    
    private function savePresencesFile($date, $presences) {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/data/presences/{$date}.json";
        $this->saveGitHubFile($url, $presences);
    }
    
    private function updateUserHistory($matricule, $date) {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/data/history/{$matricule}.json";
        $history = $this->getGitHubFile($url) ?: '[]';
        $history = json_decode($history, true);
        
        $history[] = [
            'date' => $date,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->saveGitHubFile($url, $history);
    }
    
    private function getGitHubFile($url) {
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
        return isset($data['content']) ? base64_decode($data['content']) : null;
    }
    
    private function saveGitHubFile($url, $content) {
        $headers = [
            'Authorization: token ' . $this->token,
            'Accept: application/vnd.github.v3+json'
        ];
        
        // Vérifier si le fichier existe
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        $sha = isset($data['sha']) ? $data['sha'] : null;
        
        $postData = [
            'message' => 'Update data',
            'content' => base64_encode(json_encode($content))
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
        curl_close($ch);
    }
    
    public function getUserHistory($matricule) {
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/data/history/{$matricule}.json";
        $content = $this->getGitHubFile($url);
        return $content ? json_decode($content, true) : [];
    }
    
    public function getAllPresencesToday() {
        return $this->getPresences(date('Y-m-d'));
    }
}

// Traitement des actions
$storage = new GitHubStorage();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign_presence'])) {
    if ($storage->signPresence($_SESSION['user']['matricule'])) {
        $message = "Présence enregistrée avec succès !";
        $messageType = "success";
    } else {
        $message = "Vous avez déjà signé votre présence aujourd'hui.";
        $messageType = "info";
    }
}

// Récupération des données
$todayPresences = $storage->getAllPresencesToday();
$userHistory = $storage->getUserHistory($_SESSION['user']['matricule']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Power Family</title>
    <link href="https://fonts.googleapis.com/css2?family=Changa&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: rgba(13, 28, 64, 0.95);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid var(--gold);
        }
        
        .presence-section {
            background-color: rgba(13, 28, 64, 0.95);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid var(--gold);
            margin-bottom: 20px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .card {
            background-color: rgba(13, 28, 64, 0.95);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid var(--gold);
        }
        
        button {
            padding: 12px 24px;
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
        
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success { background-color: rgba(0, 255, 0, 0.1); }
        .info { background-color: rgba(0, 0, 255, 0.1); }
        
        .presence-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .presence-item {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .logout {
            color: var(--gold);
            text-decoration: none;
            padding: 8px 16px;
            border: 2px solid var(--gold);
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .logout:hover {
            background-color: var(--gold);
            color: var(--main-bg);
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user']['matricule']); ?></h1>
            <a href="?logout=1" class="logout">Déconnexion</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="presence-section">
            <form method="POST" action="">
                <button type="submit" name="sign_presence">
                    Signer la présence
                </button>
            </form>
        </div>
        
        <div class="grid">
            <div class="card">
                <h2>Présences aujourd'hui</h2>
                <div class="presence-list">
                    <?php foreach ($todayPresences as $presence): ?>
                        <div class="presence-item">
                            <?php echo htmlspecialchars($presence['matricule']); ?> - 
                            <?php echo date('H:i', strtotime($presence['timestamp'])); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="card">
                <h2>Mon historique</h2>
                <div class="presence-list">
                    <?php foreach ($userHistory as $record): ?>
                        <div class="presence-item">
                            <?php echo date('d/m/Y', strtotime($record['date'])); ?> - 
                            <?php echo date('H:i', strtotime($record['timestamp'])); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>