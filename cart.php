
<?php
require "db.php";

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["product_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if ($action === "remove") {
        unset($_SESSION["cart"][$id]);
    } elseif ($action === "update") {
        $qty = (int)($_POST["quantity"] ?? 1);

        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $stock = $stmt->fetchColumn();

        if ($stock === false || $stock < 1 || $qty < 1) {
            unset($_SESSION["cart"][$id]);
        } else {
            $_SESSION["cart"][$id] = min($qty, (int)$stock);
        }
    }

    header("Location: cart.php");
    exit;
}

$items = [];
$total = 0;

foreach ($_SESSION["cart"] as $id => $qty) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$id]);
    $p = $stmt->fetch();

    if (!$p || (int)$p["stock"] < 1) {
        unset($_SESSION["cart"][$id]);
        continue;
    }

    $qty = min((int)$qty, (int)$p["stock"]);
    $_SESSION["cart"][$id] = $qty;
    $p["cart_qty"] = $qty;
    $p["line_total"] = $qty * (float)$p["price"];
    $total += $p["line_total"];
    $items[] = $p;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Cart | Nova Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="index.php">nova<span>.</span>store</a>
        <div class="nav-links">
            <a href="index.php">Continue shopping</a>
            <a href="login.php">Admin Login</a>
        </div>
    </div>
</nav>

<main class="container">
    <div class="section-heading">
        <div>
            <span class="eyebrow">YOUR SELECTION</span>
            <h2>Shopping cart</h2>
        </div>
        <span class="muted"><?= count($items) ?> items</span>
    </div>

    <?php if (!$items): ?>
        <div class="panel empty">
            <h2>Your cart is waiting.</h2>
            <p>Add something you love to get started.</p>
            <a class="btn" href="index.php">Explore products →</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="panel">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item">
                        <img
                            src="<?= htmlspecialchars($item["image"] ?: "https://placehold.co/200?text=Product") ?>"
                            alt="<?= htmlspecialchars($item["name"]) ?>"
                        >
                        <div class="cart-item-info">
                            <strong><?= htmlspecialchars($item["name"]) ?></strong>
                            <p class="muted">
                                ₱<?= number_format((float)$item["price"], 2) ?> each
                            </p>

                            <form method="POST" class="actions">
                                <input type="hidden" name="product_id"
                                    value="<?= (int)$item["id"] ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="number" name="quantity"
                                    min="1" max="<?= (int)$item["stock"] ?>"
                                    value="<?= (int)$item["cart_qty"] ?>"
                                    style="width:85px">
                                <button class="btn-small" type="submit">Update</button>
                            </form>

                            <form method="POST">
                                <input type="hidden" name="product_id"
                                    value="<?= (int)$item["id"] ?>">
                                <input type="hidden" name="action" value="remove">
                                <button class="btn-danger btn-small" type="submit">
                                    Remove
                                </button>
                            </form>
                        </div>

                        <strong>
                            ₱<?= number_format($item["line_total"], 2) ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="panel">
                <h2>Order summary</h2>
                <div class="summary-row">
                    <span class="muted">Subtotal</span>
                    <strong>₱<?= number_format($total, 2) ?></strong>
                </div>
                <div class="summary-row">
                    <span class="muted">Shipping</span>
                    <span>Calculated separately</span>
                </div>
                <hr style="border:0;border-top:1px solid var(--border)">
                <div class="summary-row total">
                    <span>Total</span>
                    <span>₱<?= number_format($total, 2) ?></span>
                </div>
                <p class="muted">Shipping is not included in this demo.</p>
                <a class="btn full" href="checkout.php">Proceed to checkout →</a>
            </div>
        </div>
    <?php endif; ?>
</main>

<footer class="footer">© <?= date("Y") ?> Nova Store</footer>
</body>
</html>
