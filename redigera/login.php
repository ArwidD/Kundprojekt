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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../index.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
            color: #631f63;
            margin-bottom: 20px;
            text-align: center;
        }

        .login-forms {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .login-forms form {
            display: flex;
            flex-direction: column;
        }

        .login-forms label {
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .login-forms input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .login-forms button {
            padding: 12px;
            background: #631f63;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            font-size: 1rem;
        }

        .login-forms button:hover {
            background: #4a164a;
        }

        .login-forms form:last-child button {
            background: #888;
        }

        .login-forms form:last-child button:hover {
            background: #666;
        }

        @media (max-width: 768px) {
            .login-container {
                margin: 20px auto;
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                padding: 15px;
            }

            .login-forms input {
                font-size: 16px;
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
    </header>
    <main>
        <div class="login-container">
            <h2>Logga in</h2>
            <?php if ($error)
                echo "<p style='color:red; text-align: center;'>$error</p>"; ?>
            <div class="login-forms">
                <form method="post">
                    <label>Användarnamn</label>
                    <input name="username" required>
                    <label>Lösenord</label>
                    <input type="password" name="password" required>
                    <button type="submit">Logga in</button>
                </form>
                <form action="../redigera/redigera.html">
                    <button type="submit">Tillbaka</button>
                </form>
            </div>
        </div>
    </main>
</body>
</body>

</html>