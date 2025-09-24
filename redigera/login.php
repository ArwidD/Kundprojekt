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

    $stmt = $conn->prepare("SELECT id, password FROM anvandare WHERE username=?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (password_verify($p, $row['password'])) {
            $_SESSION['user'] = $u;

            // Redirect tillbaka till sidan man försökte nå
            $goto = $_SESSION['redirect_after_login'] ?? '/redigera.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: $goto");
            exit;
        }
    }
    $error = "Fel användarnamn eller lösenord.";
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