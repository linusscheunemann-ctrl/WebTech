<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
print_r($_POST);



$conn = new mysqli("localhost", "root", "", "users");

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);

if ($stmt->execute()) {
    echo "Registrierung erfolgreich!";
} else {
    echo "Fehler: Benutzername existiert vielleicht schon.";
}

$stmt->close();
$conn->close();
?>