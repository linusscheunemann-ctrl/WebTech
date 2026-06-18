<?php
// ## Beginn generierter Code von Codex
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/cart.js" defer></script>
    <script src="JavaScript/admin-products.js" defer></script>
    <title>Adminbereich</title>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="admin-dashboard">
        <h1>Auftragsverwaltung</h1>
        <a href="logout.php" class="logout-button admin-logout-link">Abmelden</a>

        <?php if ($flashMessage): ?>
            <p class="form-message <?php echo htmlspecialchars($flashMessage['type'] === 'error' ? 'error-message' : 'success-message', ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($flashMessage['message'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <div class="admin-tabs">
            <?php foreach ($tabs as $tabKey => $tabConfig): ?>
                <a class="admin-tab <?php echo $tabKey === $activeTab ? 'is-active' : ''; ?>" href="admin.php?tab=<?php echo htmlspecialchars($tabKey, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($tabConfig['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <section class="admin-panel">
            <h2><?php echo htmlspecialchars($tabs[$activeTab]['label'], ENT_QUOTES, 'UTF-8'); ?></h2>

            <?php if ($bookings === []): ?>
                <p class="account-bookings-empty">Für diese Ansicht liegen aktuell keine Aufträge vor.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Nr.</th>
                        <th>Nutzer</th>
                        <th>Datum</th>
                        <th>Positionen</th>
                        <th>Brutto</th>
                        <th>Rabatt</th>
                        <th>Gesamt</th>
                        <th>Status</th>
                        <th>Hinweis</th>
                        <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                                $bookingId = (int) $booking['id'];
                                $items = $bookingItems[$bookingId] ?? [];
                                $itemLines = array_map(
                                    static fn(array $item): string => (int) $item['quantity'] . 'x ' . $item['product_name'],
                                    $items
                                );
                                $subtotal = (float) ($booking['subtotal_amount'] ?? $booking['total_amount']);
                                $discountAmount = (float) ($booking['discount_amount'] ?? 0);
                                $discountPercent = (float) ($booking['discount_percent'] ?? 0);
                                $discountLabel = (string) ($booking['discount_label'] ?? '');
                                $status = (string) ($booking['status'] ?? 'new');
                                $statusLabel = appBookingStatusLabel($status);
                                $statusClass = appBookingStatusClass($status);
                                $reason = (string) ($booking['rejection_reason'] ?? '');
                            ?>
                            <tr>
                                <td><?php echo $bookingId; ?></td>
                                <td><?php echo htmlspecialchars((string) $booking['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime((string) $booking['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($itemLines === [] ? '-' : implode(', ', $itemLines), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(appFormatMoney($subtotal), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(appFormatDiscountSummary($discountLabel !== '' ? $discountLabel : null, $discountPercent, $discountAmount), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(appFormatMoney((float) $booking['total_amount']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="admin-status <?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($reason !== '' ? $reason : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if ($status === 'new'): ?>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="advance_booking">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                            <input type="hidden" name="target_status" value="processing">
                                            <button type="submit" class="admin-action-button">In Bearbeitung</button>
                                        </form>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="reject_booking">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                            <input type="text" name="rejection_reason" placeholder="Ablehnungsgrund" required>
                                            <button type="submit" class="admin-action-button">Ablehnen</button>
                                        </form>
                                    <?php elseif ($status === 'processing'): ?>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="advance_booking">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                            <input type="hidden" name="target_status" value="completed">
                                            <button type="submit" class="admin-action-button">Abschließen</button>
                                        </form>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="reject_booking">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                            <input type="text" name="rejection_reason" placeholder="Ablehnungsgrund" required>
                                            <button type="submit" class="admin-action-button">Ablehnen</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="admin-panel">
            <div class="admin-users-toolbar">
                <h2>Nutzerliste</h2>
            </div>

            <?php if ($users === []): ?>
                <p class="account-bookings-empty">Es sind noch keine Nutzer vorhanden.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nr.</th>
                            <th>Nutzer</th>
                            <th>Rolle</th>
                            <th>Status</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php
                                $blocked = (int) ($user['is_blocked'] ?? 0) === 1;
                                $isAdminAccount = ($user['role'] ?? 'user') === 'admin';
                            ?>
                            <tr>
                                <td><?php echo (int) $user['id']; ?></td>
                                <td><?php echo htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $user['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="admin-status <?php echo $blocked ? 'status-rejected' : 'status-completed'; ?>">
                                        <?php echo $blocked ? 'Gesperrt' : 'Aktiv'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isAdminAccount): ?>
                                        -
                                    <?php else: ?>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="toggle_user_block">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                            <button type="submit" class="admin-action-button">
                                                <?php echo $blocked ? 'Entsperren' : 'Sperren'; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="admin-panel">
            <div class="admin-users-toolbar">
                <h2>Produkte</h2>
            </div>

            <div class="admin-filter-bar">
                <label for="admin-product-filter">Produkte filtern</label>
                <input
                    type="search"
                    id="admin-product-filter"
                    class="admin-product-filter"
                    placeholder="Name, Kategorie oder Unterkategorie"
                    data-products-endpoint="api/products.php"
                >
                <span class="admin-filter-count" id="admin-product-filter-count"><?php echo count($products); ?> Produkte</span>
            </div>

            <?php if ($products === []): ?>
                <p class="account-bookings-empty">Es sind noch keine Produkte vorhanden.</p>
            <?php endif; ?>

            <table class="admin-table" id="admin-products-table">
                <thead>
                    <tr>
                        <th>Nr.</th>
                        <th>Name</th>
                        <th>Bild</th>
                        <th>Preis</th>
                        <th>Kategorie</th>
                        <th>Unterkategorie</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody id="admin-products-table-body">
                    <?php if ($products === []): ?>
                        <tr>
                            <td colspan="7" class="account-bookings-empty">Keine Produkte vorhanden.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <?php $imagePath = (string) ($product['image_path'] ?? ''); ?>
                            <tr>
                                <td><?php echo (int) $product['id']; ?></td>
                                <td><?php echo htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if ($imagePath !== ''): ?>
                                        <img
                                            src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>"
                                            alt="<?php echo htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                            class="product-thumb"
                                        >
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(appFormatMoney((float) $product['price']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $product['subcategory'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <form action="admin.php" method="post" class="admin-inline-form">
                                        <input type="hidden" name="action" value="delete_product">
                                        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                        <button type="submit" class="admin-action-button" onclick="return confirm('Dieses Produkt wirklich löschen?');">Löschen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="admin-product-grid">
                <form action="admin.php" method="post" class="admin-form-grid admin-product-form">
                    <input type="hidden" name="action" value="create_product">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">

                    <label for="product_name">Produktname</label>
                    <input type="text" id="product_name" name="product_name" required>

                    <label for="product_price">Preis</label>
                    <input type="text" id="product_price" name="product_price" inputmode="decimal" placeholder="z. B. 19,90" required>

                    <label for="product_category">Kategorie</label>
                    <input type="text" id="product_category" name="product_category" required>

                    <label for="product_subcategory">Unterkategorie</label>
                    <input type="text" id="product_subcategory" name="product_subcategory" required>

                    <label class="full-width" for="product_description">Beschreibung</label>
                    <textarea id="product_description" name="product_description" rows="5" class="full-width" required></textarea>

                    <label class="full-width" for="image_id">Produktbild</label>
                    <select id="image_id" name="image_id" class="full-width">
                        <option value="">Ohne Bild</option>
                        <?php foreach ($productImages as $image): ?>
                            <option value="<?php echo (int) $image['id']; ?>">
                                <?php echo htmlspecialchars('#' . (int) $image['id'] . ' - ' . (string) ($image['original_name'] ?: $image['file_path']), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="admin-form-actions full-width">
                        <button type="submit" class="admin-action-button">Produkt anlegen</button>
                    </div>
                </form>

                <form action="admin.php" method="post" enctype="multipart/form-data" class="admin-form-grid admin-product-form">
                    <input type="hidden" name="action" value="upload_product_image">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">

                    <label class="full-width" for="product_image">Neues Produktbild hochladen</label>
                    <input type="file" id="product_image" name="product_image" accept="image/*" class="full-width" required>

                    <p class="account-bookings-empty full-width">
                        Erlaubt sind JPG, PNG, GIF und WEBP. Das Bild landet in der Bildbibliothek und kann danach für neue Produkte verwendet werden.
                    </p>

                    <div class="admin-form-actions full-width">
                        <button type="submit" class="admin-action-button">Bild hochladen</button>
                    </div>
                </form>
            </div>

            <h3 class="admin-subheading">Bildbibliothek</h3>
            <?php if ($productImages === []): ?>
                <p class="account-bookings-empty">Es wurden noch keine Produktbilder hochgeladen.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nr.</th>
                            <th>Vorschau</th>
                            <th>Datei</th>
                            <th>Hochgeladen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productImages as $image): ?>
                            <tr>
                                <td><?php echo (int) $image['id']; ?></td>
                                <td>
                                    <img
                                        src="<?php echo htmlspecialchars((string) $image['file_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="<?php echo htmlspecialchars((string) ($image['original_name'] ?: $image['file_path']), ENT_QUOTES, 'UTF-8'); ?>"
                                        class="product-thumb"
                                    >
                                </td>
                                <td><?php echo htmlspecialchars((string) ($image['original_name'] ?: $image['file_path']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime((string) $image['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="admin-panel">
            <h2>Rabatteinstellungen</h2>

            <form action="admin.php" method="post" class="admin-inline-form">
                <input type="hidden" name="action" value="update_discount_settings">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">

                <label>
                    <input type="checkbox" name="discount_enabled" <?php echo $discountConfig['enabled'] ? 'checked' : ''; ?>>
                    Rabatt aktivieren
                </label>

                <label for="discount_10_percent">10. Bestellung</label>
                <input type="text" inputmode="decimal" name="discount_10_percent" id="discount_10_percent" value="<?php echo htmlspecialchars((string) $discountConfig['ten_percent'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="discount_20_percent">20. Bestellung</label>
                <input type="text" inputmode="decimal" name="discount_20_percent" id="discount_20_percent" value="<?php echo htmlspecialchars((string) $discountConfig['twenty_percent'], ENT_QUOTES, 'UTF-8'); ?>">

                <button type="submit" class="admin-action-button">Speichern</button>
            </form>

            <p class="account-bookings-empty">
                Aktuell gilt: <?php echo $discountConfig['enabled'] ? 'aktiv' : 'deaktiviert'; ?>,
                10. Bestellung: <?php echo htmlspecialchars((string) $discountConfig['ten_percent'], ENT_QUOTES, 'UTF-8'); ?> %,
                20. Bestellung: <?php echo htmlspecialchars((string) $discountConfig['twenty_percent'], ENT_QUOTES, 'UTF-8'); ?> %.
            </p>
        </section>
    </main>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
<?php
// ## Schluss generierter Code von Codex
