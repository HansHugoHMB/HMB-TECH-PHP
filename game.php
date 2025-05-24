<?php
session_start();

// File-based game state management
define('GAME_DIR', sys_get_temp_dir() . '/wordgame/');
if (!file_exists(GAME_DIR)) {
    mkdir(GAME_DIR, 0777, true);
}

// Clean up old game files (older than 1 hour)
foreach (glob(GAME_DIR . '*') as $file) {
    if (time() - filemtime($file) > 3600) {
        unlink($file);
    }
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $gameCode = filter_input(INPUT_POST, 'gameCode', FILTER_SANITIZE_STRING);
    $gameFile = GAME_DIR . $gameCode . '.json';
    
    switch ($_POST['action']) {
        case 'join':
            if (!file_exists($gameFile)) {
                $gameData = [
                    'players' => [$_SESSION['player_id'] = uniqid()],
                    'words' => [],
                    'guesses' => [],
                    'currentTurn' => 0,
                    'lastUpdate' => time()
                ];
                file_put_contents($gameFile, json_encode($gameData));
                echo json_encode(['status' => 'waiting', 'playerId' => $_SESSION['player_id']]);
            } else {
                $gameData = json_decode(file_get_contents($gameFile), true);
                if (count($gameData['players']) < 2) {
                    $_SESSION['player_id'] = uniqid();
                    $gameData['players'][] = $_SESSION['player_id'];
                    file_put_contents($gameFile, json_encode($gameData));
                    echo json_encode(['status' => 'ready', 'playerId' => $_SESSION['player_id']]);
                } else {
                    echo json_encode(['status' => 'full']);
                }
            }
            break;

        case 'submitWord':
            if (file_exists($gameFile)) {
                $gameData = json_decode(file_get_contents($gameFile), true);
                $word = strtoupper(filter_input(INPUT_POST, 'word', FILTER_SANITIZE_STRING));
                $playerIndex = array_search($_SESSION['player_id'], $gameData['players']);
                if ($playerIndex !== false && !isset($gameData['words'][$playerIndex])) {
                    $gameData['words'][$playerIndex] = $word;
                    $gameData['guesses'][$playerIndex] = array_fill(0, strlen($word), '_');
                    file_put_contents($gameFile, json_encode($gameData));
                    echo json_encode(['status' => 'success', 'wordLength' => strlen($word)]);
                }
            }
            break;

        case 'guessLetter':
            if (file_exists($gameFile)) {
                $gameData = json_decode(file_get_contents($gameFile), true);
                $letter = strtoupper(filter_input(INPUT_POST, 'letter', FILTER_SANITIZE_STRING));
                $playerIndex = array_search($_SESSION['player_id'], $gameData['players']);
                $opponentIndex = ($playerIndex + 1) % 2;

                if ($playerIndex === $gameData['currentTurn'] && isset($gameData['words'][$opponentIndex])) {
                    $word = $gameData['words'][$opponentIndex];
                    $found = false;
                    for ($i = 0; $i < strlen($word); $i++) {
                        if ($word[$i] === $letter) {
                            $gameData['guesses'][$playerIndex][$i] = $letter;
                            $found = true;
                        }
                    }
                    $gameData['currentTurn'] = ($gameData['currentTurn'] + 1) % 2;
                    $gameData['lastUpdate'] = time();
                    file_put_contents($gameFile, json_encode($gameData));
                    echo json_encode([
                        'status' => 'success',
                        'found' => $found,
                        'guesses' => $gameData['guesses'][$playerIndex],
                        'won' => !in_array('_', $gameData['guesses'][$playerIndex])
                    ]);
                }
            }
            break;

        case 'getState':
            if (file_exists($gameFile)) {
                $gameData = json_decode(file_get_contents($gameFile), true);
                $playerIndex = array_search($_SESSION['player_id'], $gameData['players']);
                echo json_encode([
                    'status' => 'success',
                    'currentTurn' => $gameData['currentTurn'],
                    'isYourTurn' => $playerIndex === $gameData['currentTurn'],
                    'guesses' => $gameData['guesses'][$playerIndex] ?? [],
                    'opponentWordLength' => isset($gameData['words'][($playerIndex + 1) % 2]) ? 
                        strlen($gameData['words'][($playerIndex + 1) % 2]) : 0
                ]);
            }
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Word Guessing Game</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #0D1C49;
        }
        .game-container {
            background: gold;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .keyboard {
            display: grid;
            grid-template-columns: repeat(9, 1fr);
            gap: 5px;
            margin-top: 20px;
        }
        .key {
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .key:disabled {
            background: #ccc;
        }
        .word-display {
            font-size: 2em;
            letter-spacing: 10px;
            margin: 20px 0;
            text-align: center;
        }
        .victory {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: gold;
            padding: 40px;
            border-radius: 20px;
            font-size: 3em;
            display: none;
        }
        #downloadBtn {
            display: none;
            margin-top: 20px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
</head>
<body>
    <div class="game-container" id="gameContainer">
        <div id="joinGame">
            <h2>Join Game</h2>
            <input type="text" id="gameCode" placeholder="Enter game code">
            <button onclick="joinGame()">Join</button>
        </div>

        <div id="wordInput" style="display:none;">
            <h2>Enter your word</h2>
            <input type="text" id="secretWord" placeholder="Enter your secret word">
            <button onclick="submitWord()">Submit</button>
        </div>

        <div id="gamePlay" style="display:none;">
            <h3 id="status"></h3>
            <div class="word-display" id="wordDisplay"></div>
            <div class="keyboard" id="keyboard"></div>
        </div>

        <div class="victory" id="victory">
            GREAT!
            <button id="downloadBtn">Download Victory Screenshot</button>
        </div>
    </div>

    <script>
        let gameCode, playerId;
        const keyboard = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
        
        function joinGame() {
            gameCode = document.getElementById('gameCode').value;
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=join&gameCode=${gameCode}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'waiting') {
                    playerId = data.playerId;
                    alert('Waiting for another player to join...');
                    setTimeout(checkGameState, 1000);
                } else if (data.status === 'ready') {
                    playerId = data.playerId;
                    document.getElementById('joinGame').style.display = 'none';
                    document.getElementById('wordInput').style.display = 'block';
                }
            });
        }

        function submitWord() {
            const word = document.getElementById('secretWord').value.toUpperCase();
            if (!/^[A-Z]+$/.test(word)) {
                alert('Please enter only letters');
                return;
            }
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=submitWord&gameCode=${gameCode}&word=${word}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('wordInput').style.display = 'none';
                    document.getElementById('gamePlay').style.display = 'block';
                    initializeKeyboard();
                    startGame();
                }
            });
        }

        function initializeKeyboard() {
            const keyboardDiv = document.getElementById('keyboard');
            keyboard.forEach(letter => {
                const button = document.createElement('button');
                button.textContent = letter;
                button.className = 'key';
                button.onclick = () => guessLetter(letter);
                keyboardDiv.appendChild(button);
            });
        }

        function guessLetter(letter) {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=guessLetter&gameCode=${gameCode}&letter=${letter}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.querySelector(`button:contains('${letter}')`).disabled = true;
                    updateWordDisplay(data.guesses);
                    if (data.won) {
                        showVictory();
                    }
                }
            });
        }

        function updateWordDisplay(guesses) {
            document.getElementById('wordDisplay').textContent = guesses.join(' ');
        }

        function checkGameState() {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=getState&gameCode=${gameCode}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateWordDisplay(data.guesses);
                    document.getElementById('status').textContent = 
                        data.isYourTurn ? 'Your turn!' : "Opponent's turn";
                    if (data.opponentWordLength > 0) {
                        document.getElementById('joinGame').style.display = 'none';
                        document.getElementById('gamePlay').style.display = 'block';
                    }
                }
                setTimeout(checkGameState, 1000);
            });
        }

        function showVictory() {
            document.getElementById('victory').style.display = 'block';
            document.getElementById('downloadBtn').style.display = 'block';
            document.getElementById('downloadBtn').onclick = () => {
                html2canvas(document.getElementById('gameContainer')).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'victory.png';
                    link.href = canvas.toDataURL();
                    link.click();
                });
            };
        }

        // Helper function for button selector
        HTMLElement.prototype.contains = function(text) {
            return this.textContent === text;
        };
    </script>
</body>
</html>