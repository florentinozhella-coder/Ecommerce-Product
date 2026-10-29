
<?php
require "db.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION["csrf"], $_POST["csrf"] ?? "")) {
        exit("Invalid request. Please refresh the page.");
    }

    $action = $_POST["action"] ?? "";

    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Product deleted.";
    }

    if ($action === "save") {
        $id = (int)($_POST["id"] ?? 0);
        $name = trim($_POST["name"] ?? "");
        $category = trim($_POST["category"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $price = filter_var($_POST["price"] ?? "", FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST["stock"] ?? "", FILTER_VALIDATE_INT);
        $image = trim($_POST["image"] ?? "");

        if ($name === "" || $category === "" ||
            $price === false || $price < 0 ||
            $stock === false || $stock < 0) {
            $message = "Enter a valid name, category, price, and stock.";
        } elseif ($image !== "" &&
            !filter_var($image, FILTER_VALIDATE_URL)) {
            $message = "Image must be a valid URL.";
        } else {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE products SET name=?, category=?, description=?,
                     price=?, stock=?, image=? WHERE id=?"
                );
                $stmt->execute([
                    $name, $category, $description,
                    $price, $stock, $image, $id
                ]);
                $message = "Product updated successfully.";
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO products
                     (name, category, description, price, stock, image)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $name, $category, $description,
                    $price, $stock, $image
                ]);
                $message = "New product added successfully.";
            }
        }
    }
}

$editProduct = null;

if (isset($_GET["edit"])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET["edit"]]);
    $editProduct = $stmt->fetch();
}

$products = $pdo->query(
    "SELECT * FROM products ORDER BY id DESC"
)->fetchAll();

$productCount = count($products);
$stockCount = array_sum(array_column($products, "stock"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | Nova Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="index.php">nova<span>.</span>store</a>
        <div class="nav-links">
            <a href="index.php">View Store</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<main class="container">
    <div class="section-heading">
        <div>
            <span class="eyebrow">STORE MANAGEMENT</span>
            <h2>Admin dashboard</h2>
            <p class="muted">
                Welcome, <?= htmlspecialchars($_SESSION["username"] ?? "Admin") ?>.
            </p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="notice"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="product-grid">
        <div class="panel">
            <span class="muted">Total products</span>
            <h2><?= $productCount ?></h2>
        </div>
        <div class="panel">
            <span class="muted">Total units in stock</span>
            <h2><?= $stockCount ?></h2>
        </div>
    </div>

    <form class="form-card" method="POST">
        <span class="eyebrow">PRODUCT MANAGEMENT</span>
        <h2><?= $editProduct ? "Edit product" : "Add a new product" ?></h2>

        <input type="hidden" name="csrf"
            value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id"
            value="<?= (int)($editProduct["id"] ?? 0) ?>">

        <div class="form-group">
            <label>Product name</label>
            <input name="name" required maxlength="150"
                value="<?= htmlspecialchars($editProduct["name"] ?? "") ?>"
                placeholder="e.g. Wireless Earbuds">
        </div>

        <div class="form-group">
            <label>Category</label>
            <input name="category" required maxlength="100"
                list="category-options"
                value="<?= htmlspecialchars($editProduct["category"] ?? "") ?>"
                placeholder="Electronics, Fashion, Home...">
            <datalist id="category-options">
                <option value="Electronics">
                <option value="Fashion">
                <option value="Accessories">
                <option value="Home">
                <option value="Beauty">
                <option value="Sports">
            </datalist>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($editProduct["description"] ?? "") ?></textarea>
        </div>

        <div class="form-group">
            <label>Price (₱)</label>
            <input type="number" name="price" min="0" step="0.01"
                required value="<?= htmlspecialchars((string)($editProduct["price"] ?? "")) ?>">
        </div>

        <div class="form-group">
            <label>Stock quantity</label>
            <input type="number" name="stock" min="0" step="1"
                required value="<?= htmlspecialchars((string)($editProduct["stock"] ?? "10")) ?>">
        </div>

        <div class="form-group">
            <label>Product image URL</label>
            <input type="url" name="image"
                value="<?= htmlspecialchars($editProduct["image"] ?? "") ?>"
                placeholder="https://example.com/product.jpg">
            <p class="muted">Use a direct image URL. Leave blank for a placeholder.</p>
        </div>

        <button type="submit">
            <?= $editProduct ? "Save Changes" : "Add Product" ?>
        </button>

        <?php if ($editProduct): ?>
            <a class="btn btn-light" href="admin.php">Cancel edit</a>
        <?php endif; ?>
    </form>

    <div class="section-heading">
        <h2>Product inventory</h2>
        <span class="muted"><?= $productCount ?> products</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p["name"]) ?></td>
                    <td><?= htmlspecialchars($p["category"]) ?></td>
                    <td>₱<?= number_format((float)$p["price"], 2) ?></td>
                    <td><?= (int)$p["stock"] ?></td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-light btn-small"
                                href="admin.php?edit=<?= (int)$p["id"] ?>">
                                Edit
                            </a>
                            <form method="POST"
                                onsubmit="return confirm('Delete this product?')">
                                <input type="hidden" name="csrf"
                                    value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id"
                                    value="<?= (int)$p["id"] ?>">
                                <button class="btn-danger btn-small" type="submit">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="footer">Nova Store · Admin management</footer>
</body>
</html>
