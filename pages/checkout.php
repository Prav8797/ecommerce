<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db.php');  // Include the database connection

$user_id = $_SESSION['user_id'];  // Assuming user ID is stored in session

// Fetch the user's cart items from the database
$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If there are no items in the cart, redirect to the cart page
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate the total cost
$total_cost = 0;
foreach ($cart_items as $item) {
    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_cost += $product['price'] * $item['quantity'];
}

// Process the checkout form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Only these two fields will be input by the user
    $shipping_address = $_POST['shipping_address'];
    $payment_method = $_POST['payment_method'];

    // Insert the order into the 'orders' table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_cost, shipping_address, payment_method, order_status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $total_cost, $shipping_address, $payment_method, 'pending']);
    
    // Get the order ID
    $order_id = $conn->lastInsertId();

    // Insert each cart item into the 'order_items' table
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Insert cart items into 'order_items' table
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $product['price']]);
    }

    // Clear the cart after purchase
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Redirect to a success page
    header("Location: success.php?order_id=" . $order_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        /* General Styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7fc;
    margin: 0;
    padding: 0;
    color: #333;
}

/* Checkout Container */
.checkout-container {
    width: 80%;
    max-width: 900px;
    margin: 50px auto;
    background-color: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Title Styles */
.checkout-container h1 {
    font-size: 2.5em;
    color: #343a40;
    text-align: center;
    margin-bottom: 30px;
}

/* Cart Summary Section */
.cart-summary {
    margin-bottom: 40px;
    border-bottom: 2px solid #eee;
    padding-bottom: 20px;
}

.cart-summary h2 {
    font-size: 1.8em;
    color: #007bff;
    margin-bottom: 20px;
}

/* Cart Item List */
.cart-summary ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.cart-summary li {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 1.1em;
    color: #495057;
}

.cart-summary li:last-child {
    border-bottom: none;
}

.total-cost {
    font-size: 1.4em;
    font-weight: bold;
    color: #28a745;
    text-align: right;
}

/* Form Section */
.checkout-form {
    background-color: #f9f9f9;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.checkout-form h3 {
    font-size: 1.5em;
    color: #343a40;
    margin-bottom: 20px;
}

.checkout-form label {
    font-size: 1.1em;
    color: #495057;
    margin-bottom: 8px;
    display: block;
}

.checkout-form input[type="text"],
.checkout-form textarea,
.checkout-form select {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    font-size: 1em;
    color: #495057;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
}

.checkout-form textarea {
    resize: vertical;
    min-height: 100px;
}

.checkout-form select {
    cursor: pointer;
}

/* Button Styles */
.checkout-form button {
    width: 100%;
    background-color: #28a745;
    color: white;
    font-size: 1.2em;
    padding: 14px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.checkout-form button:hover {
    background-color: #218838;
}

.cart-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
}

.cart-actions a {
    font-size: 1.2em;
    color: #007bff;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.cart-actions a:hover {
    background-color: #e2e6ea;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .checkout-container {
        width: 95%;
        padding: 20px;
    }

    .cart-summary h2 {
        font-size: 1.5em;
    }

    .cart-summary li {
        font-size: 1em;
    }

    .checkout-form button {
        font-size: 1.1em;
        padding: 12px;
    }
    /* Cart Actions Container */
.cart-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

/* Complete Purchase Button */
.complete-purchase-btn {
    background-color: #28a745;
    color: white;
    padding: 12px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 1.1em;
    transition: background-color 0.3s, transform 0.2s;
}

.complete-purchase-btn:hover {
    background-color: #218838;
    transform: translateY(-2px);  /* Adds subtle lift effect */
}

/* Back to Cart Button */
.back-to-cart-btn {
    background-color: #007bff;
    color: white;
    padding: 12px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 1.1em;
    transition: background-color 0.3s, transform 0.2s;
}

.back-to-cart-btn:hover {
    background-color: #0056b3;
    transform: translateY(-2px);  /* Adds subtle lift effect */
}


}

</style>
</head>
<body>

<div class="checkout-container">
    <h1>Checkout</h1>

    <div class="cart-summary">
        <h2>Your Cart</h2>
        <?php if (!empty($cart_items)) : ?>
            <ul>
                <?php foreach ($cart_items as $item) : ?>
                    <?php
                    // Fetch product details for each item in the cart
                    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$item['product_id']]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <li>
                        <?= htmlspecialchars($product['name']); ?> - $<?= number_format($product['price'], 2); ?> x <?= $item['quantity']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="total-cost">
                Total: $<?= number_format($total_cost, 2); ?>
            </div>
        <?php else: ?>
            <p>Your cart is empty!</p>
        <?php endif; ?>
    </div>

    <form method="POST" class="checkout-form">
    <h3>Shipping Information</h3>
    <label for="shipping_address">Shipping Address</label>
    <textarea id="shipping_address" name="shipping_address" required></textarea>

    <h3>Payment Method</h3>
    <label for="payment_method">Choose Payment Method</label>
    <select id="payment_method" name="payment_method" required>
        <option value="credit_card">Credit Card</option>
        <option value="paypal">PayPal</option>
    </select>

    <div class="cart-actions">
        <!-- Changed 'Complete Purchase' from <a> to <button> -->
        <button type="submit" class="complete-purchase-btn">Complete Purchase</button>
        <a href="cart.php" class="back-to-cart-btn">Back to Cart</a>
    </div>
</form>

</div>

</body>
</html>
