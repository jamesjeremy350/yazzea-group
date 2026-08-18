<?php

include "config.php";
include "auth.php";

requireAdmin();


$orders = $conn->query(
    "SELECT
        orders.id,
        orders.total_amount,
        orders.payment_method,
        orders.order_date,
        users.fullname,
        users.username
     FROM orders
     INNER JOIN users
     ON orders.user_id = users.id
     ORDER BY orders.id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Orders - Yazzea</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<nav class="navbar">

<div class="brand">
    Yazzea
</div>


<div class="nav-right">

<a
    href="admin.php"
    class="back-button"
>
    ← Dashboard
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
    Customer Orders
</h1>

<p class="page-description">
    View all purchases made by customers.
</p>


<div class="table-container">

<table>

<thead>

<tr>

<th>Order ID</th>

<th>Customer</th>

<th>Username</th>

<th>Total</th>

<th>Payment Method</th>

<th>Date</th>

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
    $order["fullname"]
);
?>

</strong>

</td>


<td>

<?php
echo htmlspecialchars(
    $order["username"]
);
?>

</td>


<td>

₱<?php
echo number_format(
    $order["total_amount"],
    2
);
?>

</td>


<td>

<span class="payment-badge">

<?php
echo htmlspecialchars(
    $order["payment_method"]
);
?>

</span>

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

No customer orders yet.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>

</div>

</main>

</body>

</html>