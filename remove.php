<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (!empty($_SESSION['cart'])) {
        if (($key = array_search($id, $_SESSION['cart'])) !== false) {
            unset($_SESSION['cart'][$key]);
        }
    }
}

// Повертаємо на кошик
header('Location: cart.php');
exit;
?>