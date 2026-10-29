
<?php
require "db.php";

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_to_cart"])) {

    $id = (int)($_POST["product_id"] ?? 0);
    $qty = max(1, min(99, (int)($_POST["quantity"] ?? 1)));

    $stmt = $pdo->prepare(
        "SELECT id, stock FROM products WHERE id = ?"
    );
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product && $product["stock"] > 0) {
        $current = (int)($_SESSION["cart"][$id] ?? 0);
        $_SESSION["cart"][$id] =
            min((int)$product["stock"], $current + $qty);
        $_SESSION["message"] = "Product added to your cart!";
    } else {
        $_SESSION["message"] = "This product is unavailable.";
    }

    header("Location: index.php");
    exit;
}

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($search !== "") {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category !== "") {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY RAND()";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query(
    "SELECT DISTINCT category FROM products ORDER BY category"
)->fetchAll(PDO::FETCH_COLUMN);

$cartCount = array_sum($_SESSION["cart"]);
$message = $_SESSION["message"] ?? "";
unset($_SESSION["message"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nova Store | Discover Something New</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="index.php">nova<span>.</span>store</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="#products">Shop</a>
            <a href="cart.php">Cart (<?= $cartCount ?>)</a>
            <a class="btn btn-light" href="login.php">Admin Login</a>
        </div>
    </div>
</nav>

<main class="container">
    <section class="hero">
        <div>
            <span class="eyebrow">YOUR EVERYDAY DISCOVERY</span>
            <h1>Good finds.<br>Better everyday.</h1>
            <p>
                Discover thoughtful picks for your lifestyle.
                From everyday essentials to little upgrades,
                find something worth adding to your cart.  </p>
            <h3>By: Zhella Florentino,
            Xyrus Florentino,
            Ralph Tadlas,
            Lovely Quistorio</h3>
            
            <a class="btn" href="#products">Explore Collection →</a>
        </div>
        <div class="hero-art">✦</div>
    </section>

    <?php if ($message): ?>
        <div class="notice success">
            <?= htmlspecialchars($message) ?>
            <a href="cart.php"><strong> View cart →</strong></a>
        </div>
    <?php endif; ?>

    <section id="products">
        <div class="section-heading">
            <div>
                <span class="eyebrow">THE COLLECTION</span>
                <h2>Find your next favorite.</h2>
            </div>
            <span class="muted"><?= count($products) ?> products</span>
        </div>

        <form class="toolbar" method="GET">
            <input
                type="search"
                name="search"
                placeholder="Search products..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <select name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"
                        <?= $category === $cat ? "selected" : "" ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Search</button>
            <a class="btn btn-light" href="index.php">Reset</a>
        </form>

        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <article class="product-card">
                    <img
                        class="product-image"
                        src="<?= htmlspecialchars($p["image"] ?: "https://placehold.co/600x400?text=Product") ?>"
                        alt="<?= htmlspecialchars($p["name"]) ?>"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='https://placehold.co/600x400?text=Product';"
                    >

                    <div class="product-info">
                        <span class="category">
                            <?= htmlspecialchars($p["category"]) ?>
                        </span>
                        <h3><?= htmlspecialchars($p["name"]) ?></h3>
                        <p class="description">
                            <?= htmlspecialchars($p["description"] ?? "") ?>
                        </p>

                        <p class="stock">
                            <?= (int)$p["stock"] > 0
                                ? (int)$p["stock"] . " available"
                                : "Out of stock" ?>
                        </p>

                        <div class="product-bottom">
                            <span class="price">
                                ₱<?= number_format((float)$p["price"], 2) ?>
                            </span>

                            <?php if ((int)$p["stock"] > 0): ?>
                                <form method="POST">
                                    <input type="hidden" name="product_id"
                                        value="<?= (int)$p["id"] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" name="add_to_cart">
                                        + Add
                                    </button>
                                </form>
                            <?php else: ?>
                                <button disabled>Sold Out</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!$products): ?>
            <div class="panel empty">
                <h3>No products found</h3>
                <p>Try another search or choose a different category.</p>
                <a class="btn" href="index.php">Show all products</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer class="footer">
    <div class="container">
        © <?= date("Y") ?> Nova Store · Find something good.
    </div>
</footer>
</body>
</html>
