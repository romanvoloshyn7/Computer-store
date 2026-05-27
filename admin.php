<?php
include 'config.php';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged']);
    header('Location: admin.php');
    exit;
}

if (isset($_POST['login_submit'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged'] = true;
    } else {
        $login_error = "Неправильний пароль!";
    }
}

if (!isset($_SESSION['admin_logged'])) {
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вхід в адмінку</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-box { width: 300px; margin: 100px auto; text-align: center; padding: 30px; border: 1px solid #ccc; border-radius: 8px; background: #fafafa; }
        .login-box input { width: 90%; padding: 8px; margin: 15px 0; border: 1px solid #ccc; border-radius: 4px; }
        .login-box button { background: blue; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Вхід в панель</h2>
        <?php if(isset($login_error)) echo "<p style='color:red;'>$login_error</p>"; ?>
        <form method="POST">
            <label>Введіть пароль адміністратора:</label>
            <input type="password" name="password" required>
            <button type="submit" name="login_submit">Увійти</button>
        </form>
        <p><a href="index.php">Назад на сайт</a></p>
    </div>
</body>
</html>
<?php
    exit;
}

if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $new_status, 'id' => $order_id]);
    } catch (PDOException $e) {
        die("Помилка статусу: " . $e->getMessage());
    }
    header('Location: admin.php');
    exit;
}

if (isset($_POST['add_product'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $price = (float)$_POST['price'];
    $description = htmlspecialchars(trim($_POST['description']));
    $category = $_POST['category'];
    $image = htmlspecialchars(trim($_POST['image']));
    if (empty($image)) {
        $image = 'images/no-image.jpg';
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category, image) VALUES (:name, :price, :description, :category, :image)");
        $stmt->execute(['name' => $name, 'price' => $price, 'description' => $description, 'category' => $category, 'image' => $image]);
    } catch (PDOException $e) {
        die("Помилка додавання: " . $e->getMessage());
    }
    header('Location: admin.php?tab=products');
    exit;
}

if (isset($_GET['delete_product_id'])) {
    $p_id = (int)$_GET['delete_product_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $p_id]);
    } catch (PDOException $e) {
        die("Помилка видалення товару: " . $e->getMessage());
    }
    header('Location: admin.php?tab=products');
    exit;
}

if (isset($_POST['edit_product_submit'])) {
    $p_id = (int)$_POST['product_id'];
    $name = htmlspecialchars(trim($_POST['name']));
    $price = (float)$_POST['price'];
    $description = htmlspecialchars(trim($_POST['description']));
    $category = $_POST['category'];
    $image = htmlspecialchars(trim($_POST['image']));
    try {
        $stmt = $pdo->prepare("UPDATE products SET name = :name, price = :price, description = :description, category = :category, image = :image WHERE id = :id");
        $stmt->execute(['name' => $name, 'price' => $price, 'description' => $description, 'category' => $category, 'image' => $image, 'id' => $p_id]);
    } catch (PDOException $e) {
        die("Помилка оновлення товару: " . $e->getMessage());
    }
    header('Location: admin.php?tab=products');
    exit;
}

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';

$orders = [];
if ($current_tab === 'orders') {
    try {
        $sql = "SELECT o.id AS order_id, o.client_name AS client_name, o.phone AS client_phone, o.total_price AS total_sum, o.status AS order_status, o.created_at AS order_date, p.name AS product_name, oi.price AS product_price, oi.quantity AS product_quantity, p.image AS product_image FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN products p ON oi.product_id = p.id ORDER BY o.id DESC";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $id = $row['order_id'];
            if (!isset($orders[$id])) {
                $orders[$id] = [
                    'id' => $id, 'name' => $row['client_name'], 'phone' => $row['client_phone'], 'total' => $row['total_sum'], 'status' => $row['order_status'], 'date' => $row['order_date'], 'items' => []
                ];
            }
            $orders[$id]['items'][] = [
                'name' => $row['product_name'], 'price' => $row['product_price'], 'quantity' => $row['product_quantity'], 'image' => $row['product_image']
            ];
        }
    } catch (PDOException $e) {
        die("Помилка замовлень: " . $e->getMessage());
    }
}

$products = [];
if ($current_tab === 'products') {
    try {
        $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
    } catch (PDOException $e) {
        die("Помилка товарів: " . $e->getMessage());
    }
}

$edit_product = null;
if (isset($_GET['edit_product_id'])) {
    $edit_id = (int)$_GET['edit_product_id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $edit_id]);
    $edit_product = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Панель адміністратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { width: 95%; margin: 30px auto; }
        .navigation-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        .tab-link { padding: 10px 20px; background: #eee; color: #333; text-decoration: none; border-radius: 5px 5px 0 0; font-weight: bold; }
        .tab-link.active { background: blue; color: white; }
        .order-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .order-table th, .order-table td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
        .order-table th { background-color: #f4f4f4; font-weight: bold; }
        .order-table tr:nth-child(even) { background-color: #f9f9f9; }
        .product-preview { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; }
        .product-preview img { width: 40px; height: 40px; object-fit: contain; border: 1px solid #eee; border-radius: 4px; }
        .form-box { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 30px; width: 100%; max-width: 500px; }
        .form-box input, .form-box textarea, .form-box select { width: 100%; padding: 8px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .form-box button { background: green; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .status-form select { padding: 5px; border-radius: 4px; }
        .status-form button { padding: 5px 10px; background: #333; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px; }
        .btn-del { color: red; text-decoration: none; font-weight: bold; margin-left: 10px; }
        .btn-edit { color: blue; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
<div class="admin-container">
    <h1>Панель адміністратора</h1>
    <div class="navigation-tabs">
        <a href="index.php" class="tab-link">Перейти на сайт</a>
        <a href="admin.php?tab=orders" class="tab-link <?php echo $current_tab === 'orders' ? 'active' : ''; ?>">📋 Замовлення</a>
        <a href="admin.php?tab=products" class="tab-link <?php echo $current_tab === 'products' ? 'active' : ''; ?>">📦 Товари</a>
        <a href="admin.php?action=logout" class="tab-link" style="margin-left: auto; color: red;">Вихід</a>
    </div>

    <?php if ($current_tab === 'orders'): ?>
        <h2>Список замовлень</h2>
        <?php if (!empty($orders)): ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Дата</th>
                        <th>Клієнт</th>
                        <th>Телефон</th>
                        <th>Товари</th>
                        <th>Сума</th>
                        <th>Статус замовлення</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($order['date'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($order['name']); ?></strong></td>
                            <td><a href="tel:<?php echo htmlspecialchars($order['phone']); ?>"><?php echo htmlspecialchars($order['phone']); ?></a></td>
                            <td>
                                <?php foreach ($order['items'] as $item): 
                                    $img = !empty($item['image']) ? $item['image'] : 'images/no-image.jpg';
                                ?>
                                    <div class="product-preview">
                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="">
                                        <span><?php echo htmlspecialchars($item['name']); ?> <strong>(<?php echo $item['quantity']; ?> шт.)</strong></span>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td><span style="color: green; font-weight: bold;"><?php echo number_format($order['total'], 2, '.', ' '); ?> грн</span></td>
                            <td>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>В очікуванні</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Обробляється</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Виконано</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Скасовано</option>
                                    </select>
                                    <button type="submit" name="update_status">Оновити</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Замовлень немає.</p>
        <?php endif; ?>

    <?php elseif ($current_tab === 'products'): ?>
        
        <?php if ($edit_product): ?>
            <h2>Редагувати товар</h2>
            <div class="form-box" style="border: 2px solid blue;">
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                    <label>Назва товару:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>
                    <label>Ціна (грн):</label>
                    <input type="number" step="0.01" name="price" value="<?php echo $edit_product['price']; ?>" required>
                    <label>Категорія:</label>
                    <select name="category">
                        <option value="Ноутбуки" <?php echo $edit_product['category'] === 'Ноутбуки' ? 'selected' : ''; ?>>Ноутбуки</option>
                        <option value="Монітори" <?php echo $edit_product['category'] === 'Монітори' ? 'selected' : ''; ?>>Монітори</option>
                        <option value="Аксесуари" <?php echo $edit_product['category'] === 'Аксесуари' ? 'selected' : ''; ?>>Аксесуари</option>
                        <option value="Інше" <?php echo $edit_product['category'] === 'Інше' ? 'selected' : ''; ?>>Інше</option>
                    </select>
                    <label>Опис товару:</label>
                    <textarea name="description" rows="4"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                    <label>Шлях до картинки:</label>
                    <input type="text" name="image" value="<?php echo htmlspecialchars($edit_product['image']); ?>">
                    <button type="submit" name="edit_product_submit" style="background: blue;">Зберегти зміни</button>
                    <a href="admin.php?tab=products" style="margin-left:15px; color:#666;">Скасувати</a>
                </form>
            </div>
        <?php else: ?>
            <h2>Керування товарами</h2>
            <div class="form-box">
                <h3>Додати новий товар</h3>
                <form method="POST">
                    <label>Назва товару:</label>
                    <input type="text" name="name" required placeholder="Наприклад: Ноутбук Lenovo V15">
                    <label>Ціна (грн):</label>
                    <input type="number" step="0.01" name="price" required placeholder="Наприклад: 19500">
                    <label>Категорія:</label>
                    <select name="category">
                        <option value="Ноутбуки">Ноутбуки</option>
                        <option value="Монітори">Монітори</option>
                        <option value="Аксесуари">Аксесуари</option>
                        <option value="Інше">Інше</option>
                    </select>
                    <label>Опис товару:</label>
                    <textarea name="description" rows="3" placeholder="Детальні характеристики техніки..."></textarea>
                    <label>Шлях до картинки:</label>
                    <input type="text" name="image" placeholder="Наприклад: images/lenovo.jpg">
                    <button type="submit" name="add_product">Додати товар в базу</button>
                </form>
            </div>
        <?php endif; ?>

        <h3>Поточний каталог у базі</h3>
        <table class="order-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Картинка</th>
                    <th>Категорія</th>
                    <th>Назва</th>
                    <th>Опис</th>
                    <th>Ціна</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): 
                    $p_img = !empty($p['image']) ? $p['image'] : 'images/no-image.jpg';
                ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><img src="<?php echo htmlspecialchars($p_img); ?>" style="width:50px; height:50px; object-fit:contain;"></td>
                        <td><span style="font-size:12px; background:#eee; padding:3px 6px; border-radius:4px;"><?php echo htmlspecialchars($p['category']); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                        <td style="font-size:13px; max-width:300px; color:#555;"><?php echo htmlspecialchars($p['description'] ?? ''); ?></td>
                        <td><strong style="color: green;"><?php echo number_format($p['price'], 2, '.', ' '); ?> грн</strong></td>
                        <td>
                            <a href="admin.php?tab=products&edit_product_id=<?php echo $p['id']; ?>" class="btn-edit">✏️ Ред.</a>
                            <a href="admin.php?tab=products&delete_product_id=<?php echo $p['id']; ?>" class="btn-del" onclick="return confirm('Ви впевнені, що хочете видалити цей товар?')">❌ Вид.</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>