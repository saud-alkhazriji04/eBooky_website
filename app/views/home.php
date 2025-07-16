<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ebooky - Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <main>
        <section class="hero redesigned-hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Welcome to Ebooky</h1>
                    <p>Book flights, hotels, and car rentals with ease.</p>
                </div>
                <div class="hero-logo">
                    <img src="../public/images/ebooky-logo.png" alt="Ebooky Logo" />
                </div>
            </div>
        </section>
        <section class="search redesigned-search">
            <h2>Where do you want to go?</h2>
            <div class="search-buttons">
                <a href="flights" class="glass-btn">✈️ <span>Search Flights</span></a>
                <a href="hotels/search" class="glass-btn">🏨 <span>Search Hotels</span></a>
                <a href="car/search" class="glass-btn">🚗 <span>Search Car Rentals</span></a>
            </div>
        </section>
        <section class="destinations redesigned-destinations">
            <h2>Famous Destinations</h2>
            <div class="destination-list">
                <div class="destination-card glass-card">
                    <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8cGFyaXN8ZW58MHx8MHx8fDA%3D" alt="Paris">
                    <div class="destination-name">Paris</div>
                </div>
                <div class="destination-card glass-card">
                    <img src="https://images.unsplash.com/photo-1499092346589-b9b6be3e94b2?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTR8fG5ldyUyMHlvcmt8ZW58MHx8MHx8fDA%3D" alt="New York">
                    <div class="destination-name">New York</div>
                </div>
                <div class="destination-card glass-card">
                    <img src="https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8VG9reW98ZW58MHx8MHx8fDA%3D" alt="Tokyo">
                    <div class="destination-name">Tokyo</div>
                </div>
                <div class="destination-card glass-card">
                    <img src="https://images.unsplash.com/photo-1486299267070-83823f5448dd?q=80&w=1742&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="London">
                    <div class="destination-name">London</div>
                </div>
            </div>
        </section>
    </main>
    <?php include 'partials/footer.php'; ?>
</body>
</html> 