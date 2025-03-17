<?php
//Adatbázis kapcsolat létrehozása
include "db_conn.php";

//Bevétel kiszámítása
$sql_revenue = "
    SELECT SUM(s.amount * CAST(REPLACE(REPLACE(r.price, ' Ft', ''), ' ', '') AS UNSIGNED)) AS total_revenue
    FROM sales s
    JOIN recipes r ON s.name = r.name
    WHERE s.amount IS NOT NULL AND r.price IS NOT NULL
";

$result_revenue = $conn->query($sql_revenue);

if ($result_revenue) {
    $row = $result_revenue->fetch_assoc();
    $total_revenue = $row["total_revenue"] ?? 0;
    echo "Az elmúlt hét teljes árbevétele: " . number_format($total_revenue, 0, ",", " ") . " Ft\n";
} else {
    die("Hiba a bevétel számításában: " . $conn->error);
}

//Alapanyagok egységárának kiszámítása és kilistázása

$unit_prices = [];

$sql_prices = "SELECT name, amount, price FROM wholesale_prices";
$result_prices = $conn->query($sql_prices);

if ($result_prices->num_rows > 0) {
    //echo "\nAlapanyagok egységára:\n";
    while ($row = $result_prices->fetch_assoc()) {
        $name = $row['name'];
        $amount = explode(" ", $row['amount'])[0];
        $price = $row['price'];

        if (strpos($row['amount'], 'kg') !== false) {
            $unit_prices[$name] = $price / $amount; // Ft/kg
            //echo "- $name: " . number_format($unit_prices[$name], 0, ",", " ") . " Ft/kg\n";
        } elseif (strpos($row['amount'], 'l') !== false) {
            $unit_prices[$name] = $price / $amount; // Ft/liter
            //echo "- $name: " . number_format($unit_prices[$name], 0, ",", " ") . " Ft/l\n";
        } elseif (strpos($row['amount'], 'pc') !== false) {
            $unit_prices[$name] = $price / $amount; // Ft/db
            //echo "- $name: " . number_format($unit_prices[$name], 0, ",", " ") . " Ft/db\n";
        }
    }
} else {
    die("Nincsenek alapanyagok az adatbázisban.");
}

//Sütemények összetevőinek lekérdezése és költségének kiszámítása
$recipes = [];

$sql_recipes = "SELECT id, name FROM recipes";
$result_recipes = $conn->query($sql_recipes);

if ($result_recipes->num_rows > 0) {
    while ($row = $result_recipes->fetch_assoc()) {
        $recipe_id = $row['id'];
        $recipe_name = $row['name'];
        $recipes[$recipe_name] = ['ingredients' => []];

        $sql_ingredients = "SELECT name, amount FROM ingredients WHERE recipe_id = $recipe_id";
        $result_ingredients = $conn->query($sql_ingredients);

        if ($result_ingredients->num_rows > 0) {
            while ($ingredient = $result_ingredients->fetch_assoc()) {
                $recipes[$recipe_name]['ingredients'][] = $ingredient;
            }
        }
    }
} else {
    die("Nincsenek sütemények az adatbázisban.");
}

//Költség kiszámítása egy süteményhez
function calculate_cost($ingredients, $unit_prices) {
    $cost = 0;
    foreach ($ingredients as $ingredient) {
        $name = $ingredient['name'];
        $amount = explode(" ", $ingredient['amount'])[0];
        $unit = explode(" ", $ingredient['amount'])[1];

        if ($unit == 'kg') {
            $cost += $amount * $unit_prices[$name];
        } elseif ($unit == 'g') {
            $cost += ($amount / 1000) * $unit_prices[$name]; //Grammot kilogrammra váltjuk
        } elseif ($unit == 'l') {
            $cost += $amount * $unit_prices[$name];
        } elseif ($unit == 'ml') {
            $cost += ($amount / 1000) * $unit_prices[$name]; //Millilitert literre váltjuk
        } elseif ($unit == 'pc') {
            $cost += $amount * $unit_prices[$name];
        }
    }
    return $cost;
}
/*
echo "\nSütemények költsége:\n";
foreach ($recipes as $recipe_name => $recipe) {
    $cost = calculate_cost($recipe['ingredients'], $unit_prices);
    echo "- $recipe_name: " . number_format($cost, 0, ",", " ") . " Ft\n";

    //Összetevők részletes kiírása
    foreach ($recipe['ingredients'] as $ingredient) {
        $name = $ingredient['name'];
        $amount = $ingredient['amount'];
        echo "  - $name: $amount\n";
    }
}*/

//Utolsó hét eladásainak alapanyagköltségének kiszámítása
$sales_of_last_week = [];

$sql_sales = "SELECT name, amount FROM sales";
$result_sales = $conn->query($sql_sales);

if ($result_sales->num_rows > 0) {
    //echo "\nUtolsó hét eladásai:\n";
    while ($row = $result_sales->fetch_assoc()) {
        $sales_of_last_week[] = $row;
       // echo "- {$row['name']}: {$row['amount']} db\n";
    }
} else {
    die("Nincsenek eladások az utolsó hétre.");
}

$total_cost = 0;
foreach ($sales_of_last_week as $sale) {
    $recipe_name = $sale['name'];
    $amount = $sale['amount'];
    $cost = calculate_cost($recipes[$recipe_name]['ingredients'], $unit_prices);
    $total_cost += $cost * $amount;
}

echo "\nAz utolsó hét alapanyagköltsége: " . number_format($total_cost, 0, ",", " ") . " Ft\n";

//Profit kiszámítása
$profit = $total_revenue - $total_cost;
echo "Az utolsó hét profitja: " . number_format($profit, 0, ",", " ") . " Ft\n";

//Adatbázis kapcsolat lezárása
$conn->close();
?>