<?php
include 'config.php'; 
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

<div class="menu-container">
    <a href="cart.php" class="menu-link cart-active">
        🛒 Кошик <?php echo !empty($_SESSION['cart']) ? '(' . count($_SESSION['cart']) . ')' : ''; ?>
    </a>
    <a href="admin.php" class="menu-link admin-link">
        ⚙️ Панель адміністратора
    </a>
</div>

<div style="max-width: 1200px; margin: 0 auto 30px auto; padding: 0 20px; display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between; align-items: center;">
    <form method="GET" action="index.php" style="display: flex; gap: 10px; width: 100%; max-width: 400px;">
        <input type="text" name="search" placeholder="Пошук товарів..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="flex: 1; padding: 10px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 14px;">
        <button type="submit" style="background: #3182ce; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;">Шукати</button>
    </form>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="index.php" style="text-decoration: none; padding: 8px 16px; background: #edf2f7; color: #2d3748; border-radius: 8px; font-weight: 600; font-size: 14px;">Всі товари</a>
        <a href="index.php?category=Ноутбуки" style="text-decoration: none; padding: 8px 16px; background: #edf2f7; color: #2d3748; border-radius: 8px; font-weight: 600; font-size: 14px;">💻 Ноутбуки</a>
        <a href="index.php?category=Монітори" style="text-decoration: none; padding: 8px 16px; background: #edf2f7; color: #2d3748; border-radius: 8px; font-weight: 600; font-size: 14px;">🖥️ Монітори</a>
        <a href="index.php?category=Аксесуари" style="text-decoration: none; padding: 8px 16px; background: #edf2f7; color: #2d3748; border-radius: 8px; font-weight: 600; font-size: 14px;">🖱️ Аксесуари</a>
        <a href="index.php?category=Комплектуючі" style="text-decoration: none; padding: 8px 16px; background: #edf2f7; color: #2d3748; border-radius: 8px; font-weight: 600; font-size: 14px;">🔌 Комплектуючі</a>
    </div>
</div>

<div class="products">
<?php
try {
    $query = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if (!empty($_GET['search'])) {
        $query .= " AND name LIKE :search";
        $params['search'] = '%' . $_GET['search'] . '%';
    }

    if (!empty($_GET['category'])) {
        $query .= " AND category = :category";
        $params['category'] = $_GET['category'];
    }

    $query .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    if (!empty($products)):
        foreach ($products as $row):
            $imageFile = !empty($row['image']) ? $row['image'] : 'images/no-image.jpg';
    ?>
        <div class="product">
            <div>
                <span style="display: inline-block; background: #e2e8f0; color: #4a5568; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;"><?php echo htmlspecialchars($row['category']); ?></span>
                <img src="<?php echo htmlspecialchars($imageFile); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p style="font-size: 13px; color: #718096; font-weight: normal; margin-bottom: 15px; min-height: 40px; text-align: left; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($row['description'] ?? 'Опис відсутній'); ?></p>
            </div>
            <div>
                <p style="font-size: 1.3rem; color: #2b6cb0; font-weight: 700; margin-bottom: 15px; text-align: center;"><?php echo number_format($row['price'], 2, '.', ' '); ?> грн</p>
                <a href="add_to_cart.php?id=<?php echo $row['id']; ?>">Додати в кошик</a>
            </div>
        </div>
    <?php
        endforeach;
    else:
        echo "<p style='grid-column: 1/-1; text-align: center; color: #718096; font-size: 18px; padding: 40px 0;'>Товарів не знайдено за вашим запитом.</p>";
    endif;
} catch (PDOException $e) {
    echo "<p style='color: red; grid-column: 1/-1; text-align: center;'>Помилка завантаження: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
</div>
</body>
</html>