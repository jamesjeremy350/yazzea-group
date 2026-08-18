<?php

include "config.php";
include "auth.php";

requireAdmin();


/* ==========================
   TOTAL PRODUCTS
========================== */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM products"
);

$totalProducts =
    $result->fetch_assoc()["total"];


/* ==========================
   TOTAL ORDERS
========================== */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM orders"
);

$totalOrders =
    $result->fetch_assoc()["total"];


/* ==========================
   TOTAL SALES
========================== */

$result = $conn->query(
    "SELECT COALESCE(SUM(total_amount), 0) AS total
     FROM orders"
);

$totalSales =
    $result->fetch_assoc()["total"];


/* ==========================
   PRODUCTS SOLD
========================== */

$result = $conn->query(
    "SELECT COALESCE(SUM(quantity), 0) AS total
     FROM order_items"
);

$totalSold =
    $result->fetch_assoc()["total"];


/* ==========================
   PIE CHART DATA
========================== */

$chartLabels = [];
$chartValues = [];

$chartQuery = $conn->query(
    "SELECT
        products.name,
        SUM(order_items.quantity) AS sold
     FROM order_items
     INNER JOIN products
     ON products.id = order_items.product_id
     GROUP BY products.id, products.name
     HAVING sold > 0
     ORDER BY sold DESC"
);

while ($row = $chartQuery->fetch_assoc()) {

    $chartLabels[] = $row["name"];

    $chartValues[] = (int)$row["sold"];
}


/* ==========================
   PRODUCTS TABLE
========================== */

$products = $conn->query(
    "SELECT *
     FROM products
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Yazzea - Admin</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<nav class="navbar">

    <div class="brand">
        Yazzea
    </div>

    <div class="nav-right">

        <span>
            Admin:
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

            <h1>
                Admin Dashboard
            </h1>

            <p>
                Manage Yazzea products and sales.
            </p>

        </div>

        <a
            href="add_product.php"
            class="add-button"
        >
            + Add Product
        </a>

    </div>


    <!-- STATISTICS -->

    <div class="stats">

        <div class="stat-card">

            <div class="stat-number">
                <?php echo $totalProducts; ?>
            </div>

            <div class="stat-label">
                Products
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $totalSold; ?>
            </div>

            <div class="stat-label">
                Items Sold
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $totalOrders; ?>
            </div>

            <div class="stat-label">
                Orders
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                ₱<?php
                echo number_format(
                    $totalSales,
                    2
                );
                ?>
            </div>

            <div class="stat-label">
                Total Sales
            </div>

        </div>

    </div>


    <!-- CHART -->

    <div class="chart-card">

        <div>

            <h2>
                Product Sales
            </h2>

            <p class="chart-subtitle">
                Products that have already been sold
            </p>

        </div>


        <?php if (count($chartLabels) > 0): ?>

            <div class="chart-wrapper">

                <canvas id="salesChart"></canvas>

            </div>

            <div
                id="chartLegend"
                class="chart-legend"
            ></div>

        <?php else: ?>

            <div class="no-sales">

                <div class="empty-icon">
                    📊
                </div>

                <h3>
                    No sales yet
                </h3>

                <p>
                    The pie chart will appear after customers
                    buy products.
                </p>

            </div>

        <?php endif; ?>

    </div>


    <!-- PRODUCTS -->

    <div class="section-header">

        <div>

            <h2>
                Products
            </h2>

        </div>

        <a
            href="orders.php"
            class="orders-button"
        >
            View Orders
        </a>

    </div>


    <div class="table-container">

        <table>

            <thead>

            <tr>

                <th>ID</th>

                <th>Product</th>

                <th>Category</th>

                <th>Price</th>

                <th>Stock</th>

                <th>Actions</th>

            </tr>

            </thead>


            <tbody>

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

                        <?php if ($product["quantity"] <= 5): ?>

                            <span class="low-stock">
                                Low
                            </span>

                        <?php endif; ?>

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
                                onclick="return confirm('Delete this product?');"
                            >
                                Delete
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</main>


<?php if (count($chartLabels) > 0): ?>

<script>

const labels =
    <?php echo json_encode($chartLabels); ?>;

const values =
    <?php echo json_encode($chartValues); ?>;


const canvas =
    document.getElementById("salesChart");

const ctx =
    canvas.getContext("2d");


const colors = [
    "#8e5bb7",
    "#c084d4",
    "#6d4c91",
    "#b06ab3",
    "#9c6ade",
    "#7652a8",
    "#d09ad8",
    "#5d407c"
];


let total = values.reduce(
    (sum, value) => sum + value,
    0
);


let startAngle = -Math.PI / 2;


values.forEach((value, index) => {

    const sliceAngle =
        (value / total) *
        Math.PI *
        2;


    ctx.beginPath();

    ctx.moveTo(
        200,
        200
    );

    ctx.arc(
        200,
        200,
        160,
        startAngle,
        startAngle + sliceAngle
    );

    ctx.closePath();

    ctx.fillStyle =
        colors[index % colors.length];

    ctx.fill();


    startAngle += sliceAngle;

});


const legend =
    document.getElementById("chartLegend");


labels.forEach((label, index) => {

    const item =
        document.createElement("div");

    item.className =
        "legend-item";


    item.innerHTML = `
        <span
            class="legend-color"
            style="
                background:
                ${colors[index % colors.length]}
            "
        ></span>

        <span>
            ${label}
            -
            ${values[index]} sold
        </span>
    `;


    legend.appendChild(item);

});

</script>

<?php endif; ?>

</body>
</html>