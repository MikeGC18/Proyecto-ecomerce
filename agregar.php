<?php
session_start();
include "../config/connexio.php";

$id = $_POST["id"]; // ahora es POST

if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

if (isset($_SESSION["carrito"][$id])) {
    $_SESSION["carrito"][$id]++;
} else {
    $_SESSION["carrito"][$id] = 1;
}

header("Location: carrito.php");
exit();
?>

