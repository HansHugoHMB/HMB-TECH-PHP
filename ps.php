<?php
session_start();

// Configuration initiale
date_default_timezone_set('UTC');

// Tableau des étudiants
$students = array(
    'AAA000' => array('nom' => 'Ibemba', 'postnom' => 'Ntembi'),
    'AAA001' => array('nom' => 'Kitambwe', 'postnom' => 'Mudimbi'),
    'AAA002' => array('nom' => 'Mbala', 'postnom' => 'Lubaki'),
    'AAA003' => array('nom' => 'Mbala', 'postnom' => 'Mbimba'),
    'AAA004' => array('nom' => 'Mbambina', 'postnom' => 'Wazangba'),
    'AAA005' => array('nom' => 'Mbandjo', 'postnom' => 'Iyele'),
    'AAA006' => array('nom' => 'Mbatu', 'postnom' => 'Kimwanga'),
    'AAA007' => array('nom' => 'Mbaya', 'postnom' => 'Baya'),
    'AAA008' => array('nom' => 'Mbaya', 'postnom' => 'Faustin'),
    'AAA009' => array('nom' => 'Mbayo', 'postnom' => 'Bononge'),
    'AAA010' => array('nom' => 'Mbeku', 'postnom' => 'Elekwa'),
    'AAA011' => array('nom' => 'Mbele', 'postnom' => 'Kalusuti'),
    'AAA012' => array('nom' => 'Mbelenga', 'postnom' => 'Mbombawande'),
    'AAA013' => array('nom' => 'Mbema', 'postnom' => 'Zongo'),
    'AAA014' => array('nom' => 'Mbemba', 'postnom' => 'Kuku'),
    'AAA015' => array('nom' => 'Mbengi', 'postnom' => 'Mbo'),
    'AAA016' => array('nom' => 'Mbimba', 'postnom' => 'Mufungizi'),
    'AAA017' => array('nom' => 'Mbirize', 'postnom' => 'Imani'),
    'AAA018' => array('nom' => 'Mbo', 'postnom' => 'Mfumu'),
    'AAA019' => array('nom' => 'Mbo', 'postnom' => 'Wemba'),
    'AAA020' => array('nom' => 'Mbobo', 'postnom' => 'Lupombo'),
    'AAA021' => array('nom' => 'Mboko', 'postnom' => 'Ntoko'),
    'AAA022' => array('nom' => 'Mbololo', 'postnom' => 'Mwimpe'),
    'AAA023' => array('nom' => 'Mboma', 'postnom' => 'Kalamba'),
    'AAA024' => array('nom' => 'Mboma', 'postnom' => 'Ndosala'),
    'AAA025' => array('nom' => 'Mboma', 'postnom' => 'Taubala'),
    'AAA026' => array('nom' => 'Mboso', 'postnom' => 'Mawete'),
    'AAA027' => array('nom' => 'Mbuaki', 'postnom' => 'Miezi'),
    'AAA028' => array('nom' => 'Mbuba', 'postnom' => 'Ndongala'),
    'AAA029' => array('nom' => 'Mbuezo', 'postnom' => 'Lungeny'),
    'AAA030' => array('nom' => 'Mbuka', 'postnom' => 'Matuvang'),
    'AAA031' => array('nom' => 'Mbuku', 'postnom' => 'Kisalu'),
    'AAA032' => array('nom' => 'Mbula', 'postnom' => 'Mbanza'),
    'AAA033' => array('nom' => 'Mbulayi', 'postnom' => 'Tshibangu'),
    'AAA034' => array('nom' => 'Mbumba', 'postnom' => 'Ndembe'),
    'AAA035' => array('nom' => 'Mbuya', 'postnom' => 'Mujani'),
    'AAA036' => array('nom' => 'Mbuyamba', 'postnom' => 'Tshiswaka'),
    'AAA037' => array('nom' => 'Mbuyi', 'postnom' => 'Kole'),
    'AAA038' => array('nom' => 'Mbuyi', 'postnom' => 'Ndaye'),
    'AAA039' => array('nom' => 'Mbwelima', 'postnom' => 'Tanga'),
    'AAA040' => array('nom' => 'Mbwiti', 'postnom' => 'Kuyakana'),
    'AAA041' => array('nom' => 'Mekanda', 'postnom' => 'Iwoko'),
    'AAA042' => array('nom' => 'Menemene', 'postnom' => 'Nkosi'),
    'AAA043' => array('nom' => 'Menga', 'postnom' => 'Mabala'),
    'AAA044' => array('nom' => 'Mesa', 'postnom' => 'Musalu'),
    'AAA045' => array('nom' => 'Mfulungani', 'postnom' => 'Nfulameso'),
    'AAA046' => array('nom' => 'Miantise', 'postnom' => 'Ndoole'),
    'AAA047' => array('nom' => 'Mibo', 'postnom' => 'Mpewa'),
    'AAA048' => array('nom' => 'Mikadi', 'postnom' => 'Mikadi'),
    'AAA049' => array('nom' => 'Mikobi', 'postnom' => 'Belapio'),
    'AAA050' => array('nom' => 'Mikuba', 'postnom' => 'Alfani'),
    'AAA051' => array('nom' => 'Milonga', 'postnom' => 'Mudimbanya'),
    'AAA052' => array('nom' => 'Milumbu', 'postnom' => 'Kabuya'),
    'AAA053' => array('nom' => 'Minga', 'postnom' => 'Kwete'),
    'AAA054' => array('nom' => 'Minga', 'postnom' => 'Mbatshi'),
    'AAA055' => array('nom' => 'Minga', 'postnom' => 'Pongo'),
    'AAA056' => array('nom' => 'Mioma', 'postnom' => 'Kingombe'),
    'AAA057' => array('nom' => 'Miomo', 'postnom' => 'Makanda'),
    'AAA058' => array('nom' => 'Misengabu', 'postnom' => 'Tegra'),
    'AAA059' => array('nom' => 'Misiono', 'postnom' => 'Mvungu'),
    'AAA060' => array('nom' => 'Mitshiabu', 'postnom' => 'Ilunga'),
    'AAA061' => array('nom' => 'Mitunga', 'postnom' => 'Mubiangat'),
    'AAA062' => array('nom' => 'Mitungini', 'postnom' => 'Luzolo'),
    'AAA063' => array('nom' => 'Mobao', 'postnom' => 'Mopia'),
    'AAA064' => array('nom' => 'Mobonda', 'postnom' => 'Boyele'),
    'AAA065' => array('nom' => 'Modja', 'postnom' => 'Etoti'),
    'AAA066' => array('nom' => 'Mogbokula', 'postnom' => 'Ndole'),
    'AAA067' => array('nom' => 'Mohele', 'postnom' => 'Matuka'),
    'AAA068' => array('nom' => 'Mokie', 'postnom' => 'Inkini'),
    'AAA069' => array('nom' => 'Mokili', 'postnom' => 'Katshulu'),
    'AAA070' => array('nom' => 'Mokosi', 'postnom' => 'Keta'),
    'AAA071' => array('nom' => 'Mokoyo', 'postnom' => 'Bokianga'),
    'AAA072' => array('nom' => 'Mokuba', 'postnom' => 'Bapele'),
    'AAA073' => array('nom' => 'Mokutu', 'postnom' => 'Golo'),
    'AAA074' => array('nom' => 'Molithio', 'postnom' => 'Ndopo'),
    'AAA075' => array('nom' => 'Momele', 'postnom' => 'Bokila'),
    'AAA076' => array('nom' => 'Monga', 'postnom' => 'Mokesa'),
    'AAA077' => array('nom' => 'Mongbani', 'postnom' => 'Akosu'),
    'AAA078' => array('nom' => 'Monsengo', 'postnom' => 'Mafisango'),
    'AAA079' => array('nom' => 'Monsengo', 'postnom' => 'Ntwaboy'),
    'AAA080' => array('nom' => 'Moongo', 'postnom' => 'Bompusa'),
    'AAA081' => array('nom' => 'Mopayi', 'postnom' => 'Bokungu'),
    'AAA082' => array('nom' => 'Mopia', 'postnom' => 'Benie'),
    'AAA083' => array('nom' => 'Mopini', 'postnom' => 'Ngobila'),
    'AAA084' => array('nom' => 'Mopolo', 'postnom' => 'Lengo'),
    'AAA085' => array('nom' => 'Motokwa', 'postnom' => 'Dido'),
    'AAA086' => array('nom' => 'Moutsiekou', 'postnom' => 'Pandi'),
    'AAA087' => array('nom' => 'Moweni', 'postnom' => 'Bopopi'),
    'AAA088' => array('nom' => 'Mpamaleo', 'postnom' => 'Bosontia'),
    'AAA089' => array('nom' => 'Mpana', 'postnom' => 'Ngambieli'),
    'AAA090' => array('nom' => 'Mpata', 'postnom' => 'Litumbe'),
    'AAA091' => array('nom' => 'Mpay', 'postnom' => 'Nke'),
    'AAA092' => array('nom' => 'Mpela', 'postnom' => 'Bawesi'),
    'AAA093' => array('nom' => 'Mpembe', 'postnom' => 'Bassa'),
    'AAA094' => array('nom' => 'Mpengele', 'postnom' => 'Pongo'),
    'AAA095' => array('nom' => 'Mpetshi', 'postnom' => 'Bangongo'),
    'AAA096' => array('nom' => 'Mpeya', 'postnom' => 'Lingongo'),
    'AAA097' => array('nom' => 'Mwangani', 'postnom' => 'Katimuka'),
    'AAA098' => array('nom' => 'Noyo', 'postnom' => 'Mukendi')
);

// Traitement de la requête AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = $_POST['code'];
    
    if (isset($students[$code])) {
        $student = $students[$code];
        
        // Enregistrement dans Google Forms
        $formData = array(
            'entry.620452725' => ucfirst(strtolower($student['nom'])),
            'entry.1336191696' => ucfirst(strtolower($student['postnom']))
        );
        
        $ch = curl_init('https://docs.google.com/forms/d/e/1FAIpQLScDkzgykAv1EiCLqv9cTzGK7uCAFfgKG27ZMwrZcGfuEXynaA/formResponse');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        
        echo json_encode(array(
            'status' => 'success',
            'nom' => $student['nom'],
            'postnom' => $student['postnom']
        ));
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Code invalide'
        ));
    }
    exit;
}
?>
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
            padding: 0;
        }

        .presence-form {
            background-color: rgba(13, 28, 64, 0.95);
            padding: 1rem;
            border-radius: 10px;
            width: 80%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            border: 2px solid white;
        }

        .code-input {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
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
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 10px;
            transition: all 0.3s ease;
            font-family: 'Changa', sans-serif;
        }

        .submit-btn:hover {
            background-color: white;
            color: #0D1C40;
        }

        .name-display {
            margin-top: 10px;
            padding: 10px;
            min-height: 25px;
            font-weight: bold;
            font-size: 1.2em;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
            font-family: 'Changa', sans-serif;
            display: none;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #0D1C40;
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid white;
            z-index: 2000;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            font-family: 'Changa', sans-serif;
        }

        .popup h3 {
            margin: 0;
            padding-bottom: 6px;
        }

        .image-container {
            position: fixed;
            top: 5mm;
            left: 40%;
            transform: translateX(-50%);
            z-index: 9999;
        }
    </style>
</head>
<body>
    <div class="image-container">
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

    <script>
        function formatName(string) {
            return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
        }

        function validateAndSubmit() {
            const code = document.getElementById('codeInput').value;
            const nameDisplay = document.getElementById('nameDisplay');
            const popup = document.getElementById('popup');

            const formData = new FormData();
            formData.append('code', code);

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const displayName = `${formatName(data.nom)} ${formatName(data.postnom)}`;
                    nameDisplay.textContent = displayName;
                    nameDisplay.style.display = 'block';
                    
                    setTimeout(() => {
                        popup.style.display = 'block';
                        setTimeout(() => {
                            popup.style.display = 'none';
                            document.getElementById('codeInput').value = '';
                            nameDisplay.textContent = '';
                            nameDisplay.style.display = 'none';
                        }, 3000);
                    }, 2000);
                } else {
                    nameDisplay.textContent = 'Code invalide';
                    nameDisplay.style.color = '#ff4444';
                    nameDisplay.style.display = 'block';
                    setTimeout(() => {
                        nameDisplay.style.color = 'white';
                        nameDisplay.style.display = 'none';
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                nameDisplay.textContent = 'Erreur de connexion';
                nameDisplay.style.color = '#ff4444';
                nameDisplay.style.display = 'block';
            });
        }

        document.getElementById('submitBtn').addEventListener('click', validateAndSubmit);
        document.getElementById('codeInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                validateAndSubmit();
            }
        });
    </script>
</body>
</html>