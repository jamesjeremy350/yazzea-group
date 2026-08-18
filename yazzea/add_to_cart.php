<?php

include "config.php";
include "auth.php";

requireCustomer();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: shop.php");
    exit();
}

$product_id = intval($_POST["product_id"] ?? 0);
$quantity = intval($_POST["quantity"] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    header("Location: shop.php");
    exit();
}

$stmt = $conn->prepare(
    "SELECT id, name, quantity
     FROM products
     WHERE id = ?"
);

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $_SESSION["cart_error"] = "Product not found.";

    header("Location: shop.php");
    exit();
}

$product = $result->fetch_assoc();

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$current_quantity =
    $_SESSION["cart"][$product_id] ?? 0;

$new_quantity =
    $current_quantity + $quantity;

if ($new_quantity > (int)$product["quantity"]) {

    $_SESSION["cart_error"] =
        "You cannot add more than the available stock.";

    header("Location: shop.php");
    exit();
}

$_SESSION["cart"][$product_id] =
    $new_quantity;

$_SESSION["cart_success"] =
    $product["name"] .
    " has been added to your cart.";

header("Location: shop.php");

exit();

?>