<?php
session_start();
include "productos.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Botiga Online</title>
    <link rel="stylesheet" href="estilos/estilos.css">
</head>
<body>

<header>
    <h1>La Meva Botiga</h1>
    <nav>
        <a href="index.php">Inici</a>
        <a href="carrito.php">Carret</a>
    </nav>
</header>

<h2>Productes</h2>

<div class="grid">
<?php foreach($productos as $id => $producto): ?>
    <div class="card">
        <h3><?= $producto["nombre"] ?></h3>
        <p><?= $producto["precio"] ?> €</p>
        <form action="agregar.php" method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button type="submit">Afegir al carret</button>
        </form>

    </div>
<?php endforeach; ?>
</div>

</body>
</html>
