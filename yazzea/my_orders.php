<?php

include "config.php";
include "auth.php";

requireCustomer();


$stmt = $conn->prepare(
    "SELECT
        orders.id,
        orders.total_amount,
        orders.payment_method,
        orders.order_date,
        products.name,
        order_items.quantity,
        order_items.price
     FROM orders
     INNER JOIN order_items
     ON orders.id = order_items.order_id
     INNER JOIN products
     ON products.id = order_items.product_id
     WHERE orders.user_id = ?
     ORDER BY orders.id DESC"
);


$stmt->bind_param(
    "i",
    $_SESSION["user_id"]
);


$stmt->execute();

$orders =
    $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>My Orders - Yazzea</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<nav class="navbar">

<div class="brand">
    Yazzea
</div>


<div class="nav-right">

<a
    href="shop.php"
    class="back-button"
>
    ← Shop
</a>


<a
    href="logout.php"
    class="logout"
>
    Logout
</a>

</div>

</nav>


<main class="container">

<h1>
    My Orders
</h1>

<p class="page-description">
    Here are the products you have purchased.
</p>


<?php if (isset($_GET["success"])): ?>

<div class="success">

    Purchase successful!

    <br>

    Thank you for shopping with Yazzea 💜

</div>

<?php endif; ?>


<div class="table-container">

<table>

<thead>

<tr>

<th>Order ID</th>

<th>Product</th>

<th>Quantity</th>

<th>Price</th>

<th>Total</th>

<th>Date</th>

<th>Payment</th>
<th>Date</th>
<th>Receipt</th>

</tr>

</thead>


<tbody>


<?php if ($orders->num_rows > 0): ?>


<?php while ($order = $orders->fetch_assoc()): ?>


<tr>

<td>
    #<?php
    echo $order["id"];
    ?>
</td>


<td>

<strong>

<?php
echo htmlspecialchars(
    $order["name"]
);
?>

</strong>

</td>


<td>
    <?php
    echo $order["quantity"];
    ?>
</td>


<td>

₱<?php
echo number_format(
    $order["price"],
    2
);
?>

</td>


<td>

₱<?php

$itemTotal =
    $order["price"] *
    $order["quantity"];

echo number_format(
    $itemTotal,
    2
);

?>

</td>


<td>

<?php
echo date(
    "M d, Y h:i A",
    strtotime(
        $order["order_date"]
    )
);
?>

</td>

</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td
    colspan="6"
    class="no-data"
>

You haven't purchased anything yet.

<br><br>

<a href="shop.php">
    Start Shopping
</a>

</td>

</tr>

<td>
    <?php
    echo htmlspecialchars(
        $order["payment_method"]
    );
    ?>
</td>

<td>
    <?php
    echo date(
        "M d, Y h:i A",
        strtotime(
            $order["order_date"]
        )
    );
    ?>
</td>

<td>
    <a
        href="receipt.php?order_id=<?php echo $order["id"]; ?>"
        class="edit-button"
    >
        View
    </a>
</td>


<?php endif; ?>


</tbody>

</table>

</div>

</main>

</body>

</html>