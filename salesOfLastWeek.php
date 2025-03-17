<?php
//Adatbázis kapcsolat létrehozása
include "db_conn.php";

//SQL-lekérdezés az árbevétel kiszámításához
$sql = "
    SELECT SUM(s.amount * CAST(REPLACE(REPLACE(r.price, ' Ft', ''), ' ', '') AS UNSIGNED)) AS total_revenue
    FROM sales s
    JOIN recipes r ON s.name = r.name
    WHERE s.amount IS NOT NULL AND r.price IS NOT NULL
";

//Lekérdezés futtatása
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_revenue = $row["total_revenue"] ?? 0; //Ha NULL, akkor 0 legyen
    echo "Az elmúlt hét teljes árbevétele: " . number_format($total_revenue, 0, ",", " ") . " Ft";
} else {
    echo "Hiba a lekérdezésben: " . $conn->error;
}

//Kapcsolat lezárása
$conn->close();
?>
