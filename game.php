<?php
session_start();
require 'config/connexio.php';

$slug = $_GET['slug'] ?? '';

$gameStmt = $pdo->prepare("SELECT g.*, p.name AS platform, gen.name AS genre
                          FROM games g
                          LEFT JOIN platforms p ON g.platform_id = p.id
                          LEFT JOIN genres gen ON g.genre_id = gen.id
                          WHERE g.slug = ?");
$gameStmt->execute([$slug]);
$game = $gameStmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    echo "<p>Juego no encontrado.</p>";
    exit;
}


$imgsStmt = $pdo->prepare("SELECT url, alt, is_main FROM game_images WHERE game_id = ? ORDER BY is_main DESC, id ASC");
$imgsStmt->execute([$game['id']]);
$images = $imgsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($game['title']) ?> - A&M Games</title>
    <link rel="stylesheet" href="estilaje/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<header>
    <h1><a href="index.php" style="text-decoration:none;color:inherit;">A&M Games</a></h1>
</header>

<div class="container" style="max-width:800px;margin:40px auto;">
    <h2><?= htmlspecialchars($game['title']) ?></h2>
    <?php if ($images): ?>
        <?php
            $main = null;
            foreach ($images as $i) {
                if ($i['is_main']) { $main = $i; break; }
            }
            if (!$main) $main = $images[0];
        ?>
        <div class="game-main-image">
            <?php $mainUrl = ltrim($main['url'], '/'); ?>
            <img src="<?= htmlspecialchars($mainUrl) ?>" alt="<?= htmlspecialchars($main['alt']) ?>">
        </div>
        <?php if (count($images) > 1): ?>
            <div class="game-gallery">
                <?php foreach ($images as $i): ?>
                    <img src="<?= htmlspecialchars($i['url']) ?>" alt="<?= htmlspecialchars($i['alt']) ?>" class="thumb" onclick="document.querySelector('.game-main-image img').src='<?= htmlspecialchars($i['url']) ?>';">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <p><strong>Precio:</strong> <?= number_format($game['price'],2) ?> €</p>
    <p><strong>Género:</strong> <?= htmlspecialchars($game['genre'] ?? '-') ?></p>
    <p><strong>Plataforma:</strong> <?= htmlspecialchars($game['platform'] ?? '-') ?></p>
    <p><?= nl2br(htmlspecialchars($game['description'] ?? '')) ?></p>

    <form action="agregar.php" method="POST">
        <input type="hidden" name="id" value="<?= (int)$game['id'] ?>">
        <button type="submit">Añadir al carrito</button>
    </form>
</div>

</body>
</html>