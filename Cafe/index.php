<?php
include 'db_connect.php';

// ADD CUSTOMER
if(isset($_POST['add_customer'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $conn->query("INSERT INTO customers (name,email) VALUES ('$name','$email')");
}

// ADD PRODUCT
if(isset($_POST['add_product'])){
    $pname = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $conn->query("INSERT INTO products (product_name, category, price) VALUES ('$pname','$category','$price')");
}

// ADD ORDER
if(isset($_POST['add_order'])){
    $customer_id = $_POST['customer_id'];
    $order_date = $_POST['order_date'];
    $conn->query("INSERT INTO orders (customer_id, order_date) VALUES ('$customer_id','$order_date')");
    $order_id = $conn->insert_id;
    
    // Add order items
    $products = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    foreach($products as $index => $pid){
        $qty = $quantities[$index];
        $price_query = $conn->query("SELECT price FROM products WHERE product_id='$pid'");
        $price_row = $price_query->fetch_assoc();
        $price = $price_row['price'];
        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$order_id','$pid','$qty','$price')");
    }
}

// Fetch data
$customers = $conn->query("SELECT * FROM customers");
$products = $conn->query("SELECT * FROM products");
$orders = $conn->query("SELECT * FROM orders");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Café Business System</title>
    <head>
    <title>Café Business System</title>
    <link rel="stylesheet" href="style.css">
</head>

    <style>
        body{ font-family: Arial; margin:20px;}
        table{ border-collapse: collapse; width: 100%; margin-bottom: 30px;}
        table, th, td{ border:1px solid #ccc; padding:8px;}
        th{ background-color: #f2f2f2;}
        input, select{ padding:5px; margin:5px;}
        button{ padding:5px 10px; margin-top:10px; cursor:pointer;}
    </style>
</head>
<body>
    <h1>Café Business System</h1>

    <!-- Add Customer -->
    <h2>Add Customer</h2>
    <form method="POST">
        Name: <input type="text" name="name" required>
        Email: <input type="email" name="email" required>
        <button type="submit" name="add_customer">Add Customer</button>
    </form>

    <!-- Add Product -->
    <h2>Add Product</h2>
    <form method="POST">
        Name: <input type="text" name="product_name" required>
        Category: <input type="text" name="category" required>
        Price: <input type="number" name="price" step="0.01" required>
        <button type="submit" name="add_product">Add Product</button>
    </form>

    <!-- Add Order -->
    <h2>Add Order</h2>
    <form method="POST">
        Customer:
        <select name="customer_id">
            <?php while($c = $customers->fetch_assoc()){ ?>
                <option value="<?= $c['customer_id'] ?>"><?= $c['name'] ?></option>
            <?php } ?>
        </select>
        Order Date: <input type="date" name="order_date" required><br>

        <!-- Order Items -->
        Products: <br>
        <?php
        $products = $conn->query("SELECT * FROM products");
        while($p = $products->fetch_assoc()){ ?>
            <input type="checkbox" name="product_id[]" value="<?= $p['product_id'] ?>"> <?= $p['product_name'] ?>
            Quantity: <input type="number" name="quantity[]" value="1" min="1"><br>
        <?php } ?>

        <button type="submit" name="add_order">Add Order</button>
    </form>

    <!-- Display Orders -->
    <h2>Orders</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Products</th>
            <th>Total Amount</th>
        </tr>
        <?php
        $orders = $conn->query("SELECT o.order_id, c.name AS customer_name
                                FROM orders o
                                JOIN customers c ON o.customer_id = c.customer_id");
        while($o = $orders->fetch_assoc()){
            $order_id = $o['order_id'];
            $items = $conn->query("SELECT p.product_name, oi.quantity, oi.price 
                                   FROM order_items oi 
                                   JOIN products p ON oi.product_id = p.product_id
                                   WHERE order_id='$order_id'");
            $products_list = '';
            $total = 0;
            while($i = $items->fetch_assoc()){
                $products_list .= $i['product_name'].' ('.$i['quantity'].')<br>';
                $total += $i['quantity'] * $i['price'];
            }
            echo "<tr>
                    <td>{$order_id}</td>
                    <td>{$o['customer_name']}</td>
                    <td>{$products_list}</td>
                    <td>₱{$total}</td>
                  </tr>";
        }
        ?>
    </table>

    <!-- Example SQL Subquery: Top Customer -->
    <h2>Top Customer (by Spending)</h2>
    <?php
    $top_customer = $conn->query("
        SELECT name, email
        FROM customers
        WHERE customer_id = (
            SELECT customer_id
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            GROUP BY customer_id
            ORDER BY SUM(oi.quantity * oi.price) DESC
            LIMIT 1
        )
    ");
    if($tc = $top_customer->fetch_assoc()){
        echo "<p>".$tc['name']." (".$tc['email'].")</p>";
    }
    ?>
</body>
</html>
