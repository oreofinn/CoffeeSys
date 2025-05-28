<?php
$host = "db.bzgbztcvbrbiwfwpypib.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "rancesJcne092121";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    $db = new PDO($dsn, $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}
?>
