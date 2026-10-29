
<?php
require "db.php";

if (isset($_SESSION["user_id"]) && $_SESSION["role"] === "admin") {
    header("Location: admin.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST["username"] ?? "");
    $pass = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1"
    );
    $stmt->execute([$user]);
    $account = $stmt->fetch();

    if ($account && password_verify($pass, $account["password"])
        && $account["role"] === "admin") {

        session_regenerate_id(true);
        $_SESSION["user_id"] = $account["id"];
        $_SESSION["username"] = $account["username"];
        $_SESSION["role"] = $account["role"];

        header("Location: admin.php");
        exit;
    }

    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Nova Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-inner">
            <a class="brand" href="index.php">nova<span>.</span>store</a>
            <a href="index.php">← Back to store</a>
        </div>
    </nav>

    <main class="container">
        <form class="form-card" method="POST">
            <span class="eyebrow">ADMIN ACCESS</span>
            <h1>Welcome back.</h1>
            <p class="muted">Sign in to manage your products and inventory.</p>

            <?php if ($error): ?>
                <div class="notice error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit">Sign In →</button>
        </form>
    </main>
</body>
</html>
