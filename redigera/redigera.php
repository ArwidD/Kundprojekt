<?php
require_once __DIR__ . '/auth.php';

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "utbildning";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("DB-anslutning misslyckades: " . $conn->connect_error);
}

$message = "";
$arskursID = isset($_GET['arskurs']) ? intval($_GET['arskurs']) : 0;

// Hantera POST-förfrågningar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $arskursID > 0) {
    // Ta bort en rad helt
    if (isset($_POST['delete'])) {
        $deleteID = intval($_POST['delete']);
        $stmt = $conn->prepare("DELETE FROM information WHERE ID=?");
        $stmt->bind_param("i", $deleteID);
        $stmt->execute();
        $stmt->close();
        $message = "Raden har raderats!";
    } else {
        // Uppdatera existerande rader (text + kategori)
        if (!empty($_POST['info'])) {
            foreach ($_POST['info'] as $id => $text) {
                $kategori = isset($_POST['kategori'][$id]) ? intval($_POST['kategori'][$id]) : null;

                if ($kategori !== null) {
                    $stmt = $conn->prepare("UPDATE information SET information=?, kategori=? WHERE ID=?");
                    $stmt->bind_param("sii", $text, $kategori, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE information SET information=? WHERE ID=?");
                    $stmt->bind_param("si", $text, $id);
                }

                $stmt->execute();
                $stmt->close();
            }
        }

        // Lägg till nya texter
        if (!empty($_POST['new_info'])) {
            foreach ($_POST['new_info'] as $index => $text) {
                $kategori = isset($_POST['new_kategori'][$index]) ? intval($_POST['new_kategori'][$index]) : null;

                if (trim($text) !== "") {
                    if ($kategori !== null) {
                        $stmt = $conn->prepare("INSERT INTO information (information, `arskurs ID`, kategori) VALUES (?, ?, ?)");
                        $stmt->bind_param("sii", $text, $arskursID, $kategori);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO information (information, `arskurs ID`) VALUES (?, ?)");
                        $stmt->bind_param("si", $text, $arskursID);
                    }

                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $message = "Informationen har uppdaterats!";
    }
}

// Hämta befintliga rader
$infoRows = [];
$arskursNamn = "";
if ($arskursID > 0) {
    $stmt = $conn->prepare("SELECT ID, information, kategori FROM information WHERE `arskurs ID`=?");
    $stmt->bind_param("i", $arskursID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $infoRows[] = $row;
    }
    $stmt->close();

    $res = $conn->query("SELECT Arskurs FROM arskurser WHERE ID=$arskursID");
    if ($res && $res->num_rows > 0) {
        $arskursNamn = $res->fetch_assoc()['Arskurs'];
    }
}
?>
<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redigera årskurs</title>
    <link rel="stylesheet" href="../index.css">
    <style>
        main {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 15px;
        }

        textarea {
            width: 100%;
            max-width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
            font-size: 1rem;
        }

        select {
            width: 100%;
            max-width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .info-block {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 6px;
            background: white;
        }

        .info-block label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .new-info {
            background: #f9f9f9;
            padding: 15px;
            border: 1px dashed #ccc;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .delete-btn {
            background: #d9534f;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.95rem;
            width: 100%;
            margin-top: 10px;
        }

        .delete-btn:hover {
            background: #c9302c;
        }

        h1 {
            font-size: 1.8rem;
            margin: 20px 0;
            color: #333;
        }

        h3 {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #333;
        }

        @media (max-width: 768px) {
            main {
                padding: 0 10px;
            }

            .info-block {
                padding: 12px;
            }

            .new-info {
                padding: 12px;
            }



            h1 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 0 8px;
            }

            .info-block {
                padding: 10px;
                margin-bottom: 15px;
            }

            textarea {
                font-size: 16px;
                padding: 8px;
            }

            select {
                font-size: 16px;
                padding: 8px;
            }

            .delete-btn {
                padding: 8px 12px;
                font-size: 0.9rem;
            }

            h1 {
                font-size: 1.3rem;
            }

            h3 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-area">
            <div class="site-name">Utbildning.ax</div>
            <div class="site-slogan">Pedagogisk resurs på Åland</div>
        </div>
        <div class="buttons">
            <button onclick="window.location.href='redigera.html';">
                Tillbaka
            </button>
        </div>
    </header>
    <main>
        <?php if ($arskursID > 0): ?>
            <h1>Redigera information för Årskurs: <?php echo htmlspecialchars($arskursNamn); ?></h1>
            <form method="post">
                <?php foreach ($infoRows as $row): ?>
                    <div class="info-block">
                        <label for="info<?php echo $row['ID']; ?>">Text <?php echo $row['ID']; ?>:</label>
                        <textarea name="info[<?php echo $row['ID']; ?>]" id="info<?php echo $row['ID']; ?>"
                            rows="3"><?php echo htmlspecialchars($row['information']); ?></textarea>

                        <label>Kategori:</label>
                        <select name="kategori[<?php echo $row['ID']; ?>]">
                            <option value="1" <?php if ($row['kategori'] == 1)
                                echo "selected"; ?>>Information och datakunnighet
                            </option>
                            <option value="2" <?php if ($row['kategori'] == 2)
                                echo "selected"; ?>>Kommunikation och samarbete
                            </option>
                            <option value="3" <?php if ($row['kategori'] == 3)
                                echo "selected"; ?>>Skapa digitalt innehåll
                            </option>
                            <option value="4" <?php if ($row['kategori'] == 4)
                                echo "selected"; ?>>Välmående och miljö</option>
                            <option value="5" <?php if ($row['kategori'] == 5)
                                echo "selected"; ?>>Problemlösning</option>
                        </select>

                        <button type="submit" name="delete" value="<?php echo $row['ID']; ?>" class="delete-btn"
                            onclick="return confirm('Är du säker på att du vill ta bort denna rad?')">Ta bort</button>
                    </div>
                <?php endforeach; ?>

                <h3>Lägg till ny information</h3>
                <div id="new-info-container">
                    <div class="new-info">
                        <textarea name="new_info[]" rows="3" placeholder="Skriv ny information här..."></textarea>
                        <label>Kategori:</label>
                        <select name="new_kategori[]">
                            <option value="1">Information och datakunnighet</option>
                            <option value="2">Kommunikation och samarbete</option>
                            <option value="3">Skapa digitalt innehåll</option>
                            <option value="4">Välmående och miljö</option>
                            <option value="5">Problemlösning</option>
                        </select>
                    </div>
                </div>
                <div class="button-group">
                    <button type="button" onclick="addNewInfo()">Lägg till en ruta till</button>
                    <input type="submit" value="Spara ändringar">
                </div>
            </form>
        <?php else: ?>
            <p>Ingen årskurs vald.</p>
        <?php endif; ?>
    </main>


    <script>
        function addNewInfo() {
            const container = document.getElementById('new-info-container');
            const div = document.createElement('div');
            div.classList.add('new-info');
            div.innerHTML = `
        <textarea name="new_info[]" rows="3" placeholder="Skriv ny information här..."></textarea>
        <label>Kategori:</label>
        <select name="new_kategori[]">
            <option value="1">Information och datakunnighet</option>
            <option value="2">Kommunikation och samarbete</option>
            <option value="3">Skapa digitalt innehåll</option>
            <option value="4">Välmående och miljö</option>
            <option value="5">Problemlösning</option>
        </select>
    `;
            container.appendChild(div);
        }
    </script>
</body>

</html>