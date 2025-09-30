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
    <title>Redigera årskurs</title>
    <link rel="stylesheet" href="../index.css">
    <style>
        textarea { width: 100%; margin-bottom: 10px; }
        .info-block { margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; border-radius: 6px; }
        .new-info { background: #f9f9f9; padding: 10px; border: 1px dashed #ccc; margin-bottom: 10px; }
        .delete-btn { background: #d9534f; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .delete-btn:hover { background: #c9302c; }
    </style>
</head>
<body>
<?php if ($arskursID > 0): ?>
    <h1>Redigera information för Årskurs: <?php echo htmlspecialchars($arskursNamn); ?></h1>
    <?php if ($message): ?><p><b><?php echo $message; ?></b></p><?php endif; ?>

    <form method="post">
        <?php foreach ($infoRows as $row): ?>
            <div class="info-block">
                <label for="info<?php echo $row['ID']; ?>">Text <?php echo $row['ID']; ?>:</label><br>
                <textarea name="info[<?php echo $row['ID']; ?>]" id="info<?php echo $row['ID']; ?>" rows="3"><?php echo htmlspecialchars($row['information']); ?></textarea><br>

                <label>Kategori:</label>
                <select name="kategori[<?php echo $row['ID']; ?>]">
                    <option value="1" <?php if ($row['kategori']==1) echo "selected"; ?>>Information och datakunnighet</option>
                    <option value="2" <?php if ($row['kategori']==2) echo "selected"; ?>>Kommunikation och samarbete</option>
                    <option value="3" <?php if ($row['kategori']==3) echo "selected"; ?>>Skapa digitalt innehåll</option>
                    <option value="4" <?php if ($row['kategori']==4) echo "selected"; ?>>Välmående och miljö</option>
                    <option value="5" <?php if ($row['kategori']==5) echo "selected"; ?>>Problemlösning</option>
                </select>
                <br><br>

                <button type="submit" name="delete" value="<?php echo $row['ID']; ?>" class="delete-btn" onclick="return confirm('Är du säker på att du vill ta bort denna rad?')">Ta bort</button>
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
        <button type="button" onclick="addNewInfo()">Lägg till en ruta till</button>
        <br><br>
        <input type="submit" value="Spara ändringar">
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
