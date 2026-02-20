<?php
session_start();
include "config/connexio.php";

$cartCount = !empty($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;

$platforms = [];
try {
    $stmtPlat = $pdo->prepare("SELECT DISTINCT p.id, p.name FROM platforms p INNER JOIN games g ON p.id = g.platform_id ORDER BY p.name");
    $stmtPlat->execute();
    $platforms = $stmtPlat->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $platforms = [];
}

$selectedPlatform = isset($_GET['platform']) ? (int)$_GET['platform'] : null;
try {
    $stmt = $pdo->prepare("SELECT g.id, g.title, g.price, g.description, g.stock, p.name AS platform, gen.name AS genre FROM games g LEFT JOIN platforms p ON g.platform_id = p.id LEFT JOIN genres gen ON g.genre_id = gen.id ORDER BY g.title");
    $stmt->execute();
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($selectedPlatform !== null) {
        $games = array_filter($games, function($game) use ($selectedPlatform) {
            foreach ($GLOBALS['platforms'] as $plat) {
                if ($plat['id'] == $selectedPlatform && $game['platform'] == $plat['name']) {
                    return true;
                }
            }
            return false;
        });
        $games = array_values($games);
    }
} catch (Exception $e) {
    $games = [];
    $platforms = [];
    $dbError = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>A&M Games</title>
    <link rel="stylesheet" href="estilaje/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

<header>
    <h1>A&M Games</h1>
    <nav>
        <a href="index.php">Inici</a>
        <?php if(!empty($platforms)): ?>
            <?php foreach($platforms as $plat): ?>
                <a href="index.php?platform=<?= (int)$plat['id'] ?>"><?= htmlspecialchars($plat['name']) ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
        <a href="carrito.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
            <?php if($cartCount > 0): ?>
                <span class="cart-count"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
    </nav>
</header>

<h2>Productes</h2>

<div class="grid">
<?php if(empty($games)): ?>
    <p>No hay juegos para mostrar. Asegúrate de crear las tablas en la base de datos `e-commerce` (pégalo en phpMyAdmin) y que `config/connexio.php` esté correcto.</p>
    <?php if(!empty($dbError)): ?><p style="color:darkred;">Error de base de datos detectado: revisa que la tabla `games` exista.</p><?php endif; ?>
<?php else: ?>
    <?php foreach($games as $game): ?>
    <div class="card">
        <h3><?= htmlspecialchars($game['title']) ?></h3>
        <?php if(!empty($game['genre'])): ?><p><strong>Gènere:</strong> <?= htmlspecialchars($game['genre']) ?></p><?php endif; ?>
        <?php if(!empty($game['platform'])): ?><p><strong>Plataforma:</strong> <?= htmlspecialchars($game['platform']) ?></p><?php endif; ?>
        <p><?= number_format($game['price'],2) ?> €</p>
        <p><?= nl2br(htmlspecialchars($game['description'] ?? '')) ?></p>
        <form action="agregar.php" method="POST">
            <input type="hidden" name="id" value="<?= (int)$game['id'] ?>">
            <button type="submit">Afegir al carret</button>
        </form>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

</body>
</html>
