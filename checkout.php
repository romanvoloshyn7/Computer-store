<?php
include "config.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $phone = $_POST['phone'];
    $total = 0;

    if(isset($_SESSION['cart'])){
        foreach($_SESSION['cart'] as $id){
            $result = $conn->query("SELECT * FROM products WHERE id=$id");
            $product = $result->fetch_assoc();
            $total += $product['price'];
        }
    }

    $conn->query("INSERT INTO orders (phone, total_price) VALUES ('$phone', '$total')");

    $_SESSION['cart'] = [];

    echo "<h2>Замовлення оформлено!</h2>";
    echo "<a href='index.php'>Повернутися в магазин</a>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Оформлення</title>
</head>
<body>

<h1>Оформлення замовлення</h1>

<form method="POST">
    <label>Номер телефону:</label><br>
    <input type="text" name="phone" required><br><br>
    <button type="submit">Підтвердити замовлення</button>
</form>

</body>
</html>