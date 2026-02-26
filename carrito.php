<?php
session_start();
include "config/connexio.php";

$subtotal = 0;

try {
    $pdo->query('SELECT 1 FROM games LIMIT 1');
    $db_ok = true;
} catch (Exception $e) {
    $db_ok = false;
}

function formatPrice($price) {
    return number_format($price, 2, ',', '.') . ' €';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Carret de Compra</title>
    <link rel="stylesheet" href="estilaje/carrito.css">
</head>
<body>

<header>
    <h1>La Meva Botiga</h1>
</header>

<div class="container">
    <h2>Carret de Compra</h2>
    <a class="back-link" href="index.php">← Tornar a la botiga</a>

    <div class="grid">
        <?php
        if(!empty($_SESSION["carrito"])) {
            foreach($_SESSION["carrito"] as $id => $cantidad) {

                if ($db_ok) {
                    try {
                        $stmt = $pdo->prepare('SELECT id, title AS nombre, price AS precio FROM games WHERE id = ?');
                        $stmt->execute([(int)$id]);
                        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($fila) {
                            $producto = ['nombre' => $fila['nombre'], 'precio' => (float)$fila['precio']];
                        } else {
                            $producto = ['nombre' => 'Producto no disponible en BD', 'precio' => 0];
                        }
                    } catch (Exception $e) {
                        $producto = ['nombre' => 'Error BD', 'precio' => 0];
                    }
                } else {
                    $producto = ['nombre' => 'BD no disponible', 'precio' => 0];
                }

                $total = round($producto["precio"] * $cantidad, 2);
                $subtotal += $total;
        ?>
        <div class="card">
            <h3><?= htmlspecialchars($producto["nombre"]) ?></h3>
            <p><strong>Preu unitari:</strong> <?= formatPrice($producto["precio"]) ?></p>
            <p><strong>Quantitat:</strong></p>
            <form action="actualizar.php" method="POST">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="number" name="cantidad" value="<?= $cantidad ?>" min="1">
                <button type="submit">Actualitzar</button>
            </form>
            <p><strong>Total:</strong> <?= formatPrice($total) ?></p>
            <a class="eliminar" href="eliminar.php?id=<?= $id ?>">Eliminar</a>
        </div>
        <?php
            }
        } else {
            echo '<p class="no-games">El carret està buit 😢</p>';
        }
        ?>
    </div>

    <?php
    $subtotal = round($subtotal, 2);
    $iva = round($subtotal * 0.21, 2);
    $totalFinal = round($subtotal + $iva, 2);

    // Amount para Stripe en céntimos
    $amountStripe = (int) round($totalFinal * 100);
    ?>

    <div class="totals">
        <h3>Subtotal: <?= formatPrice($subtotal) ?></h3>
        <h3>IVA (21%): <?= formatPrice($iva) ?></h3>
        <h2>Total Final: <?= formatPrice($totalFinal) ?></h2>
    </div>

    <div class="payment-box">
        <a href="checkout.php?amount=<?= $amountStripe ?>" class="btn-pagar">Pagar con Stripe</a>
    </div>
</div>

</body>
</html>