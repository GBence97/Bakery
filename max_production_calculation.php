<?php
//Adatbázis kapcsolódás
include "db_conn.php";

//Lekérdezzük az összes termék receptjét
$sql_recipes = "SELECT DISTINCT recipe_id FROM ingredients";
$result_recipes = $conn->query($sql_recipes);

if ($result_recipes->num_rows > 0) {
    //Végigmegyünk az összes terméken
    while ($row_recipe = $result_recipes->fetch_assoc()) {
        $recipe_id = $row_recipe["recipe_id"];
        $max_production = PHP_INT_MAX;


        //Termék nevének lekérdezése a recept táblából
        $sql_recipe_name = "SELECT name FROM recipes WHERE id = $recipe_id";
        $result_recipe_name = $conn->query($sql_recipe_name);

        if ($result_recipe_name->num_rows > 0) {
            $row_recipe_name = $result_recipe_name->fetch_assoc();
            $recipe_name = $row_recipe_name["name"];
        } else {
            $recipe_name = "Ismeretlen termék";
        }


        //Hozzávaló lekérdezése
        $sql_ingredients = "SELECT name, amount FROM ingredients WHERE recipe_id = $recipe_id";
        $result_ingredients = $conn->query($sql_ingredients);


        if ($result_ingredients->num_rows > 0) {
            //Végigmegyünk az összes hozzávalón
            while ($row_ingredient = $result_ingredients->fetch_assoc()) {
                $ingredient_name = $row_ingredient["name"];
                $required_amount = $row_ingredient["amount"];

                //Mértékegység eltávolítása
                $required_amount = (int)preg_replace('/[^0-9]/', '', $required_amount);

                //Raktárkészlet lekérdezése
                $sql_inventory = "SELECT amount FROM inventory WHERE name = '$ingredient_name'";
                $result_inventory = $conn->query($sql_inventory);

                if ($result_inventory->num_rows > 0) {
                    $row_inventory = $result_inventory->fetch_assoc();
                    $available_amount = $row_inventory["amount"];

                    //Mértékegység eltávolítása
                    $available_amount = (int)preg_replace('/[^0-9]/', '', $available_amount);

                    //Átváltás grammra vagy milliliterre a hozzávaló neve alapján
                    if (strpos($ingredient_name, 'flour') !== false || strpos($ingredient_name, 'suggluten-free flour') !== false || strpos($ingredient_name, 'sugar') !== false || strpos($ingredient_name, 'butter') !== false || strpos($ingredient_name, 'vanilin sugar') !== false || strpos($ingredient_name, 'fruit') !== false || strpos($ingredient_name, 'chocolate') !== false) {
                        $available_amount *= 1000; // 1 kg = 1000 g

                    } elseif (strpos($ingredient_name, 'milk') !== false || strpos($ingredient_name, 'soy-milk') !== false) {
                        $available_amount *= 1000; // 1 l = 1000 ml
                    }

                    //hozzávalókat és a mennyiségekek kiírása
                    //echo "Termék ID: $recipe_id, Hozzávaló: $ingredient_name, Szükséges: $required_amount g/ml, Raktár: $available_amount g/ml\n";

                    //Max termék kiszámítása
                    if ($required_amount > 0) {
                        $possible_production = intdiv($available_amount, $required_amount);
                    } else {
                        $possible_production = 0; 
                    }

                    //A maximális gyártható mennyiség a legkisebb érték lesz
                    $max_production = min($max_production, $possible_production);
                } else {
                    //Ha nincs raktárkészlet, akkor nem gyártható a termék
                    $max_production = 0;
                    break;
                }
            }
        }

        
        echo "Termék név: $recipe_name, Maximális gyártható mennyiség: $max_production db\n";
    }
} else {
    echo "Nincsenek termékek az adatbázisban.\n";
}

//Kapcsolódás bezárása
$conn->close();
?>