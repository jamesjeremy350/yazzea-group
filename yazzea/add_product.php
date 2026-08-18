<?php

include "config.php";
include "auth.php";

requireAdmin();

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
            "INSERT INTO products
            (name, category, price, quantity, description)
            VALUES (?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "ssdis",
            $name,
            $category,
            $price,
            $quantity,
            $description
        );


        if ($stmt->execute()) {

            header("Location: admin.php");
            exit();

        } else {

            $error =
                "Unable to add product.";

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

<title>Add Product - Yazzea</title>

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
    Add Product
</h1>

<p>
    Add a new item to your shop.
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
    placeholder="Product name"
    required
>


<label>
    Category *
</label>

<select name="category" required>

<option value="">
    Select category
</option>

<option value="Beauty">
    Beauty
</option>

<option value="Bags">
    Bags
</option>

<option value="Clothes">
    Clothes
</option>

<option value="Accessories">
    Accessories
</option>

<option value="Home">
    Home
</option>

<option value="Others">
    Others
</option>

</select>


<label>
    Price *
</label>

<input
    type="number"
    name="price"
    step="0.01"
    min="0"
    placeholder="0.00"
    required
>


<label>
    Quantity *
</label>

<input
    type="number"
    name="quantity"
    min="0"
    placeholder="0"
    required
>


<label>
    Description
</label>

<textarea
    name="description"
    placeholder="Product description"
></textarea>


<div class="form-actions">

<a
    href="admin.php"
    class="cancel-button"
>
    Cancel
</a>

<button type="submit">
    Add Product
</button>

</div>

</form>

</div>

</div>

</body>
</html>