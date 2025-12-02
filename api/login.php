<?php
/* @var PDO $pdo */
include "db.php";

$login = $pdo->query("SELECT * FROM `admin` WHERE `username`='{$_POST['username']}' AND `password`='{$_POST['password']}'")->fetch();

if ($login > 0) {
    $_SESSION['admin'] = true;
    echo true;
} else {
    echo false;
}

?>