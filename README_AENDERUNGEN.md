# Änderungen

1. `db.php` und `config/Database.php` verwenden MySQL-Port **3307**.
2. Der Kontostand ist dynamisch: `konten.kontostand` gilt als Anfangsbestand; alle Buchungen werden addiert.
3. `testdaten_3_jahre.sql` erzeugt realistische Daten von August 2023 bis Juli 2026. Beim Import werden bestehende Transaktionen, Themen und Tags ersetzt.
4. Kategorien und Tags können auf dem Dashboard erstellt und Buchungen zugeordnet werden.
5. Der monatliche Saldo besitzt Anfangs- und Endmonat.
6. Buchungen lassen sich nach Art, Thema und Tag filtern sowie nach Datum, Betrag und Details sortieren.
7. Jede Buchung kann bearbeitet oder gelöscht werden.

## Start

```bash
php -S localhost:8000
```

Danach `http://localhost:8000/index.php` öffnen.
