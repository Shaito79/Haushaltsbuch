<?php 
require_once 'db.php'; 

// 1. Neue Buchung speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_buchung'])) {
    $thema_id     = (int)$_POST['thema_id'];
    $betrag       = (float)$_POST['betrag'];
    $typ          = $_POST['typ'];
    $datum        = $_POST['datum'];
    $beschreibung = trim($_POST['beschreibung']);

    // Bei einer Ausgabe speichern wir den Betrag als negativen Wert
    if ($typ === 'Ausgabe' && $betrag > 0) {
        $betrag = -$betrag;
    }

    // Standard-Konto auf ID 1 setzen (sofern vorhanden)
    $konto_id = 1; 

    $sqlInsert = "INSERT INTO transaktionen (konto_id, thema_id, betrag, beschreibung, datum) 
                  VALUES (:konto_id, :thema_id, :betrag, :beschreibung, :datum)";
    
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([
        'konto_id'     => $konto_id,
        'thema_id'     => $thema_id,
        'betrag'       => $betrag,
        'beschreibung' => $beschreibung,
        'datum'        => $datum
    ]);

    // Nach dem Speichern die Seite neu laden (verhindert doppeltes Absenden)
    header("Location: index.php");
    exit;
}

// 2. Kontostand abfragen
$stmtKonto = $pdo->query("SELECT kontostand FROM konten LIMIT 1");
$konto = $stmtKonto->fetch();
$kontostand = $konto ? $konto['kontostand'] : 0.00;

// 3. Kategorien/Themen für das Dropdown-Menü abfragen
$stmtThemen = $pdo->query("SELECT * FROM themen ORDER BY name ASC");
$themen = $stmtThemen->fetchAll();

// 4. Alle Buchungen abfragen (inklusive Thema/Kategorie)
$sqlTransaktionen = "SELECT t.*, th.name AS kategorie_name 
                     FROM transaktionen t 
                     LEFT JOIN themen th ON t.thema_id = th.id 
                     ORDER BY t.datum DESC";
$stmtTransaktionen = $pdo->query($sqlTransaktionen);
$transaktionen = $stmtTransaktionen->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css">
    <title>Haushaltskasse</title>
</head>
<body>

<div class="container">

    <h1>Haushaltskasse</h1>

    <div class="card">
        <h2>Kontostand</h2>
        <p class="kontostand"><?php echo number_format($kontostand, 2, ',', '.'); ?> €</p>
    </div>

    <div class="card">
        <h2>Monats- / Jahresauswertung</h2>

        <label for="monat">Monat</label>
        <select id="monat">
            <option>Januar</option>
            <option>Februar</option>
            <option>März</option>
            <option>April</option>
            <option>Mai</option>
            <option>Juni</option>
            <option>Juli</option>
            <option>August</option>
            <option>September</option>
            <option>Oktober</option>
            <option>November</option>
            <option>Dezember</option>
        </select>

        <label for="jahr">Jahr</label>
        <select id="jahr"></select>

        <button>Anzeigen</button>
    </div>

    <div class="card">
        <h2>Neue Buchung</h2>

        <form action="index.php" method="POST">
            <label for="typ">Typ</label>
            <select name="typ" id="typ">
                <option value="Einnahme">Einnahme</option>
                <option value="Ausgabe">Ausgabe</option>
            </select>

            <label for="betrag">Betrag</label>
            <input type="number" id="betrag" name="betrag" step="0.01" required>

            <label for="thema_id">Kategorie</label>
            <select name="thema_id" id="thema_id" required>
                <?php foreach ($themen as $thema): ?>
                    <option value="<?php echo $thema['id']; ?>">
                        <?php echo htmlspecialchars($thema['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="datum">Datum</label>
            <input type="date" id="datum" name="datum" required value="<?php echo date('Y-m-d'); ?>">

            <label for="beschreibung">Beschreibung</label>
            <input type="text" id="beschreibung" name="beschreibung">

            <button type="submit" name="submit_buchung">Speichern</button>
        </form>
    </div>

    <div class="card">
        <h2>Buchungen</h2>

        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Kategorie</th>
                    <th>Betrag</th>
                    <th>Aktion</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($transaktionen)): ?>
                    <tr>
                        <td colspan="4">Keine Buchungen vorhanden.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transaktionen as $row): ?>
                        <tr>
                            <td><?php echo date('d.m.Y', strtotime($row['datum'])); ?></td>
                            <td><?php echo htmlspecialchars($row['kategorie_name'] ?? 'Ohne'); ?></td>
                            <td><?php echo number_format($row['betrag'], 2, ',', '.'); ?> €</td>
                            <td>
                                <button>Löschen</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="./script.js"></script>

</body>
</html>