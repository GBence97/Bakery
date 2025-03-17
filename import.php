<?php
//Kapcsolódás az adatbázishoz
$conn = new mysqli("localhost", "root", "", "bakery_db");

//Ellenőrizzük a kapcsolatot
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}


//JSON fájl beolvasása
$json_data = file_get_contents("data.json");
$data = json_decode($json_data, true);

//Receptek importálása
foreach ($data['recipes'] as $recipe) {
    $stmt = $conn->prepare("INSERT INTO recipes (name, price, lactoseFree, glutenFree) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $recipe['name'], $recipe['price'], $recipe['lactoseFree'], $recipe['glutenFree']);
    $stmt->execute();
    $recipe_id = $stmt->insert_id;

    //Hozzávalók importálása
    foreach ($recipe['ingredients'] as $ingredient) {
        $stmt = $conn->prepare("INSERT INTO ingredients (recipe_id, name, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $recipe_id, $ingredient['name'], $ingredient['amount']);
        $stmt->execute();
    }
}

//Készlet importálása
foreach ($data['inventory'] as $item) {
    $stmt = $conn->prepare("INSERT INTO inventory (name, amount) VALUES (?, ?)");
    $stmt->bind_param("ss", $item['name'], $item['amount']);
    $stmt->execute();
}

//Eladások importálása
foreach ($data['salesOfLastWeek'] as $sale) {
    $stmt = $conn->prepare("INSERT INTO sales (name, amount) VALUES (?, ?)");
    $stmt->bind_param("si", $sale['name'], $sale['amount']);
    $stmt->execute();
}

//Nagyker árak importálása
foreach ($data['wholesalePrices'] as $price) {
    $stmt = $conn->prepare("INSERT INTO wholesale_prices (name, amount, price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $price['name'], $price['amount'], $price['price']);
    $stmt->execute();
}

echo "JSON adatok sikeresen importálva!";
$conn->close();
?>
