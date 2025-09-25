<?php
// Configuration de la connexion à la base de données
$host = 'localhost';
$dbname = 'benstore_db';
$username = 'root'; // Par défaut dans Wamp
$password = ''; // Par défaut vide dans Wamp

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>