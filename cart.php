<?php
include 'config.php';

if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit;
}

if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($_POST['action'] === 'increase') {
        $_SESSION['cart'][$id]++;
    } elseif ($_POST['action'] === 'decrease') {
        $_SESSION['cart'][$id]--;
        if ($_SESSION['cart'][$id] <= 0) {
            unset($_SESSION['cart'][$id]);
        }
    }
    header('Location: cart.php');
    exit;
}

$cartItems = [];
$totalSum = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();
        foreach ($products as $product) {
            $id = $product['id'];
            $quantity = $_SESSION['cart'][$id];
            $product['quantity'] = $quantity;
            $product['subtotal'] = $product['price'] * $quantity;
            $totalSum += $product['subtotal'];
            $cartItems[] = $product;
        }
    } catch (PDOException $e) {
        die("Помилка кошика: " . $e->getMessage());
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
        .cart { width: 80%; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .cart-item { display: flex; align-items: center; border-bottom: 1px solid #e2e8f0; padding: 10px 0; justify-content: space-between; }
        .cart-item-info { display: flex; align-items: center; }
        .cart-item img { width: 80px; height: 80px; object-fit: contain; margin-right: 20px; background: #fafafa; padding: 5px; border-radius: 6px; }
        .cart-item h3 { margin: 0 20px 0 0; min-width: 200px; font-size: 1.1rem; }
        .cart-item p { margin: 0 20px 0 0; }
        .cart-total { text-align: right; margin-top: 20px; font-size: 20px; font-weight: bold; border-top: 2px solid #e2e8f0; padding-top: 20px; }
        .checkout-btn { display: inline-block; background: green; color: white; padding: 12px 25px; margin-top: 15px; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .qty-btn { background: #edf2f7; border: 1px solid #cbd5e0; padding: 5px 10px; cursor: pointer; font-weight: bold; font-size: 16px; border-radius: 6px; }
        .qty-form { display: inline-block; margin: 0 5px; }
        .clear-btn { background: #e53e3e; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; float: left; margin-top: 15px; }
    </style>
</head>
<body>
<h1>Кошик</h1>
<div style="text-align: center; margin-bottom: 20px;">
    <a href="index.php" style="text-decoration: none; font-weight: bold; color: #3182ce;">⬅ Повернутись до магазину</a>
</div>
<div class="cart">
<?php if (!empty($cartItems)): ?>
    <?php foreach ($cartItems as $item): 
        $imageFile = !empty($item['image']) ? $item['image'] : 'images/no-image.jpg';
    ?>
        <div class="cart-item">
            <div class="cart-item-info">
                <img src="<?php echo htmlspecialchars($imageFile); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                <p><?php echo number_format($item['price'], 2, '.', ' '); ?> грн</p>
                
                <form method="POST" class="qty-form">
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                    <button type="submit" name="action" value="decrease" class="qty-btn">-</button>
                </form>
                <span style="font-size: 16px; font-weight: bold; margin: 0 10px;"><?php echo $item['quantity']; ?> шт.</span>
                <form method="POST" class="qty-form">
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                    <button type="submit" name="action" value="increase" class="qty-btn">+</button>
                </form>
            </div>
            <div>
                <p style="font-weight: bold; display: inline-block; margin-right: 20px; color: #2d3748;">
                    <?php echo number_format($item['subtotal'], 2, '.', ' '); ?> грн
                </p>
                <a href="remove.php?id=<?php echo $item['id']; ?>" class="delete-link" style="color: red; text-decoration: none; font-weight: bold;">Видалити</a>
            </div>
        </div>
    <?php endforeach; ?>
    
    <div style="overflow: hidden;">
        <form method="POST">
            <button type="submit" name="clear_cart" class="clear-btn">🧹 Очистити кошик</button>
        </form>
        
        <div class="cart-total">
            Загальна сума: <?php echo number_format($totalSum, 2, '.', ' '); ?> грн
            <br>
            <a href="checkout.php" class="checkout-btn">Оформити замовлення</a>
        </div>
    </div>
<?php else: ?>
    <p style="text-align: center; font-size: 18px; color: #718096; padding: 20px 0;">Ваш кошик порожній.</p>
<?php endif; ?>
</div>
</body>
</html>