<?php

include "config.php";
include "auth.php";

requireCustomer();


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: shop.php");
    exit();

}


$product_id = intval($_POST["product_id"]);
$buy_quantity = intval($_POST["quantity"]);


if (
    $product_id <= 0 ||
    $buy_quantity <= 0
) {

    header("Location: shop.php");
    exit();

}


$conn->begin_transaction();


try {


    /*
     * Lock the product while purchasing.
     */

    $stmt = $conn->prepare(
        "SELECT id, name, price, quantity
         FROM products
         WHERE id = ?
         FOR UPDATE"
    );


    $stmt->bind_param(
        "i",
        $product_id
    );


    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows !== 1) {

        throw new Exception(
            "Product not found."
        );

    }


    $product =
        $result->fetch_assoc();


    if (
        $product["quantity"] <
        $buy_quantity
    ) {

        throw new Exception(
            "Not enough stock."
        );

    }


    $total =
        $product["price"] *
        $buy_quantity;


    /*
     * Create order
     */

    $stmt = $conn->prepare(
        "INSERT INTO orders
        (user_id, total_amount)
        VALUES (?, ?)"
    );


    $stmt->bind_param(
        "id",
        $_SESSION["user_id"],
        $total
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Could not create order."
        );

    }


    $order_id =
        $conn->insert_id;


    /*
     * Save order item
     */

    $stmt = $conn->prepare(
        "INSERT INTO order_items
        (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)"
    );


    $stmt->bind_param(
        "iiid",
        $order_id,
        $product_id,
        $buy_quantity,
        $product["price"]
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Could not save order item."
        );

    }


    /*
     * Reduce stock
     */

    $new_stock =
        $product["quantity"] -
        $buy_quantity;


    $stmt = $conn->prepare(
        "UPDATE products
         SET quantity = ?
         WHERE id = ?"
    );


    $stmt->bind_param(
        "ii",
        $new_stock,
        $product_id
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Could not update stock."
        );

    }


    $conn->commit();


    header(
        "Location: my_orders.php?success=1"
    );

    exit();


} catch (Exception $e) {


    $conn->rollback();


    $_SESSION["buy_error"] =
        $e->getMessage();


    header(
        "Location: shop.php"
    );

    exit();

}

?>