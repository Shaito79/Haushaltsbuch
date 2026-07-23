Änderungen dieser Version
=========================

1. Header-Navigation
- Dashboard
- Diagramme auf dem Dashboard
- Neue Buchung
- Themen & Tags
- Buchungen
- separate Diagrammseite

2. Diagramme
- Monatlicher Saldo
- Einnahmen versus Ausgaben
- Ausgaben nach Kategorie
- separate Detailseite unter diagramme/index.php
- alle Werte kommen aus MySQL

3. Themen und Tags
- erstellen
- umbenennen
- Typ einer Kategorie ändern
- löschen

Sicherheitslogik:
- Kategorien können nicht gelöscht werden, solange Buchungen sie verwenden.
- Beim Löschen eines Tags werden zuerst seine Zuordnungen zu Buchungen entfernt.
