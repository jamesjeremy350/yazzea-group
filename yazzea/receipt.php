<?php

include "config.php";
include "auth.php";

requireCustomer();


$order_id =
    intval(
        $_GET["order_id"] ?? 0
    );


if ($order_id <= 0) {

    header("Location: my_orders.php");
    exit();

}


$stmt =
    $conn->prepare(
        "SELECT
            id,
            total_amount,
            payment_method,
            order_date
         FROM orders
         WHERE id = ?
         AND user_id = ?"
    );


$stmt->bind_param(
    "ii",
    $order_id,
    $_SESSION["user_id"]
);


$stmt->execute();

$order_result =
    $stmt->get_result();


if (
    $order_result->num_rows !== 1
) {

    header("Location: my_orders.php");
    exit();
}


$order =
    $order_result->fetch_assoc();


$stmt =
    $conn->prepare(
        "SELECT
            products.name,
            order_items.quantity,
            order_items.price
         FROM order_items
         INNER JOIN products
         ON products.id =
            order_items.product_id
         WHERE order_items.order_id = ?"
    );


$stmt->bind_param(
    "i",
    $order_id
);


$stmt->execute();

$items =
    $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Yazzea - Receipt
</title>

<link rel="stylesheet"
href="style.css">

</head>

<body>

<main class="receipt-page">

<div class="receipt-card">

<div class="receipt-logo">
    Yazzea
</div>


<h1>
    Order Receipt
</h1>


<p class="receipt-muted">

Thank you for shopping
with Yazzea 💜

</p>


<div class="receipt-meta">

<span>

Order #<?php
echo $order["id"];
?>

</span>


<span>

<?php
echo date(
    "M d, Y h:i A",
    strtotime(
        $order["order_date"]
    )
);
?>

</span>

</div>


<div class="receipt-customer">

Customer:

<strong>

<?php
echo htmlspecialchars(
    $_SESSION["fullname"]
);
?>

</strong>

</div>


<div class="receipt-items">

<?php while (
    $item =
    $items->fetch_assoc()
): ?>

<div class="summary-row">

<span>

<?php
echo htmlspecialchars(
    $item["name"]
);
?>

×

<?php
echo $item["quantity"];
?>

</span>


<strong>

₱<?php
echo number_format(
    $item["price"] *
    $item["quantity"],
    2
);
?>

</strong>

</div>

<?php endwhile; ?>

</div>


<div class="summary-row">

<span>
    Payment Method
</span>

<strong>

<?php
echo htmlspecialchars(
    $order["payment_method"]
);
?>

</strong>

</div>


<div
class="summary-row total-row"
>

<span>
    Total
</span>

<strong>

₱<?php
echo number_format(
    $order["total_amount"],
    2
);
?>

</strong>

</div>


<div class="receipt-actions">

<button
onclick="window.print()"
>
    🖨️ Print Receipt
</button>


<a
href="shop.php"
class="add-button"
>
    Continue Shopping
</a>

</div>

</div>

</main>

</body>

</html>