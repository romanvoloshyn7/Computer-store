<?php
include 'config.php'; // підключаємо базу та сесію
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Магазин комп'ютерної техніки</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Магазин комп'ютерної техніки</h1>
<a href="cart.php">🛒 Кошик</a>

<div class="products">

<?php
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0):
    while($row = $result->fetch_assoc()):
        // якщо в базі немає картинки, підставляємо заглушку
        $imageFile = !empty($row['image']) ? $row['image'] : 'no-image.jpg';
?>
    <div class="product">
        <!-- Ось правильний рядок для картинки -->
       <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
        <p><?php echo $row['price']; ?> грн</p>
        <a href="add_to_cart.php?id=<?php echo $row['id']; ?>">Додати в кошик</a>
    </div>

<?php
    endwhile;
else:
    echo "<p>Товари не знайдено.</p>";
endif;
?>

</div>

</body>
</html>