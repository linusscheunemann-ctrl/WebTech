Projektbeschreibung – WebTech Abschlussprojekt

Projektthema: Digitales Autohaus mit Fahrzeugvermietung

Das Projekt ist eine webbasierte Anwendung in Form eines digitalen Autohauses, bei dem Nutzer Fahrzeuge verschiedener Marken ansehen, filtern und reservieren können.

Der Schwerpunkt liegt auf einer übersichtlichen Fahrzeugdarstellung, einer Such- und Filterfunktion nach Marken sowie einer benutzerbasierten Reservierungsfunktion.

Die Anwendung wird als klassische Webanwendung unter Verwendung von HTML, CSS, JavaScript, PHP und MySQL im MVC-Pattern umgesetzt.

⸻

1. Ziel des Projekts

Ziel der Anwendung ist die Entwicklung eines digitalen Fahrzeugportals, in dem Benutzer:
	•	verfügbare Fahrzeuge ansehen
	•	nach Marken suchen
	•	Fahrzeuge filtern
	•	Fahrzeugdetails einsehen
	•	sich registrieren
	•	sich einloggen
	•	ein Fahrzeug reservieren / mieten

können.

Zusätzlich erhält ein Administrator erweiterte Rechte zur Verwaltung der gesamten Plattform.

⸻

2. Grundfunktionalität

Die Plattform besteht aus zwei Hauptbereichen:

Benutzerbereich

Für Kunden / registrierte Nutzer

Funktionen
	•	Registrierung
	•	Login / Logout
	•	Fahrzeugübersicht
	•	Suche nach Marken
	•	Filterfunktion
	•	Detailseite eines Fahrzeugs
	•	Fahrzeug reservieren
	•	eigene Reservierung ansehen
	•	Reservierung stornieren

⸻

Adminbereich

Für Administratoren

Funktionen
	•	Fahrzeuge hinzufügen
	•	Fahrzeuge bearbeiten
	•	Fahrzeuge löschen
	•	neue Marken hinzufügen
	•	Bilder hochladen
	•	Reservierungen verwalten
	•	Benutzer verwalten

Dies erfüllt die Anforderungen für dynamische Erweiterbarkeit und CRUD-Operationen.

⸻

3. Kategorien und Unterkategorien

Da die Aufgabenstellung mindestens zwei Kategorien und Unterkategorien verlangt, wird die Struktur wie folgt definiert:

Kategorie 1: Fahrzeugtyp
	•	PKW
	•	SUV
	•	Sportwagen

Kategorie 2: Marken
	•	Marke A
	•	Marke B
	•	Marke C

Da konkrete Marken noch nicht festgelegt sind, bleibt dies zunächst abstrakt.

Später können reale Marken ergänzt werden.

Zum Beispiel:
	•	BMW
	•	Audi
	•	Mercedes

Diese Struktur ist prüfungstechnisch sehr gut.

⸻

4. Fahrzeugdaten

Jedes Fahrzeug besitzt folgende Eigenschaften:
	•	Fahrzeug-ID
	•	Marke
	•	Modell
	•	Baujahr
	•	PS
	•	Fahrzeugbild
	•	Verfügbarkeitsstatus
	•	Reservierungsstatus

Optional erweiterbar:
	•	Farbe
	•	Kraftstofftyp
	•	Getriebe
	•	Sitzplätze

⸻

5. Benutzerverwaltung

Die Anwendung enthält ein vollständiges Benutzerverwaltungssystem.

Registrierung

Benutzer können einen eigenen Account erstellen.

Pflichtfelder:
	•	Vorname
	•	Nachname
	•	E-Mail
	•	Passwort

⸻

Login

Benutzer melden sich mit:
	•	E-Mail
	•	Passwort

an.

Die Session-Verwaltung erfolgt serverseitig über PHP-Sessions und Cookies.

⸻

6. Reservierungsfunktion

Benutzer können ein Fahrzeug direkt reservieren.

Da ihr Option B gewählt habt, erfolgt die Reservierung ohne Datumsangabe.

Ablauf:
	1.	Fahrzeug auswählen
	2.	Fahrzeugdetailseite öffnen
	3.	„Reservieren“-Button klicken
	4.	Fahrzeug wird dem Benutzerkonto zugeordnet
	5.	Status wechselt auf „reserviert“

Ein Benutzer kann immer nur eine aktive Reservierung gleichzeitig besitzen.

Diese Logik sollte serverseitig geprüft werden.

⸻

7. Workflow-Modell (für die Aufgabe sehr wichtig)

Hier ist bereits ein strukturelles Seitenmodell:

⸻

Seiten als Knoten
	•	Startseite
	•	Login
	•	Registrierung
	•	Fahrzeugübersicht
	•	Fahrzeugdetailseite
	•	Benutzerkonto
	•	Reservierungsseite
	•	Admin-Dashboard
	•	Fahrzeugverwaltung

⸻

Seitenwechsel als Kanten + Interaktionen

Startseite → Login

Interaktion: Login-Daten eingeben

Startseite → Registrierung

Interaktion: neuen Account erstellen

Startseite → Fahrzeugübersicht

Interaktion: Fahrzeuge anzeigen

Fahrzeugübersicht → Detailseite

Interaktion: Fahrzeug auswählen

Detailseite → Reservierungsseite

Interaktion: Fahrzeug reservieren

Reservierungsseite → Benutzerkonto

Interaktion: Reservierung anzeigen

Benutzerkonto → Logout

Interaktion: abmelden

Admin-Dashboard → Fahrzeugverwaltung

Interaktion: Fahrzeug hinzufügen / bearbeiten / löschen

⸻

Wichtig:
Es gibt keine Deadlocks, da von jeder Seite ein Rückweg möglich ist.

Das ist für die Aufgabe entscheidend.

⸻

8. Technologien und deren Einsatz

⸻

HTML

Struktur aller Seiten

Zum Beispiel:
	•	Formulare
	•	Tabellen
	•	Kartenansichten

⸻

CSS

Responsive Gestaltung

Wichtig laut Vorgabe:
	•	optimiert für Desktop
	•	1280x720 bis 1920x1080

⸻

JavaScript

Für dynamische Benutzerinteraktionen

Zum Beispiel:
	•	Live-Suche
	•	Filter
	•	Formularvalidierung
	•	Reservierungsstatus

⸻

AJAX (Pflicht)

Sehr wichtig für die Prüfung.

Empfohlenes Beispiel:

Live-Fahrzeugsuche

Ohne Seitenreload nach Marken suchen

z. B.
	•	Nutzer tippt „BMW“
	•	AJAX sendet Anfrage an PHP
	•	JSON-Daten werden zurückgegeben
	•	Fahrzeuge werden dynamisch angezeigt

Das ist perfekt für die Vorgabe.

⸻

PHP

Backend + MVC

Model

Datenbankzugriffe

View

HTML-Ausgabe

Controller

Logik und Request-Steuerung

⸻

MySQL

Speicherung von:
	•	Benutzern
	•	Fahrzeugen
	•	Marken
	•	Reservierungen
	•	Bildern

⸻

9. Datenbankstruktur (grobe Planung)

Tabelle users
	•	id
	•	firstname
	•	lastname
	•	email
	•	password
	•	role

⸻

Tabelle vehicles
	•	id
	•	brand_id
	•	model
	•	year
	•	horsepower
	•	image
	•	status

⸻

Tabelle brands
	•	id
	•	brand_name

⸻

Tabelle reservations
	•	id
	•	user_id
	•	vehicle_id
	•	reservation_status

Hier ist bereits ein JOIN enthalten:

users ↔ reservations ↔ vehicles

Damit ist die JOIN-Vorgabe erfüllt.

⸻

10. Teamaufteilung (3 Personen)

Sehr wichtig für die Prüfungsordnung.

⸻

Person 1 – Frontend
	•	HTML
	•	CSS
	•	Seitenstruktur
	•	Responsives Layout
	•	Fahrzeugkarten

⸻

Person 2 – Backend / MVC
	•	PHP Controller
	•	Login
	•	Registrierung
	•	Session / Cookies
	•	Reservierungslogik

⸻

Person 3 – Datenbank / Admin / AJAX
	•	MySQL
	•	CRUD
	•	Adminbereich
	•	Fahrzeugverwaltung
	•	AJAX-Suche
	•	Bild-Upload
