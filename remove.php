<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if (!empty($_SESSION['cart']) && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
}

header('Location: cart.php');
exit;
?>