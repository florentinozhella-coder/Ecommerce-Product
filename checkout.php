
<?php
require "db.php";

if (empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

if (empty($_SESSION["checkout_csrf"])) {
    $_SESSION["checkout_csrf"] = bin2hex(random_bytes(32));
}

$error = "";
$successOrder = null;
$items = [];
$total = 0;

function loadCheckoutItems(PDO $pdo, array $cart): array {
    $items = [];
    $total = 0;

    foreach ($cart as $id => $qty) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([(int)$id]);
        $product = $stmt->fetch();

        if (!$product) {
            continue;
        }

        $qty = (int)$qty;
        $product["cart_qty"] = $qty;
        $product["line_total"] = $qty * (float)$product["price"];

        $total += $product["line_total"];
        $items[] = $product;
    }

    return [$items, $total];
}

[$items, $total] = loadCheckoutItems($pdo, $_SESSION["cart"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals(
        $_SESSION["checkout_csrf"],
        $_POST["csrf"] ?? ""
    )) {
        $error = "Invalid request. Refresh the page and try again.";
    } else {
        $name = trim($_POST["customer_name"] ?? "");
        $address = trim($_POST["address"] ?? "");

        if ($name === "" || $address === "") {
            $error = "Please enter your name and delivery address.";
        } elseif (strlen($name) > 150 || strlen($address) > 2000) {
            $error = "Please shorten the name or address.";
        } else {
            try {
                $pdo->beginTransaction();

                $cart = $_SESSION["cart"];
                $verifiedItems = [];
                $verifiedTotal = 0;

                foreach ($cart as $id => $qty) {
                    $id = (int)$id;
                    $qty = (int)$qty;

                    if ($qty < 1) {
                        throw new Exception("Invalid product quantity.");
                    }

                    $stmt = $pdo->prepare(
                        "SELECT * FROM products WHERE id = ? FOR UPDATE"
                    );
                    $stmt->execute([$id]);
                    $p = $stmt->fetch();

                    if (!$p || (int)$p["stock"] < $qty) {
                        throw new Exception(
                            "Insufficient stock for " .
                            ($p["name"] ?? "a product") .
                            ". Please update your cart."
                        );
                    }

                    $p["cart_qty"] = $qty;
                    $p["line_total"] = $qty * (float)$p["price"];
                    $verifiedTotal += $p["line_total"];
                    $verifiedItems[] = $p;
                }

                if (!$verifiedItems) {
                    throw new Exception("Your cart is empty.");
                }

                $stmt = $pdo->prepare(
                    "INSERT INTO orders (customer_name, address, total)
                     VALUES (?, ?, ?)"
                );
                $stmt->execute([$name, $address, $verifiedTotal]);

                $orderId = (int)$pdo->lastInsertId();

                $itemStmt = $pdo->prepare(
                    "INSERT INTO order_items
                     (order_id, product_id, product_name, quantity, price)
                     VALUES (?, ?, ?, ?, ?)"
                );

                $stockStmt = $pdo->prepare(
                    "UPDATE products SET stock = stock - ?
                     WHERE id = ? AND stock >= ?"
                );

                foreach ($verifiedItems as $p) {
                    $itemStmt->execute([
                        $orderId,
                        $p["id"],
                        $p["name"],
                        $p["cart_qty"],
                        $p["price"]
                    ]);

                    $stockStmt->execute([
                        $p["cart_qty"],
                        $p["id"],
                        $p["cart_qty"]
                    ]);

                    if ($stockStmt->rowCount() !== 1) {
                        throw new Exception(
                            "Stock changed during checkout. Please try again."
                        );
                    }
                }

                $pdo->commit();

                $_SESSION["cart"] = [];
                $_SESSION["last_order_id"] = $orderId;
                $_SESSION["checkout_csrf"] = bin2hex(random_bytes(32));
                $successOrder = [
                    "id" => $orderId,
                    "name" => $name,
                    "total" => $verifiedTotal
                ];
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = $e instanceof PDOException
                    ? "The order could not be saved. Please check your database and try again."
                    : $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout | Nova Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="index.php">nova<span>.</span>store</a>
        <div class="nav-links">
            <a href="index.php">Store</a>
            <a href="cart.php">Back to cart</a>
        </div>
    </div>
</nav>

<main class="container">
    <?php if ($successOrder): ?>
        <section class="form-card">
            <span class="eyebrow">ORDER CONFIRMED</span>
            <h1>Thank you!</h1>
            <div class="notice success">Your order has been saved successfully.</div>
            <p>Order number: <strong>#<?= $successOrder["id"] ?></strong></p>
            <p>Customer: <?= htmlspecialchars($successOrder["name"]) ?></p>
            <p class="total">
                Total: ₱<?= number_format($successOrder["total"], 2) ?>
            </p>
            <p class="muted">
                This is a demonstration checkout. No online payment was collected.
            </p>
            <a class="btn full" href="index.php">Continue shopping</a>
        </section>
    <?php else: ?>
        <div class="section-heading">
            <div>
                <span class="eyebrow">ALMOST YOURS</span>
                <h2>Checkout</h2>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="notice error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!$items): ?>
            <div class="panel empty">
                <p>Your cart has no available products.</p>
                <a class="btn" href="index.php">Return to store</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <form class="panel" method="POST">
                    <h2>Delivery details</h2>

                    <input type="hidden" name="csrf"
                        value="<?= htmlspecialchars($_SESSION["checkout_csrf"]) ?>">

                    <div class="form-group">
                        <label>Full name</label>
                        <input name="customer_name" maxlength="150" required
                            value="<?= htmlspecialchars($_POST["customer_name"] ?? "") ?>">
                    </div>

                    <div class="form-group">
                        <label>Delivery address</label>
                        <textarea name="address" rows="4" maxlength="2000"
                            required><?= htmlspecialchars($_POST["address"] ?? "") ?></textarea>
                    </div>

                    <button type="submit" class="full">
                        Place order · ₱<?= number_format($total, 2) ?>
                    </button>
                </form>

                <div class="panel">
                    <h2>Your order</h2>
                    <?php foreach ($items as $item): ?>
                        <div class="summary-row">
                            <span>
                                <?= htmlspecialchars($item["name"]) ?>
                                × <?= (int)$item["cart_qty"] ?>
                            </span>
                            <strong>
                                ₱<?= number_format($item["line_total"], 2) ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>

                    <hr style="border:0;border-top:1px solid var(--border)">
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>₱<?= number_format($total, 2) ?></span>
                    </div>
                    <p class="muted">
                        Final prices and stock are checked again when you place your order.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<footer class="footer">© <?= date("Y") ?> Nova Store</footer>
</body>
</html>
