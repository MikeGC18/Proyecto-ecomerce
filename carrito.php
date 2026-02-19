<?php
session_start();
include "config/connexio.php";
 
$subtotal = 0;

// Comprobar si la tabla `games` está accesible
try {
    $pdo->query('SELECT 1 FROM games LIMIT 1');
    $db_ok = true;
} catch (Exception $e) {
    $db_ok = false;
}
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
        
        if ($db_ok) {
            try {
                $stmt = $pdo->prepare('SELECT id, title AS nombre, price AS precio FROM games WHERE id = ?');
                $stmt->execute([(int)$id]);
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($fila) {
                    $producto = ['nombre' => $fila['nombre'], 'precio' => $fila['precio']];
                } else {
                    $producto = ['nombre' => 'Producto no disponible en BD', 'precio' => 0];
                }
            } catch (Exception $e) {
                $producto = ['nombre' => 'Error BD', 'precio' => 0];
            }
        } else {
            $producto = ['nombre' => 'BD no disponible', 'precio' => 0];
        }

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
