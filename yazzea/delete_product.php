<?php

include "config.php";
include "auth.php";

requireAdmin();


if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: admin.php");
    exit();

}


$id = intval($_GET["id"]);


$stmt = $conn->prepare(
    "DELETE FROM products
     WHERE id = ?"
);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$stmt->close();


header("Location: admin.php");

exit();

?>