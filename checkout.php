<?php
include "config.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_name = htmlspecialchars(trim($_POST['client_name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $total = 0;
    $cartItems = [];
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
                $total += $product['subtotal'];
                $cartItems[] = $product;
            }
            $pdo->beginTransaction();
            $stmtOrder = $pdo->prepare("INSERT INTO orders (client_name, phone, total_price, status) VALUES (:client_name, :phone, :total_price, 'pending')");
            $stmtOrder->execute([
                'client_name' => $client_name,
                'phone' => $phone,
                'total_price' => $total
            ]);
            $orderId = $pdo->lastInsertId();
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price, quantity) VALUES (:order_id, :product_id, :price, :quantity)");
            foreach ($cartItems as $item) {
                $stmtItem->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity']
                ]);
            }
            $pdo->commit();
            $_SESSION['cart'] = [];
            echo "<h2>Замовлення №$orderId успішно оформлено!</h2>";
            echo "<a href='index.php'>Повернутися в магазин</a>";
            exit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            die("Помилка при оформленні замовлення: " . $e->getMessage());
        }
    } else {
        echo "<h2>Ваш кошик порожній!</h2>";
        echo "<a href='index.php'>Повернутися в магазин</a>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Оформлення замовлення</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-form { width: 300px; margin: 50px auto; text-align: center; padding: 20px; border: 1px solid #ccc; border-radius: 10px; }
        .checkout-form input { width: 90%; padding: 8px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        .checkout-form button { background: green; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
<h1>Оформлення замовлення</h1>
<div style="text-align: center;">
    <a href="cart.php">⬅ Назад до кошика</a>
</div>
<div class="checkout-form">
    <form method="POST">
        <label>Ваше ім'я:</label>
        <input type="text" name="client_name" placeholder="Іван" required>
        <label>Номер телефону:</label>
        <input type="text" name="phone" placeholder="+380..." required>
        <button type="submit">Підтвердити замовлення</button>
    </form>
</div>
</body>
</html>