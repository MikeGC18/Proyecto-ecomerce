<?php
include "config/connexio.php";

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM games WHERE slug = ?");
$stmt->execute([$slug]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    die("Juego no encontrado");
}

$imgStmt = $pdo->prepare("SELECT * FROM game_images WHERE game_id = ?");
$imgStmt->execute([$game['id']]);
$images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($game['title']) ?></title>
    <link rel="stylesheet" href="estilaje/game.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <h1><a href="index.php">A&M Games</a></h1>
</header>

<div class="container">

    <a href="index.php" class="back-link">← Volver a la tienda</a>

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
                    <img src="<?= htmlspecialchars($i['url']) ?>"
                         alt="<?= htmlspecialchars($i['alt']) ?>"
                         class="thumb"
                         onclick="document.querySelector('.game-main-image img').src='<?= htmlspecialchars($i['url']) ?>';">
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