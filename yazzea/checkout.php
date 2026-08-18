<?php

include "config.php";
include "auth.php";

requireCustomer();


if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


if (empty($_SESSION["cart"])) {

    header("Location: cart.php");
    exit();

}


$payment_methods = [
    "GCash",
    "Cash on Delivery (COD)",
    "Debit/Credit Card",
    "PayPal"
];


$error = "";

$items = [];

$total = 0;


$ids =
    array_keys(
        $_SESSION["cart"]
    );


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

    $qty =
        (int)$_SESSION["cart"]
        [$product["id"]];


    $product["cart_quantity"] =
        $qty;


    $product["line_total"] =
        (float)$product["price"]
        * $qty;


    $items[] =
        $product;


    $total +=
        $product["line_total"];
}


if (
    count($items) !==
    count($_SESSION["cart"])
) {

    $error =
        "One or more products in your cart are no longer available.";
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    $error === ""
) {

    $payment_method =
        $_POST["payment_method"] ?? "";


    if (
        !in_array(
            $payment_method,
            $payment_methods,
            true
        )
    ) {

        $error =
            "Please select a payment method.";

    } else {

        $conn->begin_transaction();


        try {

            $locked_items = [];

            $locked_total = 0;


            foreach (
                $_SESSION["cart"]
                as $product_id => $qty
            ) {

                $product_id =
                    (int)$product_id;

                $qty =
                    (int)$qty;


                $lock =
                    $conn->prepare(
                        "SELECT id, name, price, quantity
                         FROM products
                         WHERE id = ?
                         FOR UPDATE"
                    );


                $lock->bind_param(
                    "i",
                    $product_id
                );

                $lock->execute();


                $locked_result =
                    $lock->get_result();


                if (
                    $locked_result
                    ->num_rows !== 1
                ) {

                    throw new Exception(
                        "A product in your cart no longer exists."
                    );
                }


                $product =
                    $locked_result
                    ->fetch_assoc();


                if (
                    $qty <= 0 ||
                    $product["quantity"] < $qty
                ) {

                    throw new Exception(
                        "Not enough stock for " .
                        $product["name"] .
                        "."
                    );
                }


                $product["cart_quantity"] =
                    $qty;


                $product["line_total"] =
                    (float)$product["price"]
                    * $qty;


                $locked_items[] =
                    $product;


                $locked_total +=
                    $product["line_total"];
            }


            $stmt =
                $conn->prepare(
                    "INSERT INTO orders
                    (
                        user_id,
                        total_amount,
                        payment_method
                    )
                    VALUES (?, ?, ?)"
                );


            $stmt->bind_param(
                "ids",
                $_SESSION["user_id"],
                $locked_total,
                $payment_method
            );


            if (!$stmt->execute()) {

                throw new Exception(
                    "Could not create the order."
                );
            }


            $order_id =
                $conn->insert_id;


            foreach (
                $locked_items as $product
            ) {


                $item_stmt =
                    $conn->prepare(
                        "INSERT INTO order_items
                        (
                            order_id,
                            product_id,
                            quantity,
                            price
                        )
                        VALUES (?, ?, ?, ?)"
                    );


                $item_stmt->bind_param(
                    "iiid",
                    $order_id,
                    $product["id"],
                    $product["cart_quantity"],
                    $product["price"]
                );


                if (
                    !$item_stmt->execute()
                ) {

                    throw new Exception(
                        "Could not save an order item."
                    );
                }


                $new_stock =
                    (int)$product["quantity"]
                    -
                    (int)$product["cart_quantity"];


                $stock_stmt =
                    $conn->prepare(
                        "UPDATE products
                         SET quantity = ?
                         WHERE id = ?"
                    );


                $stock_stmt->bind_param(
                    "ii",
                    $new_stock,
                    $product["id"]
                );


                if (
                    !$stock_stmt->execute()
                ) {

                    throw new Exception(
                        "Could not update product stock."
                    );
                }
            }


            $conn->commit();


            $_SESSION["cart"] = [];


            header(
                "Location: receipt.php?order_id=" .
                $order_id
            );

            exit();


        } catch (
            Exception $e
        ) {

            $conn->rollback();

            $error =
                $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Yazzea - Checkout
</title>

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
href="cart.php"
class="back-button"
>
    ← Cart
</a>

<a
href="logout.php"
class="logout"
>
    Logout
</a>

</div>

</nav>


<main
class="container narrow-container"
>

<div class="page-header">

<div>

<h1>
    Checkout
</h1>

<p>
    Choose your payment method.
</p>

</div>

</div>


<?php if (
    $error !== ""
): ?>

<div class="error">

<?php
echo htmlspecialchars(
    $error
);
?>

</div>

<?php endif; ?>


<div class="checkout-card">

<h2>
    Order Items
</h2>


<?php foreach (
    $items as $item
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
echo $item["cart_quantity"];
?>

</span>


<strong>

₱<?php
echo number_format(
    $item["line_total"],
    2
);
?>

</strong>

</div>

<?php endforeach; ?>


<div
class="summary-row total-row"
>

<span>
    Total
</span>

<strong>

₱<?php
echo number_format(
    $total,
    2
);
?>

</strong>

</div>


<form method="POST">

<h2 class="payment-title">
    Payment Method
</h2>


<p class="small-note">

For this school-project version,
the selected payment method is
saved with the order.

No card, GCash, or PayPal
login information is collected.

</p>


<div class="payment-options">

<?php foreach (
    $payment_methods as $method
): ?>

<label class="payment-option">

<input
type="radio"
name="payment_method"
value="<?php
echo htmlspecialchars(
    $method
);
?>"
required
>

<span>

<?php
echo htmlspecialchars(
    $method
);
?>

</span>

</label>

<?php endforeach; ?>

</div>


<button
type="submit"
class="checkout-button full-button"
>

Place Order

</button>

</form>

</div>

</main>

</body>
</html>