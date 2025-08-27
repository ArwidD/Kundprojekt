<?php
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

// Uppdatera existerande texter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['info'])) {
        foreach ($_POST['info'] as $id => $text) {
            $stmt = $conn->prepare("UPDATE information SET information=? WHERE ID=?");
            $stmt->bind_param("si", $text, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Lägg till nya textrutor (om man fyllt i)
    if (!empty($_POST['new_info'])) {
        foreach ($_POST['new_info'] as $text) {
            if (trim($text) !== "") {
                $stmt = $conn->prepare("INSERT INTO information (information, `arskurs ID`) VALUES (?, ?)");
                $stmt->bind_param("si", $text, $arskursID);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    $message = "Informationen har uppdaterats!";
}

// Hämta information för vald årskurs
$infoRows = [];
$arskursNamn = "";
if ($arskursID > 0) {
    $stmt = $conn->prepare("SELECT ID, information FROM information WHERE `arskurs ID`=?");
    $stmt->bind_param("i", $arskursID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $infoRows[] = $row;
    }
    $stmt->close();

    // Hämta namnet på årskursen
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
    <title>Redigera årskurs</title>
    <link rel="stylesheet" href="../index.css">
    <style>
        textarea {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-block {
            margin-bottom: 20px;
        }

        .new-info {
            background: #f9f9f9;
            padding: 10px;
            border: 1px dashed #ccc;
        }
    </style>
</head>

<body>
    <?php if ($arskursID > 0): ?>
        <h1>Redigera information för Årskurs: <?php echo htmlspecialchars($arskursNamn); ?></h1>
        <?php if ($message): ?>
            <p><b><?php echo $message; ?></b></p><?php endif; ?>

        <form method="post">
            <?php foreach ($infoRows as $row): ?>
                <div class="info-block">
                    <label for="info<?php echo $row['ID']; ?>">Text <?php echo $row['ID']; ?>:</label><br>
                    <textarea name="info[<?php echo $row['ID']; ?>]" id="info<?php echo $row['ID']; ?>"
                        rows="4"><?php echo htmlspecialchars($row['information'] ?? ""); ?></textarea>
                </div>
            <?php endforeach; ?>

            <h3>Lägg till ny information</h3>
            <div id="new-info-container">
                <div class="new-info">
                    <textarea name="new_info[]" rows="3" placeholder="Skriv ny information här..."></textarea>
                </div>
            </div>
            <button type="button" onclick="addNewInfo()">Lägg till en ruta till</button>
            <br><br>
            <button type="submit">Spara ändringar</button>
        </form>
    <?php else: ?>
        <p>Ingen årskurs vald.</p>
    <?php endif; ?>

    <p><a href="redigera.html">Tillbaka</a></p>

    <script>
        function addNewInfo() {
            const container = document.getElementById('new-info-container');
            const div = document.createElement('div');
            div.classList.add('new-info');
            div.innerHTML = '<textarea name="new_info[]" rows="3" placeholder="Skriv ny information här..."></textarea>';
            container.appendChild(div);
        }
    </script>
</body>

</html>