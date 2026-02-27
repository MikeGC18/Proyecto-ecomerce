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

$sliderImages = [
    'imatges/juego-the-elder-scrolls-4820.jpg',
    'imatges/164476-hiper_escape-ubisoft-juego_de_disparos-edificio-juego_de_aventura_de_accion-7680x4320.jpg',
    'imatges/assassins-creed-8k-gaming-89wgmqyt92rw9nqb.jpg'
];

$selectedPlatform = isset($_GET['platform']) ? (int)$_GET['platform'] : null;

try {
    $stmt = $pdo->prepare("
        SELECT g.id, g.title, g.slug, g.price, g.description, g.stock,
               p.name AS platform, gen.name AS genre
        FROM games g
        LEFT JOIN platforms p ON g.platform_id = p.id
        LEFT JOIN genres gen ON g.genre_id = gen.id
        ORDER BY g.title
    ");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header>
    <h1>A&M Games</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <?php if(!empty($platforms)): ?>
            <?php foreach($platforms as $plat): ?>
                <a href="index.php?platform=<?= (int)$plat['id'] ?>">
                    <?= htmlspecialchars($plat['name']) ?>
                </a>
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

<section class="hero-slider">
    <div class="slider-container">
        <?php foreach($sliderImages as $index => $img): ?>
        <div class="slide <?= $index === 0 ? 'active' : '' ?>">
            <div class="slide-bg" style="background-image:url('<?= $img ?>')"></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<h2>Juegos</h2>

<div class="grid">
<?php if(empty($games)): ?>
    <p>No hay juegos para mostrar.</p>
    <?php if(!empty($dbError)): ?>
        <p style="color:darkred;">Error de base de datos.</p>
    <?php endif; ?>
<?php else: ?>
    <?php foreach($games as $game): ?>
    <?php
        $imgStmt = $pdo->prepare("
            SELECT url, alt
            FROM game_images
            WHERE game_id = ? AND is_main = 1
            LIMIT 1
        ");
        $imgStmt->execute([$game['id']]);
        $img = $imgStmt->fetch(PDO::FETCH_ASSOC);

        $imgUrl = ($img && !empty($img['url']))
            ? ltrim($img['url'], '/')
            : 'imatges/default.jpg';

        $imgAlt = $img ? $img['alt'] : $game['title'];
    ?>

    <div class="card">
        <a href="game.php?slug=<?= urlencode($game['slug']) ?>">
            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($imgAlt) ?>">
            <h3><?= htmlspecialchars($game['title']) ?></h3>
        </a>
        <?php if(!empty($game['genre'])): ?>
            <p><strong>Gènere:</strong> <?= htmlspecialchars($game['genre']) ?></p>
        <?php endif; ?>
        <?php if(!empty($game['platform'])): ?>
            <p><strong>Plataforma:</strong> <?= htmlspecialchars($game['platform']) ?></p>
        <?php endif; ?>
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

<script>
let current = 0;
const slides = document.querySelectorAll('.slide');

function nextSlide() {
    slides[current].classList.remove('active');
    current = (current + 1) % slides.length;
    slides[current].classList.add('active');
}

if (slides.length > 1) {
    setInterval(nextSlide, 3500);
}
</script>

</body>
</html>
