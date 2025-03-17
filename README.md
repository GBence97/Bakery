
# Pékség Adatbázis

Ez a projekt egy pékség adatbázisát kezeli, amelyből különböző elemzéseket és kimutatásokat végezhetünk. A rendszer feldolgozza a data.json fájlban található adatokat, majd elmenti azokat egy MySQL adatbázisba.


Használat:

1.Adatbázis és táblák létrehozása:

2.Indítsd el az XAMPP-ot, és győződj meg róla, hogy a MySQL és az Apache fut.

3.Nyisd meg a phpMyAdmin-t (http://localhost/phpmyadmin/).

4.Hozz létre egy új adatbázist bakery_db néven.

5.Futtasd az alábbi SQL parancsokat a táblák létrehozásához:



CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    price VARCHAR(50),
    lactoseFree BOOLEAN,
    glutenFree BOOLEAN
);

CREATE TABLE ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT,
    name VARCHAR(255),
    amount VARCHAR(50),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    amount VARCHAR(50)
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    amount INT
);

CREATE TABLE wholesale_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    amount VARCHAR(50),
    price INT
);

Adatok importálása:
A data.json fájl tartalmát az import.php szkript tölti fel az adatbázisba.

Futtasd az import.php-t a következő parancs segítségével:
- php import.php

Feladat:
1.Dolgozd fel a csatolt data.json fájlt és mentsd el egy adatbázisba (import.php)
2.Számold ki az utolsó hét árbevételét (salesOfLastWeek.php)
3.Külön listában jelenítsd meg a gluténmentes, a laktózmentes és a glutén és laktózmentes termékek nevét és árát (product_list.php) 
4.Számold ki az utolsó hét profitját (bevétel-alapanyag_költség) (profit.php)
5.Számold ki hogy a különböző termékekből külön-külön mennyit lehet maximum legyártani a jelenlegi készletből (max_production_calculation.php)
6.Számold ki a következő rendelés költségét és profitját (calculate_order_profit.php)