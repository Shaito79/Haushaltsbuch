# PDO- und INI-Konfiguration

Die Datenbankzugangsdaten stehen in `config/database.ini`.

`db.php` erzeugt ein PDO-Objekt und gibt es ausdrücklich zurück. Die Hauptseiten binden es so ein:

```php
$pdo = require __DIR__ . '/db.php';
```

Dadurch kann `$pdo` nicht mehr versehentlich den Integer-Wert `1` enthalten.
