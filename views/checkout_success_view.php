<?php
// ## Beginn generierter Code von Codex
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=cart.php?booking=success">
    <title>Buchung abgeschlossen</title>
</head>
<body>
    <script>
        try {
            localStorage.removeItem('cart');
        } catch (error) {
        }
        window.location.replace('cart.php?booking=success');
    </script>
    <noscript>
        <p>Die Buchung wurde abgeschlossen. <a href="cart.php?booking=success">Weiter</a>.</p>
    </noscript>
</body>
</html>
<?php
// ## Schluss generierter Code von Codex
