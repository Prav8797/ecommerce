<?php
// Include the database connection
include('../includes/db.php');

// Get the order ID from the URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Completed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            text-align: center;
            padding: 50px;
        }
        .order-completed {
            background-color: #28a745;
            color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .order-completed h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .order-completed p {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .continue-shopping-btn {
            background-color: #007bff;
            color: white;
            font-size: 1.2em;
            padding: 14px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .continue-shopping-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="order-completed">
    <h1>Order Completed Successfully!</h1>
    <p>Thank you for your purchase. Your order ID is <?= htmlspecialchars($order_id); ?>.</p>
    <a href="../index.php" class="continue-shopping-btn">Continue Shopping</a>
</div>

</body>
</html>
