<?php

$db_host = "localhost";
$db_name = "riget_zoo_adventures";
$db_user = "root";
$db_password = "";

$dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

try {
    $db = new PDO($dsn, $db_user, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $db;
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
