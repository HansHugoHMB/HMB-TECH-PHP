<?php
ob_start(); // Commence la mise en tampon de la sortie

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Power Family - Présences</title>
    <link rel="icon" href="https://raw.githubusercontent.com/HansHugoHMB/Images/main/FAV.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Changa&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background-color: #0C1C41;
            background-image: url('https://github.com/HansHugoHMB/Images/raw/main/ISTA_3.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Changa', sans-serif;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0; /* Remove default body padding */
        }

        .presence-form {
            background-color: rgba(13, 28, 64, 0.95);
            padding: 1rem; /* Further decrease padding to reduce height */
            border-radius: 10px;
            width: 80%; /* Set width to 80% of viewport */
            max-width: 400px; /* Limit maximum width for better readability on larger screens */
            text-align: center;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            border: 2px solid white;
        }

        .code-input {
            width: 80%;
            padding: 10px; /* Decrease padding slightly */
            margin: 10px 0; /* Decrease margin slightly */
            text-align: center;
            font-size: 1.2em;
            border: none;
            border-radius: 5px;
            background: white;
            font-family: 'Changa', sans-serif;
        }

        .submit-btn {
            background-color: #0D1C40;
            color: white;
            border: 2px solid white;
            padding: 8px 20px; /* Decrease padding slightly */
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 10px; /* Decrease margin slightly */
            transition: all 0.3s ease;
            font-family: 'Changa', sans-serif;
        }

        .submit-btn:hover {
            background-color: white;
            color: #0D1C40;
        }

        .name-display {
            margin-top: 10px; /* Decrease margin slightly */
            padding: 10px; /* Decrease padding slightly */
            min-height: 25px; /* Decrease min-height */
            font-weight: bold;
            font-size: 1.2em;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
            font-family: 'Changa', sans-serif;
            display: none; /* Initially hide the name display */
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #0D1C40; /* Set the background color to blue */
            color: white;
            padding: 15px; /* Decrease padding slightly */
            border-radius: 10px;
            text-align: center;
            border: 2px solid white;
            z-index: 2000;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            font-family: 'Changa', sans-serif;
        }

        .popup h3 {
            margin: 0;
            padding-bottom: 6px; /* Decrease padding slightly */
        }
    </style>
</head>
<body>
    <div class="image-container" style="position: fixed; top: 5mm; left: 40%; transform: translateX(-50%); z-index: 9999;">
    <img src="https://raw.githubusercontent.com/HansHugoHMB/Images/main/loading.svg" alt="Loading Image" style="width: 100%; height: auto;">
</div>
    <div class="presence-form">
        <h2>Enregistrement de Présence</h2>
        <input type="password" id="codeInput" class="code-input" placeholder="Entrez votre code">
        <div id="nameDisplay" class="name-display"></div>
        <button id="submitBtn" class="submit-btn">Valider</button>
    </div>

    <div id="popup" class="popup">
        <h3>FÉLICITATIONS !</h3>
        <p>Vous êtes présent pour aujourd'hui.</p>
    </div>
    <script src="https://image-guard-lib.pages.dev/dist/imageGuard.min.js"></script>
    <script>
        function formatName(string) {
            return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
        }

        const students = {
            'AAA000': {nom: "Ibemba", postnom: "Ntembi"},
            'AAA001': {nom: "Kitambwe", postnom: "Mudimbi"},
            'AAA002': {nom: "Mbala", postnom: "Lubaki"},
            'AAA003': {nom: "Mbala", postnom: "Mbimba"},
            'AAA004': {nom: "Mbambina", postnom: "Wazangba"},
            'AAA005': {nom: "Mbandjo", postnom: "Iyele"},
            'AAA006': {nom: "Mbatu", postnom: "Kimwanga"},
            'AAA007': {nom: "Mbaya", postnom: "Baya"},
            'AAA008': {nom: "Mbaya", postnom: "Faustin"},
            'AAA009': {nom: "Mbayo", postnom: "Bononge"},
            'AAA010': {nom: "Mbeku", postnom: "Elekwa"},
            'AAA011': {nom: "Mbele", postnom: "Kalusuti"},
            'AAA012': {nom: "Mbelenga", postnom: "Mbombawande"},
            'AAA013': {nom: "Mbema", postnom: "Zongo"},
            'AAA014': {nom: "Mbemba", postnom: "Kuku"},
            'AAA015': {nom: "Mbengi", postnom: "Mbo"},
            'AAA016': {nom: "Mbimba", postnom: "Mufungizi"},
            'AAA017': {nom: "Mbirize", postnom: "Imani"},
            'AAA018': {nom: "Mbo", postnom: "Mfumu"},
            'AAA019': {nom: "Mbo", postnom: "Wemba"},
            'AAA020': {nom: "Mbobo", postnom: "Lupombo"},
            'AAA021': {nom: "Mboko", postnom: "Ntoko"},
            'AAA022': {nom: "Mbololo", postnom: "Mwimpe"},
            'AAA023': {nom: "Mboma", postnom: "Kalamba"},
            'AAA024': {nom: "Mboma", postnom: "Ndosala"},
            'AAA025': {nom: "Mboma", postnom: "Taubala"},
            'AAA026': {nom: "Mboso", postnom: "Mawete"},
            'AAA027': {nom: "Mbuaki", postnom: "Miezi"},
            'AAA028': {nom: "Mbuba", postnom: "Ndongala"},
            'AAA029': {nom: "Mbuezo", postnom: "Lungeny"},
            'AAA030': {nom: "Mbuka", postnom: "Matuvang"},
            'AAA031': {nom: "Mbuku", postnom: "Kisalu"},
            'AAA032': {nom: "Mbula", postnom: "Mbanza"},
            'AAA033': {nom: "Mbulayi", postnom: "Tshibangu"},
            'AAA034': {nom: "Mbumba", postnom: "Ndembe"},
            'AAA035': {nom: "Mbuya", postnom: "Mujani"},
            'AAA036': {nom: "Mbuyamba", postnom: "Tshiswaka"},
            'AAA037': {nom: "Mbuyi", postnom: "Kole"},
            'AAA038': {nom: "Mbuyi", postnom: "Ndaye"},
            'AAA039': {nom: "Mbwelima", postnom: "Tanga"},
            'AAA040': {nom: "Mbwiti", postnom: "Kuyakana"},
            'AAA041': {nom: "Mekanda", postnom: "Iwoko"},
            'AAA042': {nom: "Menemene", postnom: "Nkosi"},
            'AAA043': {nom: "Menga", postnom: "Mabala"},
            'AAA044': {nom: "Mesa", postnom: "Musalu"},
            'AAA045': {nom: "Mfulungani", postnom: "Nfulameso"},
            'AAA046': {nom: "Miantise", postnom: "Ndoole"},
            'AAA047': {nom: "Mibo", postnom: "Mpewa"},
            'AAA048': {nom: "Mikadi", postnom: "Mikadi"},
            'AAA049': {nom: "Mikobi", postnom: "Belapio"},
            'AAA050': {nom: "Mikuba", postnom: "Alfani"},
            'AAA051': {nom: "Milonga", postnom: "Mudimbanya"},
            'AAA052': {nom: "Milumbu", postnom: "Kabuya"},
            'AAA053': {nom: "Minga", postnom: "Kwete"},
            'AAA054': {nom: "Minga", postnom: "Mbatshi"},
            'AAA055': {nom: "Minga", postnom: "Pongo"},
            'AAA056': {nom: "Mioma", postnom: "Kingombe"},
            'AAA057': {nom: "Miomo", postnom: "Makanda"},
            'AAA058': {nom: "Misengabu", postnom: "Tegra"},
            'AAA059': {nom: "Misiono", postnom: "Mvungu"},
            'AAA060': {nom: "Mitshiabu", postnom: "Ilunga"},
            'AAA061': {nom: "Mitunga", postnom: "Mubiangat"},
            'AAA062': {nom: "Mitungini", postnom: "Luzolo"},
            'AAA063': {nom: "Mobao", postnom: "Mopia"},
            'AAA064': {nom: "Mobonda", postnom: "Boyele"},
            'AAA065': {nom: "Modja", postnom: "Etoti"},
            'AAA066': {nom: "Mogbokula", postnom: "Ndole"},
            'AAA067': {nom: "Mohele", postnom: "Matuka"},
            'AAA068': {nom: "Mokie", postnom: "Inkini"},
            'AAA069': {nom: "Mokili", postnom: "Katshulu"},
            'AAA070': {nom: "Mokosi", postnom: "Keta"},
            'AAA071': {nom: "Mokoyo", postnom: "Bokianga"},
            'AAA072': {nom: "Mokuba", postnom: "Bapele"},
            'AAA073': {nom: "Mokutu", postnom: "Golo"},
            'AAA074': {nom: "Molithio", postnom: "Ndopo"},
            'AAA075': {nom: "Momele", postnom: "Bokila"},
            'AAA076': {nom: "Monga", postnom: "Mokesa"},
            'AAA077': {nom: "Mongbani", postnom: "Akosu"},
            'AAA078': {nom: "Monsengo", postnom: "Mafisango"},
            'AAA079': {nom: "Monsengo", postnom: "Ntwaboy"},
            'AAA080': {nom: "Moongo", postnom: "Bompusa"},
            'AAA081': {nom: "Mopayi", postnom: "Bokungu"},
            'AAA082': {nom: "Mopia", postnom: "Benie"},
            'AAA083': {nom: "Mopini", postnom: "Ngobila"},
            'AAA084': {nom: "Mopolo", postnom: "Lengo"},
            'AAA085': {nom: "Motokwa", postnom: "Dido"},
            'AAA086': {nom: "Moutsiekou", postnom: "Pandi"},
            'AAA087': {nom: "Moweni", postnom: "Bopopi"},
            'AAA088': {nom: "Mpamaleo", postnom: "Bosontia"},
            'AAA089': {nom: "Mpana", postnom: "Ngambieli"},
            'AAA090': {nom: "Mpata", postnom: "Litumbe"},
            'AAA091': {nom: "Mpay", postnom: "Nke"},
            'AAA092': {nom: "Mpela", postnom: "Bawesi"},
            'AAA093': {nom: "Mpembe", postnom: "Bassa"},
            'AAA094': {nom: "Mpengele", postnom: "Pongo"},
            'AAA095': {nom: "Mpetshi", postnom: "Bangongo"},
            'AAA096': {nom: "Mpeya", postnom: "Lingongo"},
            'AAA097': {nom: "Mwangani", postnom: "Katimuka"},
            'AAA098': {nom: "Noyo", postnom: "Mukendi"}
        };

        function validateAndSubmit() {
            const code = document.getElementById('codeInput').value;
            const nameDisplay = document.getElementById('nameDisplay');
            const popup = document.getElementById('popup');

            if (students[code]) {
                const student = students[code];
                const displayName = `${formatName(student.nom)} ${formatName(student.postnom)}`;
                nameDisplay.textContent = displayName;
                nameDisplay.style.display = 'block'; // Show the name

                // Envoyer à Google Forms
                const formData = new FormData();
                formData.append('entry.620452725', formatName(student.nom)); // ID for nom from the provided link
                formData.append('entry.1336191696', formatName(student.postnom)); // ID for postnom from the provided link

                fetch('https://docs.google.com/forms/d/e/1FAIpQLScDkzgykAv1EiCLqv9cTzGK7uCAFfgKG27ZMwrZcGfuEXynaA/formResponse', { // Updated Google Forms link (using the base URL)
                    method: 'POST',
                    body: formData,
                    mode: 'no-cors'
                });

                // Afficher le popup
                setTimeout(() => {
                    popup.style.display = 'block';

                    // Cacher le popup et réinitialiser
                    setTimeout(() => {
                        popup.style.display = 'none';
                        document.getElementById('codeInput').value = '';
                        nameDisplay.textContent = '';
                        nameDisplay.style.display = 'none'; // Hide the name again
                    }, 3000);
                }, 2000);
            } else {
                nameDisplay.textContent = 'Code invalide';
                nameDisplay.style.color = '#ff4444';
                nameDisplay.style.display = 'block'; // Show the error message
                setTimeout(() => {
                    nameDisplay.style.color = 'white';
                    nameDisplay.style.display = 'none'; // Hide the error message
                }, 2000);
            }
        }

        // Event Listeners
        document.getElementById('submitBtn').addEventListener('click', validateAndSubmit);
        document.getElementById('codeInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                validateAndSubmit();
            }
        });
    </script>


</body>
</html>

<?php
ob_end_flush(); // Envoie le contenu tamponné au navigateur
?>