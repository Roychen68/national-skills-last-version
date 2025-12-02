<?php
session_start();

$pdo = new PDO ("mysql:host=localhost;charset=utf8;dbname=web04","admin","1234");
header("Content-type: application/json");
?>