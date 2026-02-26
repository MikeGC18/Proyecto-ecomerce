<?php
session_start();
require 'lib/stripe-php-master/init.php';
include 'config/connexio.php';

// Calcular total del carrito
$subtotal = 0;

if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $stmt = $pdo->prepare('SELECT price FROM games WHERE id = ?');
        $stmt->execute([(int)$id]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($game) {
            $subtotal += (float)$game['price'] * $cantidad;
        }
    }
}

$subtotal = round($subtotal, 2);
$iva = round($subtotal * 0.21, 2);
$totalFinal = round($subtotal + $iva, 2);

// amount en céntimos para Stripe
$amount = (int) round($totalFinal * 100);

// Crear sesión de pago Stripe (simulado)
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'eur',
            'product_data' => [
                'name' => 'Compra en A&M Games',
            ],
            'unit_amount' => $amount,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost/Proyecto%20Ecomerce/succes.php',
    'cancel_url' => 'http://localhost/Proyecto%20Ecomerce/carrito.php',
]);

header("Location: " . $session->url);
exit;