# Ergänzungen: Zeitraumfilter und Datenbank-Config

## Zeitraumfilter

Im Bereich **Letzte Buchungen** stehen jetzt die Felder **Ab** und **Bis** zur Verfügung. Beide Felder sind optional und können mit den bestehenden Filtern und Sortierungen kombiniert werden.

## Datenbankkonfiguration

Alle Zugangsdaten befinden sich zentral in:

```text
config/database_config.php
```

Voreingestellte Werte:

```php
'url'      => 'localhost',
'port'     => 3307,
'database' => 'haushaltsbuch',
'username' => 'root',
'password' => '',
'charset'  => 'utf8mb4',
```

Sowohl `db.php` als auch `config/Database.php` lesen diese Datei ein. Zugangsdaten müssen daher nur noch an einer Stelle geändert werden.
