# Projektbeschreibung

## Projektthema: Autohaus

Das Projekt ist eine webbasierte Anwendung in Form eines **digitalen Autohauses**, bei dem Nutzer Fahrzeuge verschiedener Marken ansehen, filtern und reservieren können.

Der Schwerpunkt liegt auf:

- einer übersichtlichen Fahrzeugdarstellung
- einer Such- und Filterfunktion nach Marken
- einer benutzerbasierten Reservierungsfunktion

Die Anwendung wird als klassische Webanwendung unter Verwendung von:

- **HTML**
- **CSS**
- **JavaScript**
- **PHP**
- **MySQL**

nach dem **MVC-Pattern (Model-View-Controller)** umgesetzt.

---

# 1. Ziel des Projekts

Ziel der Anwendung ist die Entwicklung eines digitalen Fahrzeugportals, in dem Benutzer:

- verfügbare Fahrzeuge ansehen
- nach Marken suchen
- Fahrzeuge filtern
- Fahrzeugdetails einsehen
- sich registrieren
- sich einloggen
- ein Fahrzeug reservieren / mieten

können.

Zusätzlich erhält ein **Administrator** erweiterte Rechte zur Verwaltung der gesamten Plattform.

---

# 2. Grundfunktionalität

Die Plattform besteht aus zwei Hauptbereichen:

---

## 👤 Benutzerbereich

Für Kunden und registrierte Nutzer.

### Funktionen

- Registrierung
- Login / Logout
- Fahrzeugübersicht
- Suche nach Marken
- Filterfunktion
- Detailseite eines Fahrzeugs
- Fahrzeug reservieren
- eigene Reservierung ansehen
- Reservierung stornieren

---

## 🛠️ Adminbereich

Für Administratoren.

### Funktionen

- Fahrzeuge hinzufügen
- Fahrzeuge bearbeiten
- Fahrzeuge löschen
- neue Marken hinzufügen
- Bilder hochladen
- Reservierungen verwalten
- Benutzer verwalten

Diese Funktionen erfüllen die Anforderungen für:

- **CRUD-Operationen**
- **dynamische Erweiterbarkeit**
- **Admin-Rollenverwaltung**

---

# 3. Kategorien und Unterkategorien

Da die Aufgabenstellung mindestens zwei Kategorien und Unterkategorien verlangt, wird die Struktur wie folgt definiert:

---

## 🚘 Kategorie 1: Fahrzeugtyp

- PKW
- SUV
- Sportwagen

---

## 🏷️ Kategorie 2: Marken

- Marke A
- Marke B
- Marke C

Da konkrete Marken noch nicht final festgelegt sind, bleibt dies zunächst abstrakt.

Später können reale Marken ergänzt werden, z. B.:

- BMW
- Audi
- Mercedes

Diese Struktur ist prüfungstechnisch sehr gut geeignet.

---

# 4. Fahrzeugdaten

Jedes Fahrzeug besitzt folgende Eigenschaften:

- **Fahrzeug-ID**
- **Marke**
- **Modell**
- **Baujahr**
- **PS**
- **Fahrzeugbild**
- **Verfügbarkeitsstatus**
- **Reservierungsstatus**

---

## Optional erweiterbar

- Farbe
- Kraftstofftyp
- Getriebe
- Sitzplätze

---

# 5. Benutzerverwaltung

Die Anwendung enthält ein vollständiges Benutzerverwaltungssystem.

---

## 📝 Registrierung

Benutzer können einen eigenen Account erstellen.

### Pflichtfelder

- Vorname
- Nachname
- E-Mail
- Passwort

---

## 🔐 Login

Benutzer melden sich mit folgenden Daten an:

- E-Mail
- Passwort

Die Session-Verwaltung erfolgt serverseitig über:

- **PHP-Sessions**
- **Cookies**

---

# 6. Reservierungsfunktion

Benutzer können ein Fahrzeug direkt reservieren.

Die Reservierung erfolgt **ohne Datumsangabe**.

---

## Ablauf

1. Fahrzeug auswählen
2. Fahrzeugdetailseite öffnen
3. **„Reservieren“**-Button klicken
4. Fahrzeug wird dem Benutzerkonto zugeordnet
5. Status wechselt auf **„reserviert“**

Ein Benutzer kann immer nur **eine aktive Reservierung gleichzeitig** besitzen.

Diese Logik wird serverseitig geprüft.

---

# 7. Workflow-Modell

Dieses Modell ist besonders wichtig für die Aufgabenstellung.

---

## 📄 Seiten als Knoten

- Startseite
- Login
- Registrierung
- Fahrzeugübersicht
- Fahrzeugdetailseite
- Benutzerkonto
- Reservierungsseite
- Admin-Dashboard
- Fahrzeugverwaltung

---

## 🔄 Seitenwechsel als Kanten + Interaktionen

| Von | Nach | Interaktion |
|---|---|---|
| Startseite | Login | Login-Daten eingeben |
| Startseite | Registrierung | neuen Account erstellen |
| Startseite | Fahrzeugübersicht | Fahrzeuge anzeigen |
| Fahrzeugübersicht | Detailseite | Fahrzeug auswählen |
| Detailseite | Reservierungsseite | Fahrzeug reservieren |
| Reservierungsseite | Benutzerkonto | Reservierung anzeigen |
| Benutzerkonto | Logout | abmelden |
| Admin-Dashboard | Fahrzeugverwaltung | Fahrzeug hinzufügen / bearbeiten / löschen |

---

### Wichtig

Es gibt **keine Deadlocks**, da von jeder Seite ein Rückweg möglich ist.

Dies ist für die Aufgabenstellung entscheidend.

---

# 8. Technologien und deren Einsatz

---

## HTML

Struktur aller Seiten:

- Formulare
- Tabellen
- Kartenansichten

---

## CSS

Responsive Gestaltung.

### Vorgabe

Optimiert für Desktop-Auflösungen von:

- **1280 × 720**
- bis **1920 × 1080**

---

## JavaScript

Für dynamische Benutzerinteraktionen:

- Live-Suche
- Filter
- Formularvalidierung
- Reservierungsstatus

---

## AJAX (Pflicht)

Ein Pflichtbestandteil des Projekts.

---

## 🔍 Live-Fahrzeugsuche

Suche nach Marken **ohne Seitenreload**

### Beispielablauf

- Nutzer tippt „BMW“
- AJAX sendet Anfrage an PHP
- JSON-Daten werden zurückgegeben
- Fahrzeuge werden dynamisch angezeigt

Dies erfüllt die Prüfungsanforderung optimal.

---

## PHP

Backend nach MVC.

### Model
Datenbankzugriffe

### View
HTML-Ausgabe

### Controller
Logik und Request-Steuerung

---

## MySQL

Speicherung von:

- Benutzern
- Fahrzeugen
- Marken
- Reservierungen
- Bildern

---

# 9. Datenbankstruktur (grobe Planung)

---

## Tabelle `users`

| Feld | Beschreibung |
|---|---|
| id | Benutzer-ID |
| firstname | Vorname |
| lastname | Nachname |
| email | E-Mail |
| password | Passwort |
| role | Benutzerrolle |

---

## Tabelle `vehicles`

| Feld | Beschreibung |
|---|---|
| id | Fahrzeug-ID |
| brand_id | Fremdschlüssel Marke |
| model | Modell |
| year | Baujahr |
| horsepower | PS |
| image | Bild |
| status | Verfügbarkeit |

---

## Tabelle `brands`

| Feld | Beschreibung |
|---|---|
| id | Marken-ID |
| brand_name | Markenname |

---

## Tabelle `reservations`

| Feld | Beschreibung |
|---|---|
| id | Reservierungs-ID |
| user_id | Benutzer-ID |
| vehicle_id | Fahrzeug-ID |
| reservation_status | Status |

---

## 🔗 JOIN-Beziehung

```text
users ↔ reservations ↔ vehicles
```

Damit ist die JOIN-Vorgabe erfüllt.

---

# 10. Teamaufteilung (3 Personen)

Sehr wichtig für die Prüfungsordnung.

---

## 👨‍💻 Person 1 – Frontend

- HTML
- CSS
- Seitenstruktur
- responsives Layout
- Fahrzeugkarten

---

## ⚙️ Person 2 – Backend / MVC

- PHP Controller
- Login
- Registrierung
- Sessions / Cookies
- Reservierungslogik

---

## 🗄️ Person 3 – Datenbank / Admin / AJAX

- MySQL
- CRUD
- Adminbereich
- Fahrzeugverwaltung
- AJAX-Suche
- Bild-Upload