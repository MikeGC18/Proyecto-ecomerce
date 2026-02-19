<?php
session_start();
include "../config/connexio.php";

if (isset($_GET["id"])) {
    $id = $_GET["id"];
    unset($_SESSION["carrito"][$id]);
}

header("Location: carrito.php");
exit();

