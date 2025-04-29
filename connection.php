<?php
header("Content-Type: application/javascript");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Traitement AJAX depuis JS
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $valid_email = "test@exemple.com";
    $valid_password = "123456";

    if ($email === $valid_email && $password === $valid_password) {
        echo "Connexion réussie ! Bienvenue, $email.";
    } else {
        echo "Identifiants incorrects.";
    }
    exit;
}
?>

// === STYLE POPUP ===
const style = document.createElement('style');
style.textContent = `
#popup-bg {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background-color: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
#popup-box {
    background-color: #0D1C40;
    padding: 20px;
    border-radius: 10px;
    color: white;
    width: 300px;
}
#popup-box input, #popup-box button {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border: none;
    border-radius: 5px;
}
#popup-box button {
    background-color: #1E90FF;
    color: white;
}
#popup-close {
    text-align: right;
    color: red;
    cursor: pointer;
}
`;
document.head.appendChild(style);

// === STRUCTURE POPUP ===
const modal = document.createElement('div');
modal.id = 'popup-bg';
modal.innerHTML = `
  <div id="popup-box">
    <div id="popup-close">X</div>
    <h3>Connexion</h3>
    <form id="loginForm">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Mot de passe" required />
      <button type="submit">Se connecter</button>
      <div id="popup-msg"></div>
    </form>
  </div>
`;
document.body.appendChild(modal);

// === FERMETURE POPUP ===
document.getElementById('popup-close').onclick = () => {
    modal.remove();
};

// === ENVOI FORMULAIRE ===
document.getElementById('loginForm').onsubmit = async function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const res = await fetch("connexion.php", {
        method: "POST",
        body: formData
    });

    const text = await res.text();
    document.getElementById('popup-msg').innerText = text;
};