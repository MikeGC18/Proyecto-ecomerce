<?php
session_start();
include "config/connexio.php";

$items = [];

if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $id => $cantidad) {

        $stmt = $pdo->prepare("SELECT id, title, price FROM games WHERE id = ?");
        $stmt->execute([(int)$id]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($game) {
            $imgStmt = $pdo->prepare("SELECT url, alt FROM game_images WHERE game_id = ? AND is_main = 1 LIMIT 1");
            $imgStmt->execute([$game['id']]);
            $img = $imgStmt->fetch(PDO::FETCH_ASSOC);

            if ($img && !empty($img['url'])) {
                $imgUrl = ltrim($img['url'], '/');
            } else {
                $imgUrl = 'imatges/default.jpg';
            }

            $items[] = [
                'nombre' => $game['title'],
                'precio' => (float)$game['price'],
                'cantidad' => $cantidad,
                'total' => (float)$game['price'] * $cantidad,
                'imagen' => $imgUrl,
                'alt' => $img['alt'] ?? $game['title']
            ];
        }
    }
}

unset($_SESSION['carrito']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pago Exitoso</title>
    <link rel="stylesheet" href="estilaje/succes.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <h1>Pago Completado 🎮</h1>
</header>

<div class="container">
    <h2>🎉 ¡Transacción Exitosa!</h2>
    <p>Gracias por tu compra. Aquí está el resumen:</p>

    <?php if (!empty($items)): ?>
        <div class="grid">
            <?php foreach ($items as $it): ?>
            <div class="card">
                <img src="<?= htmlspecialchars($it['imagen']) ?>" alt="<?= htmlspecialchars($it['alt']) ?>">
                <h3><?= htmlspecialchars($it['nombre']) ?></h3>
                <p><strong>Precio:</strong> <?= number_format($it['precio'],2) ?> €</p>
                <p><strong>Cantidad:</strong> <?= $it['cantidad'] ?></p>
                <p><strong>Total:</strong> <?= number_format($it['total'],2) ?> €</p>
            </div>
            <?php endforeach; ?>
        </div>

        <?php
        $totalFinal = array_sum(array_column($items, 'total'));
        ?>
        <div class="totals">
            <h2>Total Pagado: <?= number_format($totalFinal,2) ?> €</h2>
        </div>
    <?php else: ?>
        <p class="no-games">No se encontraron productos en el pedido.</p>
    <?php endif; ?>

    <a href="index.php" class="back-link">← Volver a la tienda</a>
</div>

</body>
</html>