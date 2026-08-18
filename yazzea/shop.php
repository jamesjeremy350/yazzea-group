<?php

include "config.php";
include "auth.php";

requireCustomer();


if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


$search =
    trim($_GET["search"] ?? "");

$category =
    trim($_GET["category"] ?? "");


/* ==========================
   GET CATEGORIES
========================== */

$categories = [];

$catResult = $conn->query(
    "SELECT DISTINCT category
     FROM products
     WHERE category <> ''
     ORDER BY category ASC"
);


while (
    $cat =
    $catResult->fetch_assoc()
) {

    $categories[] =
        $cat["category"];
}


/* ==========================
   SEARCH + CATEGORY
========================== */

if (
    $search !== "" ||
    $category !== ""
) {

    $conditions = [];

    $params = [];

    $types = "";


    if ($search !== "") {

        $term =
            "%" . $search . "%";

        $conditions[] =
            "(name LIKE ?
              OR category LIKE ?
              OR description LIKE ?)";

        $params[] = $term;
        $params[] = $term;
        $params[] = $term;

        $types .= "sss";
    }


    if ($category !== "") {

        $conditions[] =
            "category = ?";

        $params[] =
            $category;

        $types .= "s";
    }


    $sql =
        "SELECT *
         FROM products
         WHERE " .
        implode(
            " AND ",
            $conditions
        ) .
        " ORDER BY id DESC";


    $stmt =
        $conn->prepare($sql);

    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $products =
        $stmt->get_result();

} else {

    $products =
        $conn->query(
            "SELECT *
             FROM products
             ORDER BY id DESC"
        );
}


$cart_count =
    array_sum(
        $_SESSION["cart"]
    );

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Yazzea - Shop</title>

<link rel="stylesheet"
href="style.css">

</head>

<body>

<nav class="navbar">

<div class="brand">
    Yazzea
</div>


<div class="nav-right">

<span>
Welcome,
<?php
echo htmlspecialchars(
    $_SESSION["fullname"]
);
?>
</span>


<a
href="cart.php"
class="cart-button"
>
    🛒 Cart
    (<?php echo $cart_count; ?>)
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
    Yazzea Shop
</h1>

<p>
    Browse our products by section.
</p>

</div>

</div>


<?php if (
    isset(
        $_SESSION["cart_success"]
    )
): ?>

<div class="success">

<?php
echo htmlspecialchars(
    $_SESSION["cart_success"]
);

unset(
    $_SESSION["cart_success"]
);
?>

</div>

<?php endif; ?>


<?php if (
    isset(
        $_SESSION["cart_error"]
    )
): ?>

<div class="error">

<?php

echo htmlspecialchars(
    $_SESSION["cart_error"]
);

unset(
    $_SESSION["cart_error"]
);

?>

</div>

<?php endif; ?>


<form
method="GET"
class="search-box"
>

<input
type="text"
name="search"
placeholder="Search products..."
value="<?php
echo htmlspecialchars(
    $search
);
?>"
>

<button
type="submit"
>
Search
</button>


<?php if (
    $search !== "" ||
    $category !== ""
): ?>

<a
href="shop.php"
class="clear-button"
>
    Clear
</a>

<?php endif; ?>

</form>


<!-- CATEGORY BUTTONS -->

<div class="category-tabs">

<a
href="shop.php"
class="category-tab
<?php
echo $category === ""
    ? "active"
    : "";
?>"
>
    All
</a>


<?php foreach (
    $categories as $cat
): ?>

<a
href="shop.php?category=<?php
echo urlencode($cat);
?>"
class="category-tab
<?php
echo $category === $cat
    ? "active"
    : "";
?>"
>

<?php
echo htmlspecialchars(
    $cat
);
?>

</a>

<?php endforeach; ?>

</div>


<!-- PRODUCTS -->

<div class="product-grid">

<?php if (
    $products->num_rows > 0
): ?>


<?php while (
    $product =
    $products->fetch_assoc()
): ?>


<div class="product-card">


<div class="product-image">

<?php if (
    !empty(
        $product["image"]
    )
): ?>

<img
src="images/<?php
echo htmlspecialchars(
    $product["image"]
);
?>"
alt="<?php
echo htmlspecialchars(
    $product["name"]
);
?>"
>

<?php else: ?>

<div class="product-icon">
    🛍️
</div>

<?php endif; ?>

</div>


<span class="category">

<?php
echo htmlspecialchars(
    $product["category"]
);
?>

</span>


<h2>

<?php
echo htmlspecialchars(
    $product["name"]
);
?>

</h2>


<p class="product-description">

<?php
echo htmlspecialchars(
    $product["description"]
);
?>

</p>


<div class="product-bottom">

<strong class="price">

₱<?php
echo number_format(
    $product["price"],
    2
);
?>

</strong>


<span class="stock">

<?php

if (
    $product["quantity"] > 0
) {

    echo
        $product["quantity"] .
        " available";

} else {

    echo "Out of stock";

}

?>

</span>

</div>


<?php if (
    $product["quantity"] > 0
): ?>

<form
action="add_to_cart.php"
method="POST"
class="buy-form"
>

<input
type="hidden"
name="product_id"
value="<?php
echo $product["id"];
?>"
>


<label>
    Quantity
</label>


<input
type="number"
name="quantity"
min="1"
max="<?php
echo $product["quantity"];
?>"
value="1"
required
>


<button
type="submit"
class="buy-button"
>
    🛒 Add to Cart
</button>

</form>


<?php else: ?>

<button
class="buy-button disabled"
disabled
>
    Out of Stock
</button>

<?php endif; ?>


</div>


<?php endwhile; ?>


<?php else: ?>


<div class="no-products">

<h2>
    No products found
</h2>

<p>
    Try another search or section.
</p>

</div>


<?php endif; ?>

</div>

</main>

</body>
</html>