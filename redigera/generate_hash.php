<?php
$password = "1";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash för lösenordet '1': <br>";
echo $hash;
?>