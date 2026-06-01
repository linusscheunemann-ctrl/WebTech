<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/toggle-theme.js"></script>
    <script src="JavaScript/cart.js" defer></script>
    <title>Motorräder</title>
</head>
<body>
    <!-- Navigation Anfang -->
   <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <!-- Navigation Ende -->
    
    <h1>Motorräder</h1>
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
                <td><img src="images/motorrad-museum-ingolstadt-1.jpg" alt="Ducati Panigale V4"></td>
                <td>Harley-Davidson</td>
                <td>Street 750</td>
                <td>120ps</td>
            </tr>
            <tr>
                <td><img src="images/image.png" alt="BMW S1000RR"></td>
                <td>bmw</td>
                <td>S1000RR</td>
                <td>205 PS</td>
            </tr>
        </tbody>
    </table>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
