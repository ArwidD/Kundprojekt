<?php
session_start();

// DB-anslutning
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "utbildning";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("DB-anslutning misslyckades: " . $conn->connect_error);
}

// Logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';

    echo "DEBUG INFO:<br>";
    echo "Användarnamn inmatat: '" . htmlspecialchars($u) . "'<br>";
    echo "Lösenord inmatat: '" . htmlspecialchars($p) . "'<br><br>";

    $stmt = $conn->prepare("SELECT id, username, password FROM anvandare WHERE username=?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $res = $stmt->get_result();

    echo "Antal rader funna i DB: " . $res->num_rows . "<br><br>";

    if ($row = $res->fetch_assoc()) {
        echo "Användare hittad i DB:<br>";
        echo "- ID: " . $row['id'] . "<br>";
        echo "- Username: '" . htmlspecialchars($row['username']) . "'<br>";
        echo "- Hash: " . substr($row['password'], 0, 20) . "...<br><br>";

        $verify_result = password_verify($p, $row['password']);
        echo "password_verify() resultat: " . ($verify_result ? 'TRUE ✓' : 'FALSE ✗') . "<br><br>";

        if ($verify_result) {
            $_SESSION['user'] = $u;
            $goto = $_SESSION['redirect_after_login'] ?? '/redigera.php';
            unset($_SESSION['redirect_after_login']);
            echo "Login lyckades! Redirectar till: $goto<br>";
            echo "<a href='$goto'>Klicka här om du inte redirectas automatiskt</a>";
            header("Location: $goto");
            exit;
        } else {
            echo "<strong style='color:red;'>Lösenordet matchade inte!</strong><br>";
        }
    } else {
        echo "<strong style='color:red;'>Ingen användare hittades med det användarnamnet!</strong><br>";
    }

    echo "<br><a href='login.php'>Tillbaka till login</a>";
    die(); // Stoppa här för att se all debug-info
}
?>
<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="utf-8">
    <title>Login</title>
</head>

<body>
    <h2>Logga in</h2>
    <?php if ($error)
        echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <label>Användarnamn<br><input name="username" required></label><br>
        <label>Lösenord<br><input type="password" name="password" required></label><br><br>
        <button type="submit">Logga in</button>
    </form>
    <br>
    <form action="../redigera/redigera.html">
        <button type="submit">Tillbaka</button>
    </form>
</body>
</body>

</html>