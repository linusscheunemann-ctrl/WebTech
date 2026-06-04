<!--## Beginn Code von Moritz -->

<footer id="footer-wrapper">
    <div id="footersocial">
        <ul>
            <li><a href="#"><img src="images/footer-facebook.png" alt="Facebook"></a></li>
            <li><a href="#"><img src="images/footer-email.png" alt="Instagram"></a></li>
            <li><a href="#"><img src="images/footer-telefon.png" alt="Email"></a></li>
            <li><a href="#"><img src="images/footer-anfahrt.png" alt="Telefon"></a></li>
        </ul>
    </div>

    <div id="claim-footer">
        <span id="head-footer">Kontakt</span>
        <p>Auto Union Straße 1</p>
        <p>85053 Ingolstadt</p>
        <p>Email: info@deinautohaus.de</p>
        <p>Telefon: 01234-567890</p>
        <p>Öffnungszeiten: Mo-Fr 9-18 Uhr, Sa 10-14 Uhr</p>
    </div>

    <div id="copyright">
        <p>© 2026 Dein Autohaus. Alle Rechte vorbehalten.</p>
        <p><a href="impressum.php">Impressum</a></p>
    </div>
</footer>

<div id="cart-drawer" class="cart-drawer" aria-hidden="true">
    <div class="cart-drawer-backdrop" data-cart-close></div>
    <aside class="cart-drawer-panel" aria-label="Aktueller Warenkorb">
        <div class="cart-drawer-header">
            <div>
                <p class="cart-drawer-eyebrow">Schnellansicht</p>
                <h2>Dein Warenkorb</h2>
            </div>
            <button type="button" class="cart-drawer-close" data-cart-close aria-label="Warenkorb schließen">×</button>
        </div>

        <div class="cart-drawer-meta">
            <span id="cart-drawer-count">0 Artikel</span>
            <a class="cart-drawer-link" href="cart.php">Zur Warenkorbseite</a>
        </div>

        <div id="cart-drawer-items" class="cart-drawer-items"></div>

        <div class="cart-drawer-footer">
            <div>
                <span>Gesamtsumme</span>
                <strong id="cart-drawer-total">0,00 €</strong>
            </div>
            <div class="cart-drawer-actions">
                <button type="button" class="btn-back" onclick="closeCartDrawer()">Weiter shoppen</button>
                <button type="button" class="btn-checkout" onclick="location.href='cart.php'">Checkout</button>
            </div>
        </div>
    </aside>
</div>
<!--## Schluss Code von Moritz -->