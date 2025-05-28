<?php
$host = "YOUR_SUPABASE_HOST";
$port = "5432";
$dbname = "YOUR_DB_NAME";
$user = "YOUR_DB_USER";
$password = "YOUR_DB_PASSWORD";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    $db = new PDO($dsn, $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}
?>
