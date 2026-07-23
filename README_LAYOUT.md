# Haushaltsbuch – überarbeitetes Layout

## Starten

1. MySQL/XAMPP starten und die Datenbank `haushaltsbuch` bereitstellen.
2. Zugangsdaten in `db.php` prüfen.
3. Im Projektordner den PHP-Server starten:
   `php -S localhost:8000`
4. Dashboard öffnen:
   `http://localhost:8000/index.php`
5. Diagramme öffnen:
   `http://localhost:8000/diagramme/index.php`

## Geänderte Dateien

- `index.php`: modernes Dashboard, Kennzahlen, Buchungsformular, Charts und Löschfunktion
- `diagramme/index.php`: Diagramme direkt aus MySQL-Daten
- `style.css`: einheitliches responsives Design
- `script.js`: kleine UI-Helfer
- `includes/header.php`: gemeinsame Navigation und CSS-Einbindung
- `includes/footer.php`: gemeinsamer Footer und Bootstrap-JavaScript

Die bestehenden DAO-, Datenbank- und Statistikdateien wurden nicht entfernt.
