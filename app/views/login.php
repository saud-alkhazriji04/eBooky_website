<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ebooky</title>
    <link rel="stylesheet" href="<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <main>
        <section class="auth-form">
            <h2>Sign In</h2>
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form action="login" method="post">
                <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Sign In</button>
            </form>
            <p>Don't have an account? <a href="register">Register here</a></p>
        </section>
    </main>
    <?php include 'partials/footer.php'; ?>
</body>
</html> 