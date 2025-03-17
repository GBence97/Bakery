<?php
//Adatbázis kapcsolódás
include "db_conn.php";

//Rendelési mennyiségek
$orders = [
    "Francia krémes" => 300,
    "Rákóczi túrós" => 200,
    "Képviselőfánk" => 300,
    "Isler" => 100,
    "Tiramisu" => 150,
];

$total_cost = 0; //Összes alapanyagköltség
$total_revenue = 0; //Összes bevétel
$total_profit = 0; //Összes profit

//Nagyker árak lekérdezése
$sql_wholesale = "SELECT name, amount, price FROM wholesale_prices";
$result_wholesale = $conn->query($sql_wholesale);

$unit_prices = []; //Alapanyagok egységárai

if ($result_wholesale->num_rows > 0) {
    while ($row = $result_wholesale->fetch_assoc()) {
        $name = $row['name'];
        //Mennyiség és az összeg végéről a karakter tisztítása
        $amount = (float)preg_replace('/[^0-9.]/', '', $row['amount']); 
        $price = (float)preg_replace('/[^0-9.]/', '', $row['price']); 

        //Mértékegység meghatározása
        if (strpos($row['amount'], 'kg') !== false) {
            $unit_prices[$name] = $price / $amount; //Ft/kg
        } elseif (strpos($row['amount'], 'l') !== false) {
            $unit_prices[$name] = $price / $amount; //Ft/liter
        } elseif (strpos($row['amount'], 'pc') !== false) {
            $unit_prices[$name] = $price / $amount; //Ft/db
        }
    }
} else {
    die("Nincsenek alapanyagok az adatbázisban.");
}

//Összes rendelésen végig megy
foreach ($orders as $product_name => $quantity) {
    //Termék adatainak lekérdezése (ár)
    $sql_recipe = "SELECT id, price FROM recipes WHERE name = '$product_name'";
    $result_recipe = $conn->query($sql_recipe);

    if ($result_recipe->num_rows > 0) {
        $row_recipe = $result_recipe->fetch_assoc();
        $recipe_id = $row_recipe["id"];
        $price = (int)preg_replace('/[^0-9]/', '', $row_recipe["price"]); //Ár tisztítása

        //Bevétel számítása (ár * mennyiség)
        $revenue = $price * $quantity;
        $total_revenue += $revenue;

        //Alapanyagköltség számítása
        $sql_ingredients = "SELECT name, amount FROM ingredients WHERE recipe_id = $recipe_id";
        $result_ingredients = $conn->query($sql_ingredients);

        $recipe_cost = 0; //Alapanyagköltség egy termékhez

        if ($result_ingredients->num_rows > 0) {
            while ($row_ingredient = $result_ingredients->fetch_assoc()) {
                $ingredient_name = $row_ingredient["name"];
                $required_amount = $row_ingredient["amount"];

                //Mennyiség és mértékegység szétválasztása
                $amount_parts = explode(" ", $required_amount);
                $amount_value = (float)$amount_parts[0]; //Mennyiség (szám)
                $amount_unit = $amount_parts[1]; //Mértékegység (kg, g, l, ml, pc)

                //Mértékegység átváltása
                if ($amount_unit == 'g') {
                    $amount_value /= 1000; //Grammot kilogrammra váltjuk
                } elseif ($amount_unit == 'ml') {
                    $amount_value /= 1000; //Millilitert literre váltjuk
                }

                //Alapanyagköltség számítása
                if (isset($unit_prices[$ingredient_name])) {
                    $ingredient_cost = $amount_value * $unit_prices[$ingredient_name];
                    $recipe_cost += $ingredient_cost;
                }
            }
        }

        //Teljes alapanyagköltség a rendelt mennyiséghez
        $total_recipe_cost = $recipe_cost * $quantity;
        $total_cost += $total_recipe_cost;

        //Profit számítása
        $profit = $revenue - $total_recipe_cost;
        $total_profit += $profit;

        //Eredmény kiírása
        echo "Termék: $product_name, Mennyiség: $quantity db, Bevétel: $revenue Ft, Alapanyagköltség: $total_recipe_cost Ft, Profit: $profit Ft\n";
    } else {
        echo "Nincs adat a termékhez: $product_name\n";
    }
}

//Összesített eredmény kiírása
echo "\nÖsszes bevétel: $total_revenue Ft\n";
echo "Összes alapanyagköltség: $total_cost Ft\n";
echo "Összes profit: $total_profit Ft\n";

//Kapcsolódás bezárása
$conn->close();
?>