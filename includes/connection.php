<?php
$host = "dpg-d0rmnire5dus73cjcj5g-a.db.render.com";
$port = "5432";
$dbname = "scms_r5wl";
$user = "coffeesyssys";
$password = "bfFVWht3M82ruwazsVLS7drssxmvjX8m";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    $db = new PDO($dsn, $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}
?>
