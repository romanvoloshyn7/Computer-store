<?php
include 'config.php';

$cartItems = [];
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', $_SESSION['cart']);
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кошик</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart { width: 80%; margin: 0 auto; }
        .cart-item { display: flex; align-items: center; border-bottom: 1px solid #ccc; padding: 10px 0; }
        .cart-item img { width: 100px; height: 100px; object-fit: cover; margin-right: 20px; }
        .cart-item h3 { margin: 0 20px 0 0; }
        .cart-item p { margin: 0 20px 0 0; }
        .cart-item a { color: red; text-decoration: none; }
    </style>
</head>
<body>

<h1>Кошик</h1>
<a href="index.php">⬅ Повернутись до магазину</a>

<div class="cart">
<?php if (!empty($cartItems)): ?>
    <?php foreach ($cartItems as $item): ?>
        <div class="cart-item">
            <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
            <p>Ціна: <?php echo $item['price']; ?> грн</p>
            <a href="remove.php?id=<?php echo $item['id']; ?>">Видалити</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Кошик порожній.</p>
<?php endif; ?>
</div>

</body>
</html>