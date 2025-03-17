<?php
//Adatbázis kapcsolat
include "db_conn.php";

//Laktózmentes termékek lekérdezése
$sql_freelac = "SELECT name, price FROM recipes WHERE lactoseFree = 1";

$result = $conn->query($sql_freelac);

//Eredmények ellenőrzése és kiírása
if($result->num_rows > 0){
    echo "Laktózmentes termékek:\n";
    echo "_____________________\n";


    while($row = $result->fetch_assoc()){
        echo "Termék neve: " .$row["name"] . "\n";
        echo "Ára: " .$row["price"] . "\n";
        //echo "-----------------------------------";
    }

}else{echo"Nincs laktózmentes termék!";}

//Gluténmentes termékek lekérdezése
$sql_freeglu = "SELECT name, price FROM recipes WHERE glutenFree = 1";

$result = $conn->query($sql_freeglu);

//Eredmények ellenőrzése és kiírása
if($result->num_rows > 0){
    echo "\n Gluténmentes termékek:\n";
    echo "_____________________\n";


    while($row = $result->fetch_assoc()){
        echo "Termék neve: " .$row["name"] . "\n";
        echo "Ára: " .$row["price"] . "\n";
        //echo "-----------------------------------";
    }

}else{echo"Nincs gluténmentes termék!";}


//Glutén és laktózmentes termékek lekérdezése
$sql_free = "SELECT name, price FROM recipes WHERE glutenFree = 1 AND lactoseFree = 1";

$result = $conn->query($sql_free);

//Eredmények ellenőrzése és kiírása
if($result->num_rows > 0){
    echo "\n Glutén és laktózmentes termékek:\n";
    echo "_____________________\n";


    while($row = $result->fetch_assoc()){
        echo "Termék neve: " .$row["name"] . "\n";
        echo "Ára: " .$row["price"] . "\n";
        //echo "-----------------------------------";
    }

}else{echo"Nincs glutén és laktózmentes termék!";}



//kapcsolat bontása
$conn->close();


?>