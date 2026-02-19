<?php
session_start();
include "../config/connexio.php";

$id = $_POST["id"];
$cantidad = $_POST["cantidad"];

if($cantidad <= 0){
    unset($_SESSION["carrito"][$id]);
} else {
    $_SESSION["carrito"][$id] = $cantidad;
}

header("Location: carrito.php");
exit();
