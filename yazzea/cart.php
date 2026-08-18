<?php

include "config.php";
include "auth.php";

requireCustomer();

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action =
        $_POST["action"] ?? "";

    $product_id =
        intval($_POST["product_id"] ?? 0);


    if (
        $action === "remove" &&
        isset($_SESSION["cart"][$product_id])
    ) {

        unset(
            $_SESSION["cart"][$product_id]
        );
    }


    if (
        $action === "update" &&
        isset($_SESSION["cart"][$product_id])
    ) {

        $quantity =
            intval($_POST["quantity"] ?? 1);

        if ($quantity <= 0) {

            unset(
                $_SESSION["cart"][$product_id]
            );

        } else {

            $stmt = $conn->prepare(
                "SELECT quantity
                 FROM products
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "i",
                $product_id
            );

            $stmt->execute();

            $result =
                $stmt->get_result();

            if ($result->num_rows === 1) {

                $stock =
                    (int)$result
                    ->fetch_assoc()["quantity"];

                $_SESSION["cart"][$product_id] =
                    min($quantity, $stock);

                if (
                    $_SESSION["cart"][$product_id]
                    <= 0
                ) {

                    unset(
                        $_SESSION["cart"][$product_id]
                    );
                }
            }
        }
    }

    header("Location: cart.php");
    exit();
}


$cart =
    $_SESSION["cart"];

$items = [];

$grand_total = 0;


if (!empty($cart)) {

    $ids =
        array_keys($cart);

    $placeholders =
        implode(
            ",",
            array_fill(
                0,
                count($ids),
                "?"
            )
        );

    $types =
        str_repeat(
            "i",
            count($ids)
        );


    $stmt = $conn->prepare(
        "SELECT *
         FROM products
         WHERE id IN ($placeholders)
         ORDER BY id DESC"
    );


    $stmt->bind_param(
        $types,
        ...$ids
    );

    $stmt->execute();

    $result =
        $stmt->get_result();


    while (
        $product =
        $result->fetch_assoc()
    ) {

        $quantity =
            (int)$cart[$product["id"]];

        $line_total =
            (float)$product["price"]
            * $quantity;

        $product["cart_quantity"] =
            $quantity;

        $product["line_total"] =
            $line_total;

        $items[] =
            $product;

        $grand_total +=
            $line_total;
    }
}


$cart_count =
    array_sum($_SESSION["cart"]);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Yazzea - Cart</title>

<link rel="stylesheet"
href="style.css">

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
href="my_orders.php"
class="orders-button"
>
    My Orders
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

<div class="page-header">

<div>

<h1>
    🛒 My Cart
</h1>

<p>
    <?php echo $cart_count; ?>
    item(s) in your cart.
</p>

</div>

</div>


<?php if (empty($items)): ?>

<div class="empty-card">

<h2>
    Your cart is empty.
</h2>

<p>
    Add some Yazzea products first.
</p>

<br>

<a
href="shop.php"
class="add-button"
>
    Continue Shopping
</a>

</div>

<?php else: ?>


<div class="cart-layout">

<div class="cart-list">

<?php foreach ($items as $item): ?>

<div class="cart-item">


<div class="cart-item-image">

<?php if (!empty($item["image"])): ?>

<img
src="images/<?php
echo htmlspecialchars(
    $item["image"]
);
?>"
alt="<?php
echo htmlspecialchars(
    $item["name"]
);
?>"
>

<?php else: ?>

🛍️

<?php endif; ?>

</div>


<div class="cart-item-info">

<span class="category">

<?php
echo htmlspecialchars(
    $item["category"]
);
?>

</span>

<h2>

<?php
echo htmlspecialchars(
    $item["name"]
);
?>

</h2>

<p>

₱<?php
echo number_format(
    $item["price"],
    2
);
?>
each

</p>

</div>


<form
method="POST"
class="cart-quantity-form"
>

<input
type="hidden"
name="action"
value="update"
>

<input
type="hidden"
name="product_id"
value="<?php
echo $item["id"];
?>
">

<input
type="number"
name="quantity"
min="1"
max="<?php
echo $item["quantity"];
?>"
value="<?php
echo $item["cart_quantity"];
?>"
>

<button type="submit">
    Update
</button>

</form>


<strong>

₱<?php
echo number_format(
    $item["line_total"],
    2
);
?>

</strong>


<form method="POST">

<input
type="hidden"
name="action"
value="remove"
>

<input
type="hidden"
name="product_id"
value="<?php
echo $item["id"];
?>
">

<button
type="submit"
class="delete-button"
>
    Remove
</button>

</form>


</div>

<?php endforeach; ?>

</div>


<div class="cart-summary">

<h2>
    Order Summary
</h2>

<div class="summary-row">

<span>
    Items
</span>

<strong>
    <?php echo $cart_count; ?>
</strong>

</div>


<div class="summary-row total-row">

<span>
    Total
</span>

<strong>

₱<?php
echo number_format(
    $grand_total,
    2
);
?>

</strong>

</div>


<a
href="checkout.php"
class="checkout-button"
>
    Proceed to Checkout →
</a>

</div>

</div>

<?php endif; ?>

</main>

</body>
</html>