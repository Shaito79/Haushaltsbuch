# Datenbankkonfiguration

Die Datenbankzugangsdaten stehen zentral in:

```text
config/database.ini
```

Standardwerte:

```ini
host = localhost
port = 3307
database = haushaltsbuch
username = root
password = ""
charset = utf8mb4
```

Die Datei kann mit VS Code, Notepad oder jedem anderen Texteditor geöffnet werden.
Sowohl `db.php` als auch `config/Database.php` lesen diese Datei automatisch ein.
