<?php
session_start();
include "productos.php";

$subtotal = 0;
?>

<h1>Carret de Compra</h1>
<a href="index.php">Tornar a la botiga</a>

<table border="1">
<tr>
    <th>Producte</th>
    <th>Preu</th>
    <th>Quantitat</th>
    <th>Total</th>
    <th>Acció</th>
</tr>

<?php
if(!empty($_SESSION["carrito"])) {
    foreach($_SESSION["carrito"] as $id => $cantidad) {
        $producto = $productos[$id];
        $total = $producto["precio"] * $cantidad;
        $subtotal += $total;
?>
<tr>
    <td><?= $producto["nombre"] ?></td>
    <td><?= $producto["precio"] ?> €</td>
    <td>
        <form action="actualizar.php" method="POST">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="number" name="cantidad" value="<?= $cantidad ?>" min="1">
            <button>Actualitzar</button>
        </form>
    </td>
    <td><?= $total ?> €</td>
    <td>
        <a href="eliminar.php?id=<?= $id ?>">Eliminar</a>
    </td>
</tr>
<?php
    }
}
?>
</table>

<?php
$iva = $subtotal * 0.21;
$totalFinal = $subtotal + $iva;
?>

<h3>Subtotal: <?= $subtotal ?> €</h3>
<h3>IVA (21%): <?= $iva ?> €</h3>
<h2>Total Final: <?= $totalFinal ?> €</h2>
