<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ebooky</title>
    <link rel="stylesheet" href="<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <main>
        <section class="auth-form">
            <h2>Register</h2>
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form action="register" method="post">
                <input type="text" name="name" placeholder="Name" required value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>">
                <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="country" placeholder="Country" required value="<?php echo htmlspecialchars($old['country'] ?? ''); ?>">
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login">Sign in here</a></p>
        </section>
    </main>
    <?php include 'partials/footer.php'; ?>
</body>
</html> 