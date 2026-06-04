<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/toggle-theme.js"></script>
    <script src="JavaScript/cart.js" defer></script>
    <title>Autos</title>
</head>
<body>
    <!-- Navigation Anfang -->
   <?php require_once __DIR__ . '/includes/navbar.php'; ?>
    <!-- Navigation Ende -->
<!--## Beginn Code von Moritz -->
    <h1>Autos</h1>
    <table>
        <thead>
            <tr>
                <th>Bild</th>
                <th>Marke</th>
                <th>Modell</th>
                <th>Leistung</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><img src="images/classic-cars-ingolstadt-11.jpg" alt="Ferrari F40"></td>
                <td>Ferrari</td>
                <td>F40</td>
                <td>620 PS</td>
            </tr>
        
          
            <tr>
                <td><img src="images/bg9.jpg" alt="Ferrari Enzo"></td>
                <td>Ferrari</td>
                <td>Enzo</td>
                <td>660 PS</td>
            </tr>
       
        </tbody>
    </table>
<!--## Schluss Code von Moritz -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
