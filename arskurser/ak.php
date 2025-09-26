<?php
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "utbildning";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Anslutningsfel: " . $conn->connect_error]));
}

// Alias på kolumnnamnet så vi slipper mellanslag i JSON
$sql = "SELECT ID, information, `arskurs ID` AS arskurs_id, kategori FROM information";
$result = $conn->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
$conn->close();
