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
    "SELECT *
     FROM products
     WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    header("Location: admin.php");
    exit();

}


$product = $result->fetch_assoc();

$stmt->close();


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $category = trim($_POST["category"]);
    $price = trim($_POST["price"]);
    $quantity = trim($_POST["quantity"]);
    $description = trim($_POST["description"]);


    if (
        $name === "" ||
        $category === "" ||
        $price === "" ||
        $quantity === ""
    ) {

        $error =
            "Please fill in all required fields.";

    } elseif (!is_numeric($price)) {

        $error =
            "Price must be a number.";

    } elseif (!is_numeric($quantity)) {

        $error =
            "Quantity must be a number.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE products
             SET name = ?,
                 category = ?,
                 price = ?,
                 quantity = ?,
                 description = ?
             WHERE id = ?"
        );


        $stmt->bind_param(
            "ssdisi",
            $name,
            $category,
            $price,
            $quantity,
            $description,
            $id
        );


        if ($stmt->execute()) {

            header("Location: admin.php");
            exit();

        } else {

            $error =
                "Unable to update product.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Product - Yazzea</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<nav class="navbar">

<div class="brand">
    Yazzea
</div>

<a
    href="admin.php"
    class="back-button"
>
    ← Back
</a>

</nav>


<div class="form-container">

<div class="form-card">

<h1>
    Edit Product
</h1>

<p>
    Update product information.
</p>


<?php if ($error !== ""): ?>

<div class="error">
    <?php echo htmlspecialchars($error); ?>
</div>

<?php endif; ?>


<form method="POST">

<label>
    Product Name *
</label>

<input
    type="text"
    name="name"
    value="<?php
    echo htmlspecialchars(
        $product["name"]
    );
    ?>"
    required
>


<label>
    Category *
</label>

<select
    name="category"
    required
>

<?php

$categories = [
    "Beauty",
    "Bags",
    "Clothes",
    "Accessories",
    "Home",
    "Others"
];


foreach ($categories as $category) {

    $selected =
        $product["category"] === $category
        ? "selected"
        : "";

    echo "<option value=\"" .
        htmlspecialchars($category) .
        "\" $selected>" .
        htmlspecialchars($category) .
        "</option>";
}

?>

</select>


<label>
    Price *
</label>

<input
    type="number"
    name="price"
    step="0.01"
    min="0"
    value="<?php
    echo htmlspecialchars(
        $product["price"]
    );
    ?>"
    required
>


<label>
    Quantity *
</label>

<input
    type="number"
    name="quantity"
    min="0"
    value="<?php
    echo htmlspecialchars(
        $product["quantity"]
    );
    ?>"
    required
>


<label>
    Description
</label>

<textarea
    name="description"
><?php
echo htmlspecialchars(
    $product["description"]
);
?></textarea>


<div class="form-actions">

<a
    href="admin.php"
    class="cancel-button"
>
    Cancel
</a>

<button type="submit">
    Save Changes
</button>

</div>

</form>

</div>

</div>

</body>
</html>