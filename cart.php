<!DOCTYPE html>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="toggle-theme.js"></script>
<head>
   
    <title>Über uns</title>
</head>
    <!-- Navigation Anfang -->
   <a href="index.php"> <img src="images/logo.png" alt="Autohaus" class="logo"></a>
<nav>
    <div class="nav-center">
        <div class="dropdown">
            <a href="bestand.php" class="nav-button" id="dropbtn">CARS & BIKES</a>
            <a href="shop.php" class="nav-button">SHOP</a>

            <div class="dropdown-content">
                <a href="autos.php">Autos</a>
                <a href="motorraeder.php">Motorräder</a>
                <a href="shop.php" class="nav-button">SHOP</a>
            </div>
        </div>

        <a href="about.php" class="nav-button">ABOUT</a>
    </div>

    <div class="nav-right">
        <button onclick="myFunction()" id="theme-toggle" class="theme-toggle">🌕</button>
        <a href="login.php"><img src="images/login.png" alt="Login" class="login-icon"></a>
    </div>
</nav>

<body>
    
    <div class="cart-container"> 
        <h1>Warenkorb</h1>
        <p class="middle" id="empty-msg" style="display: block;">
            "Ihr Warenkorb ist leer. Fügen Sie Produkte hinzu, um fortzufahren." <br><br>
            <a href="index.php">Zurück zur Startseite</a>
        </p>
        <!--- Tabelle für den Warenkorb (Nils)--->
        <table id="cart-table" style="display: none;">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Produkt</th>
                    <th>Preis</th>
                    <th>Anzahl</th>
                    <th>Netto</th>
                    
                    
                    
                </tr>
            </thead>
            <tbody id="cart-body"> </tbody>
                <!-- Dynamisch generierte Zeilen für Produkte im Warenkorb -->
                 <tfoot>
                    
                 <tr>
                    <td colspan="4">Nettobetrag</td>
                    <td id="netto-price" colspan="2"></td>
                </tr>
                 <tr>
                    <td colspan="4">zzgl. MwSt. (19%)</td>
                    <td id="mwst-price" colspan="2"></td>         <!-- MwSt.-Zeile -->
                </tr>
                <tr class="total-row"></tr>
                    <td colspan="4"> Gesamt (inkl.MwSt.):</td>
                    <td id="total-price" colspan="2">0,00 €</td>
                 </tr>
                
                 </tfoot>
        </table>
        <div class="cart-actions">
            <a href="shop.php" class="btn-back">Weiter einkaufen</a>
            <button class="btn-clear" onclick="clearCart()">Warenkorb leeren</button>
            <button class="btn-checkout" onclick="checkout()">Zur Kasse</button>

        </div>
    </div>
    <script>

        // -------------------------------------------------------
        // Hilfsfunktionen
        // -------------------------------------------------------

        // Liest den Warenkorb aus dem localStorage aus (Array von Objekten)
        function getCart() {
            return JSON.parse(localStorage.getItem('cart')) || [];
        }

        // Speichert das aktuelle Warenkorb-Array im localStorage
        function saveCart(cart) {
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        // Wandelt einen Preis-String wie "19,99 €" in eine Zahl (19.99) um
        function parsePrice(priceStr) {
            return parseFloat(priceStr.replace(' €', '').replace(',', '.'));
        }

        // Formatiert eine Zahl als deutschen Preis-String, z. B. 19.99 → "19,99 €"
        function formatPrice(num) {
            return num.toFixed(2).replace('.', ',') + ' €';
        }
        function calcMwst(netto){
            return netto * 0.19;
        }

        // -------------------------------------------------------
        // Warenkorb-Anzeige aufbauen
        // -------------------------------------------------------

        // Rendert die Warenkorb-Tabelle anhand der localStorage-Daten neu
        function renderCart() {
            const cart = getCart();
            const tbody = document.getElementById('cart-body');
            const emptyMsg = document.getElementById('empty-msg');
            const cartTable = document.getElementById('cart-table');

            tbody.innerHTML = '';  // Tabellenkörper vor dem Neuaufbau leeren

            if (cart.length === 0) {
                // Leerer Warenkorb: Tabelle ausblenden, Hinweis einblenden
                cartTable.style.display = 'none';
                emptyMsg.style.display = 'block';
                document.getElementById('total-price').textContent = '0,00 €';
                return;
            }

            // Warenkorb hat Inhalt: Tabelle einblenden, Hinweis ausblenden
            cartTable.style.display = 'table';
            emptyMsg.style.display = 'none';

            let gesamtBetrag = 0;  // Akkumulator für den Gesamtpreis

            // Für jeden Artikel eine Tabellenzeile erstellen
            cart.forEach(function(item, index) {
                const einzelpreis = parsePrice(item.price);          // Einzelpreis als Zahl
                const zeilenSumme = einzelpreis * item.menge;         // Zeilensumme berechnen
                gesamtBetrag += zeilenSumme;                          // Zum Gesamtbetrag addieren

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${item.name}</td>
                    <td>${item.price}</td>
                    <td>
                        <!-- Menge verringern -->
                        <button class="qty-btn" onclick="changeQty(${index}, -1)">−</button>
                        <span class="qty-value">${item.menge}</span>
                        <!-- Menge erhöhen -->
                        <button class="qty-btn" onclick="changeQty(${index}, +1)">+</button>
                    </td>
                    <td>${formatPrice(zeilenSumme)}</td>
                    <td>
                        <!-- Artikel komplett aus dem Warenkorb entfernen -->
                        <button class="remove-btn" onclick="removeItem(${index})">Entfernen</button>
                    </td>
                `;
                tbody.appendChild(row);  // Zeile der Tabelle hinzufügen
            });

            // Gesamtbetrag in der Fußzeile der Tabelle anzeigen
            const nettoBetrag = gesamtBetrag / 1.19;
            const mwstBetrag = gesamtBetrag - nettoBetrag;
            document.getElementById('total-price').textContent = formatPrice(gesamtBetrag);
            document.getElementById('netto-price').textContent  = formatPrice(nettoBetrag);
            document.getElementById('mwst-price').textContent   = formatPrice(mwstBetrag);

        }

        // -------------------------------------------------------
        // Warenkorb-Aktionen
        // -------------------------------------------------------

        // Ändert die Menge eines Artikels um den angegebenen Wert (+1 oder -1).
        // Wenn die Menge auf 0 fällt, wird der Artikel entfernt.
        function changeQty(index, delta) {
            const cart = getCart();
            cart[index].menge += delta;

            if (cart[index].menge <= 0) {
                cart.splice(index, 1);  // Artikel aus Array entfernen wenn Menge 0
            }

            saveCart(cart);   // Geänderten Warenkorb speichern
            renderCart();     // Tabelle neu rendern
        }
        // Preis mit Steuer berechnen 
        
        

        // Entfernt einen Artikel anhand seines Index komplett aus dem Warenkorb
        function removeItem(index) {
            const cart = getCart();
            cart.splice(index, 1);  // Artikel an Position index entfernen
            saveCart(cart);
            renderCart();
        }

        // Leert den gesamten Warenkorb nach einer Sicherheitsabfrage
        function clearCart() {
            if (confirm('Möchtest du den gesamten Warenkorb leeren?')) {
                localStorage.removeItem('cart');  // Warenkorb-Eintrag aus localStorage löschen
                renderCart();
            }
        }

        // Platzhalter für den Checkout-Prozess
        function checkout() {
            alert('Vielen Dank für deine Bestellung! 🎉');
            localStorage.removeItem('cart');  
            renderCart();
        }

        // -------------------------------------------------------
        //  Warenkorb beim Laden rendern
        // -------------------------------------------------------
        renderCart();
    
    </script>
</body>