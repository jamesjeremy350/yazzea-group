<?php

include "config.php";

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$search = "";

if (isset($_GET["search"])) {

    $search = trim($_GET["search"]);

}

if ($search != "") {

    $term = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT *
         FROM products
         WHERE name LIKE ?
         OR category LIKE ?
         OR description LIKE ?
         ORDER BY id DESC"
    );

    $stmt->bind_param(
        "sss",
        $term,
        $term,
        $term
    );

    $stmt->execute();

    $products = $stmt->get_result();

} else {

    $products = $conn->query(
        "SELECT *
         FROM products
         ORDER BY id DESC"
    );

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Yazzea - Dashboard</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<nav class="navbar">

    <div class="brand">
        Yazzea
    </div>

    <div class="nav-right">

        <span>
            Hello,
            <?php
            echo htmlspecialchars(
                $_SESSION["fullname"]
            );
            ?>
        </span>

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

            <h1>Product Management</h1>

            <p>
                Manage your Yazzea products.
            </p>

        </div>

        <a
            href="add_product.php"
            class="add-button"
        >
            + Add Product
        </a>

    </div>


    <form
        method="GET"
        class="search-box"
    >

        <input
            type="text"
            name="search"
            placeholder="Search product..."
            value="<?php
            echo htmlspecialchars($search);
            ?>"
        >

        <button type="submit">
            Search
        </button>

        <?php if ($search != ""): ?>

            <a
                href="dashboard.php"
                class="clear-button"
            >
                Clear
            </a>

        <?php endif; ?>

    </form>


    <div class="table-container">

        <table>

            <thead>

            <tr>

                <th>ID</th>

                <th>Product Name</th>

                <th>Category</th>

                <th>Price</th>

                <th>Quantity</th>

                <th>Description</th>

                <th>Actions</th>

            </tr>

            </thead>

            <tbody>

            <?php if ($products->num_rows > 0): ?>

                <?php while ($product = $products->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php
                            echo $product["id"];
                            ?>
                        </td>

                        <td>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $product["name"]
                                );
                                ?>
                            </strong>

                        </td>

                        <td>

                            <span class="category">

                                <?php
                                echo htmlspecialchars(
                                    $product["category"]
                                );
                                ?>

                            </span>

                        </td>

                        <td>

                            ₱<?php
                            echo number_format(
                                $product["price"],
                                2
                            );
                            ?>

                        </td>

                        <td>
                            <?php
                            echo $product["quantity"];
                            ?>
                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $product["description"]
                            );
                            ?>

                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="edit_product.php?id=<?php echo $product["id"]; ?>"
                                    class="edit-button"
                                >
                                    Edit
                                </a>

                                <a
                                    href="delete_product.php?id=<?php echo $product["id"]; ?>"
                                    class="delete-button"
                                    onclick="return confirm('Are you sure you want to delete this product?');"
                                >
                                    Delete
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        class="no-data"
                    >
                        No products found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>

</body>
</html>