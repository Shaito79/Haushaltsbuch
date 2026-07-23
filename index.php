<?php
require_once __DIR__ . '/db.php';

function redirectHome(array $params = []): never {
    $query = $params ? '?' . http_build_query($params) : '';
    header('Location: index.php' . $query);
    exit;
}

function selected(string|int $value, string|int $current): string {
    return (string)$value === (string)$current ? ' selected' : '';
}

$kontoId = 1;
$meldung = $_GET['meldung'] ?? '';
$fehler = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $aktion = $_POST['aktion'] ?? '';

        if ($aktion === 'buchung_speichern' || $aktion === 'buchung_aktualisieren') {
            $themaId = filter_input(INPUT_POST, 'thema_id', FILTER_VALIDATE_INT);
            $betrag = (float)str_replace(',', '.', (string)($_POST['betrag'] ?? '0'));
            $typ = $_POST['typ'] ?? 'Ausgabe';
            $datum = $_POST['datum'] ?? date('Y-m-d');
            $beschreibung = trim((string)($_POST['beschreibung'] ?? ''));
            $tagIds = array_values(array_filter(array_map('intval', $_POST['tag_ids'] ?? [])));

            if (!$themaId || $betrag <= 0 || !in_array($typ, ['Einnahme', 'Ausgabe'], true)) {
                throw new RuntimeException('Bitte Buchungstyp, Betrag und Thema vollständig angeben.');
            }

            $betrag = $typ === 'Ausgabe' ? -abs($betrag) : abs($betrag);
            $pdo->beginTransaction();

            if ($aktion === 'buchung_aktualisieren') {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if (!$id) {
                    throw new RuntimeException('Ungültige Buchungs-ID.');
                }
                $stmt = $pdo->prepare(
                    'UPDATE transaktionen
                     SET thema_id = :thema_id, betrag = :betrag, beschreibung = :beschreibung, datum = :datum
                     WHERE id = :id AND konto_id = :konto_id'
                );
                $stmt->execute([
                    'thema_id' => $themaId,
                    'betrag' => $betrag,
                    'beschreibung' => $beschreibung,
                    'datum' => $datum,
                    'id' => $id,
                    'konto_id' => $kontoId,
                ]);
                $transaktionId = $id;
                $pdo->prepare('DELETE FROM transaktionen_tags WHERE transaktion_id = ?')->execute([$id]);
                $meldungText = 'Buchung wurde aktualisiert.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO transaktionen (konto_id, thema_id, betrag, beschreibung, datum)
                     VALUES (:konto_id, :thema_id, :betrag, :beschreibung, :datum)'
                );
                $stmt->execute([
                    'konto_id' => $kontoId,
                    'thema_id' => $themaId,
                    'betrag' => $betrag,
                    'beschreibung' => $beschreibung,
                    'datum' => $datum,
                ]);
                $transaktionId = (int)$pdo->lastInsertId();
                $meldungText = 'Buchung wurde gespeichert.';
            }

            if ($tagIds) {
                $tagStmt = $pdo->prepare(
                    'INSERT IGNORE INTO transaktionen_tags (transaktion_id, tag_id) VALUES (:transaktion_id, :tag_id)'
                );
                foreach ($tagIds as $tagId) {
                    $tagStmt->execute(['transaktion_id' => $transaktionId, 'tag_id' => $tagId]);
                }
            }

            $pdo->commit();
            redirectHome(['meldung' => $meldungText]);
        }

        if ($aktion === 'buchung_loeschen') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $stmt = $pdo->prepare('DELETE FROM transaktionen WHERE id = :id AND konto_id = :konto_id');
                $stmt->execute(['id' => $id, 'konto_id' => $kontoId]);
            }
            redirectHome(['meldung' => 'Buchung wurde gelöscht.']);
        }

        if ($aktion === 'thema_erstellen') {
            $name = trim((string)($_POST['name'] ?? ''));
            $typ = $_POST['thema_typ'] ?? '';
            if ($name === '' || !in_array($typ, ['einnahme', 'ausgabe'], true)) {
                throw new RuntimeException('Bitte einen Kategorienamen und einen gültigen Typ angeben.');
            }
            $stmt = $pdo->prepare('INSERT INTO themen (name, typ) VALUES (:name, :typ)');
            $stmt->execute(['name' => $name, 'typ' => $typ]);
            redirectHome(['meldung' => 'Kategorie wurde erstellt.']);
        }

        if ($aktion === 'tag_erstellen') {
            $name = trim((string)($_POST['name'] ?? ''));
            if ($name === '') {
                throw new RuntimeException('Bitte einen Tagnamen angeben.');
            }
            $stmt = $pdo->prepare('INSERT INTO tags (name) VALUES (:name)');
            $stmt->execute(['name' => $name]);
            redirectHome(['meldung' => 'Tag wurde erstellt.']);
        }
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $fehler = $e instanceof PDOException && $e->getCode() === '23000'
        ? 'Dieser Name ist bereits vorhanden.'
        : $e->getMessage();
}

$themen = $pdo->query('SELECT id, name, typ FROM themen ORDER BY name')->fetchAll();
$tags = $pdo->query('SELECT id, name FROM tags ORDER BY name')->fetchAll();

$kontoStmt = $pdo->prepare(
    'SELECT k.name, k.kontostand AS anfangsbestand,
            k.kontostand + COALESCE(SUM(t.betrag), 0) AS aktueller_kontostand
     FROM konten k
     LEFT JOIN transaktionen t ON t.konto_id = k.id
     WHERE k.id = :konto_id
     GROUP BY k.id, k.name, k.kontostand'
);
$kontoStmt->execute(['konto_id' => $kontoId]);
$konto = $kontoStmt->fetch() ?: ['name' => 'Girokonto', 'anfangsbestand' => 0, 'aktueller_kontostand' => 0];

$summaryStmt = $pdo->prepare(
    'SELECT
        COALESCE(SUM(CASE WHEN betrag > 0 THEN betrag ELSE 0 END), 0) AS einnahmen,
        COALESCE(ABS(SUM(CASE WHEN betrag < 0 THEN betrag ELSE 0 END)), 0) AS ausgaben,
        COUNT(*) AS anzahl
     FROM transaktionen WHERE konto_id = :konto_id'
);
$summaryStmt->execute(['konto_id' => $kontoId]);
$summary = $summaryStmt->fetch();

$startMonth = $_GET['start_monat'] ?? date('Y-m', strtotime('-11 months'));
$endMonth = $_GET['end_monat'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $startMonth)) $startMonth = date('Y-m', strtotime('-11 months'));
if (!preg_match('/^\d{4}-\d{2}$/', $endMonth)) $endMonth = date('Y-m');
if ($startMonth > $endMonth) [$startMonth, $endMonth] = [$endMonth, $startMonth];
$rangeStart = $startMonth . '-01';
$rangeEnd = date('Y-m-t', strtotime($endMonth . '-01'));

$monthlyStmt = $pdo->prepare(
    "SELECT DATE_FORMAT(datum, '%Y-%m') AS monat, SUM(betrag) AS saldo
     FROM transaktionen
     WHERE konto_id = :konto_id AND datum BETWEEN :von AND :bis
     GROUP BY DATE_FORMAT(datum, '%Y-%m') ORDER BY monat"
);
$monthlyStmt->execute(['konto_id' => $kontoId, 'von' => $rangeStart, 'bis' => $rangeEnd]);
$monthlyRaw = array_column($monthlyStmt->fetchAll(), 'saldo', 'monat');
$monthlyLabels = [];
$monthlyValues = [];
$cursor = new DateTimeImmutable($rangeStart);
$endCursor = new DateTimeImmutable($rangeEnd);
while ($cursor <= $endCursor) {
    $key = $cursor->format('Y-m');
    $monthlyLabels[] = $cursor->format('m/Y');
    $monthlyValues[] = (float)($monthlyRaw[$key] ?? 0);
    $cursor = $cursor->modify('+1 month');
}

$filterTyp = $_GET['filter_typ'] ?? 'alle';
$filterThema = filter_input(INPUT_GET, 'filter_thema', FILTER_VALIDATE_INT) ?: 0;
$filterTag = filter_input(INPUT_GET, 'filter_tag', FILTER_VALIDATE_INT) ?: 0;
$sort = $_GET['sort'] ?? 'datum';
$richtung = strtolower($_GET['richtung'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$sortMap = ['datum' => 't.datum', 'betrag' => 't.betrag', 'details' => 't.beschreibung'];
$sortColumn = $sortMap[$sort] ?? 't.datum';

$where = ['t.konto_id = :konto_id'];
$params = ['konto_id' => $kontoId];
if ($filterTyp === 'einnahme') $where[] = 't.betrag > 0';
if ($filterTyp === 'ausgabe') $where[] = 't.betrag < 0';
if ($filterThema) { $where[] = 't.thema_id = :thema_id'; $params['thema_id'] = $filterThema; }
if ($filterTag) {
    $where[] = 'EXISTS (SELECT 1 FROM transaktionen_tags ftt WHERE ftt.transaktion_id = t.id AND ftt.tag_id = :tag_id)';
    $params['tag_id'] = $filterTag;
}

$sql = "SELECT t.*, th.name AS thema_name,
               GROUP_CONCAT(DISTINCT tg.name ORDER BY tg.name SEPARATOR ', ') AS tag_namen
        FROM transaktionen t
        LEFT JOIN themen th ON th.id = t.thema_id
        LEFT JOIN transaktionen_tags tt ON tt.transaktion_id = t.id
        LEFT JOIN tags tg ON tg.id = tt.tag_id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY t.id, th.name
        ORDER BY $sortColumn $richtung, t.id $richtung";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaktionen = $stmt->fetchAll();

$editTransaktion = null;
$editTagIds = [];
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM transaktionen WHERE id = :id AND konto_id = :konto_id');
    $stmt->execute(['id' => $editId, 'konto_id' => $kontoId]);
    $editTransaktion = $stmt->fetch() ?: null;
    if ($editTransaktion) {
        $stmt = $pdo->prepare('SELECT tag_id FROM transaktionen_tags WHERE transaktion_id = ?');
        $stmt->execute([$editId]);
        $editTagIds = array_map('intval', array_column($stmt->fetchAll(), 'tag_id'));
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Haushaltskasse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="topbar">
    <div><span class="logo"><i class="bi bi-wallet2"></i></span><strong>Haushaltskasse</strong></div>
    <span class="account-name"><?= htmlspecialchars($konto['name']) ?></span>
</header>

<main class="page-shell">
    <?php if ($meldung): ?><div class="alert success"><?= htmlspecialchars($meldung) ?></div><?php endif; ?>
    <?php if ($fehler): ?><div class="alert error"><?= htmlspecialchars($fehler) ?></div><?php endif; ?>

    <section class="stats-grid">
        <article class="stat-card primary"><span>Aktueller Kontostand</span><strong><?= number_format((float)$konto['aktueller_kontostand'], 2, ',', '.') ?> €</strong><small>Anfangsbestand: <?= number_format((float)$konto['anfangsbestand'], 2, ',', '.') ?> €</small></article>
        <article class="stat-card income"><span>Einnahmen gesamt</span><strong><?= number_format((float)$summary['einnahmen'], 2, ',', '.') ?> €</strong></article>
        <article class="stat-card expense"><span>Ausgaben gesamt</span><strong><?= number_format((float)$summary['ausgaben'], 2, ',', '.') ?> €</strong></article>
        <article class="stat-card neutral"><span>Buchungen</span><strong><?= (int)$summary['anzahl'] ?></strong></article>
    </section>

    <section class="content-grid">
        <article class="panel chart-panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Auswertung</p><h2>Monatlicher Saldo</h2></div>
                <form class="range-form" method="get">
                    <label>Anfangsmonat<input type="month" name="start_monat" value="<?= htmlspecialchars($startMonth) ?>"></label>
                    <label>Endmonat<input type="month" name="end_monat" value="<?= htmlspecialchars($endMonth) ?>"></label>
                    <button class="button secondary" type="submit"><i class="bi bi-arrow-repeat"></i> Anzeigen</button>
                </form>
            </div>
            <div class="chart-wrap"><canvas id="saldoChart"></canvas></div>
        </article>

        <article class="panel form-panel" id="buchungsformular">
            <div class="panel-heading"><div><p class="eyebrow"><?= $editTransaktion ? 'Bearbeiten' : 'Neu erfassen' ?></p><h2><?= $editTransaktion ? 'Buchung verändern' : 'Neue Buchung' ?></h2></div></div>
            <form method="post" class="form-grid">
                <input type="hidden" name="aktion" value="<?= $editTransaktion ? 'buchung_aktualisieren' : 'buchung_speichern' ?>">
                <?php if ($editTransaktion): ?><input type="hidden" name="id" value="<?= (int)$editTransaktion['id'] ?>"><?php endif; ?>
                <label>Typ<select name="typ" required><option value="Einnahme"<?= selected('Einnahme', $editTransaktion && $editTransaktion['betrag'] > 0 ? 'Einnahme' : 'Ausgabe') ?>>Geldeingang</option><option value="Ausgabe"<?= selected('Ausgabe', $editTransaktion && $editTransaktion['betrag'] > 0 ? 'Einnahme' : 'Ausgabe') ?>>Zahlung</option></select></label>
                <label>Betrag<input type="number" name="betrag" min="0.01" step="0.01" required value="<?= $editTransaktion ? htmlspecialchars((string)abs((float)$editTransaktion['betrag'])) : '' ?>"></label>
                <label>Thema<select name="thema_id" required><option value="">Bitte wählen</option><?php foreach ($themen as $thema): ?><option value="<?= (int)$thema['id'] ?>"<?= selected($thema['id'], $editTransaktion['thema_id'] ?? '') ?>><?= htmlspecialchars($thema['name']) ?></option><?php endforeach; ?></select></label>
                <label>Datum<input type="date" name="datum" required value="<?= htmlspecialchars($editTransaktion['datum'] ?? date('Y-m-d')) ?>"></label>
                <label class="full">Details<input type="text" name="beschreibung" maxlength="255" value="<?= htmlspecialchars($editTransaktion['beschreibung'] ?? '') ?>" placeholder="z. B. Wocheneinkauf bei REWE"></label>
                <label class="full">Tags<select name="tag_ids[]" multiple size="4"><?php foreach ($tags as $tag): ?><option value="<?= (int)$tag['id'] ?>"<?= in_array((int)$tag['id'], $editTagIds, true) ? ' selected' : '' ?>><?= htmlspecialchars($tag['name']) ?></option><?php endforeach; ?></select><small>Mehrfachauswahl mit Strg/Cmd.</small></label>
                <div class="form-actions full"><button class="button primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $editTransaktion ? 'Änderungen speichern' : 'Buchung speichern' ?></button><?php if ($editTransaktion): ?><a class="button ghost" href="index.php#buchungsformular">Abbrechen</a><?php endif; ?></div>
            </form>
        </article>
    </section>

    <section class="management-grid">
        <article class="panel compact"><div class="panel-heading"><div><p class="eyebrow">Verwaltung</p><h2>Neue Kategorie</h2></div></div><form method="post" class="inline-form"><input type="hidden" name="aktion" value="thema_erstellen"><input type="text" name="name" required maxlength="50" placeholder="z. B. Gesundheit"><select name="thema_typ" required><option value="ausgabe">Ausgabe</option><option value="einnahme">Einnahme</option></select><button class="button secondary" type="submit">Erstellen</button></form></article>
        <article class="panel compact"><div class="panel-heading"><div><p class="eyebrow">Verwaltung</p><h2>Neuer Tag</h2></div></div><form method="post" class="inline-form"><input type="hidden" name="aktion" value="tag_erstellen"><input type="text" name="name" required maxlength="50" placeholder="z. B. Vertrag"><button class="button secondary" type="submit">Erstellen</button></form></article>
    </section>

    <section class="panel bookings-panel">
        <div class="panel-heading"><div><p class="eyebrow">Übersicht</p><h2>Letzte Buchungen</h2></div><span class="result-count"><?= count($transaktionen) ?> Treffer</span></div>
        <form method="get" class="filter-bar">
            <label>Art<select name="filter_typ"><option value="alle"<?= selected('alle', $filterTyp) ?>>Alle</option><option value="ausgabe"<?= selected('ausgabe', $filterTyp) ?>>Zahlungen</option><option value="einnahme"<?= selected('einnahme', $filterTyp) ?>>Geldeingänge</option></select></label>
            <label>Thema<select name="filter_thema"><option value="0">Alle Themen</option><?php foreach ($themen as $thema): ?><option value="<?= (int)$thema['id'] ?>"<?= selected($thema['id'], $filterThema) ?>><?= htmlspecialchars($thema['name']) ?></option><?php endforeach; ?></select></label>
            <label>Tag<select name="filter_tag"><option value="0">Alle Tags</option><?php foreach ($tags as $tag): ?><option value="<?= (int)$tag['id'] ?>"<?= selected($tag['id'], $filterTag) ?>><?= htmlspecialchars($tag['name']) ?></option><?php endforeach; ?></select></label>
            <label>Sortieren nach<select name="sort"><option value="datum"<?= selected('datum', $sort) ?>>Datum</option><option value="betrag"<?= selected('betrag', $sort) ?>>Betrag</option><option value="details"<?= selected('details', $sort) ?>>Details</option></select></label>
            <label>Reihenfolge<select name="richtung"><option value="desc"<?= selected('desc', strtolower($richtung)) ?>>Absteigend</option><option value="asc"<?= selected('asc', strtolower($richtung)) ?>>Aufsteigend</option></select></label>
            <button class="button primary" type="submit"><i class="bi bi-funnel"></i> Filtern</button><a class="button ghost" href="index.php">Zurücksetzen</a>
        </form>
        <div class="table-wrap"><table><thead><tr><th>Datum</th><th>Art</th><th>Thema</th><th>Tags</th><th>Details</th><th class="number">Betrag</th><th>Aktionen</th></tr></thead><tbody>
        <?php if (!$transaktionen): ?><tr><td colspan="7" class="empty">Keine passenden Buchungen vorhanden.</td></tr><?php endif; ?>
        <?php foreach ($transaktionen as $row): ?><tr><td><?= date('d.m.Y', strtotime($row['datum'])) ?></td><td><span class="badge <?= $row['betrag'] >= 0 ? 'badge-income' : 'badge-expense' ?>"><?= $row['betrag'] >= 0 ? 'Geldeingang' : 'Zahlung' ?></span></td><td><?= htmlspecialchars($row['thema_name'] ?? 'Ohne Thema') ?></td><td><?= htmlspecialchars($row['tag_namen'] ?: '–') ?></td><td><?= htmlspecialchars($row['beschreibung'] ?: '–') ?></td><td class="number amount <?= $row['betrag'] >= 0 ? 'positive' : 'negative' ?>"><?= $row['betrag'] >= 0 ? '+' : '' ?><?= number_format((float)$row['betrag'], 2, ',', '.') ?> €</td><td><div class="actions"><a class="icon-button edit" title="Verändern" href="?edit=<?= (int)$row['id'] ?>#buchungsformular"><i class="bi bi-pencil-square"></i></a><form method="post" onsubmit="return confirm('Buchung wirklich löschen?');"><input type="hidden" name="aktion" value="buchung_loeschen"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="icon-button delete" type="submit" title="Löschen"><i class="bi bi-trash3"></i></button></form></div></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
</main>
<script>
const ctx = document.getElementById('saldoChart');
new Chart(ctx, {type: 'bar', data: {labels: <?= json_encode($monthlyLabels, JSON_UNESCAPED_UNICODE) ?>, datasets: [{label: 'Saldo', data: <?= json_encode($monthlyValues) ?>, backgroundColor: (c) => c.raw >= 0 ? '#16a34a' : '#dc2626', borderRadius: 6}]}, options: {responsive: true, maintainAspectRatio: false, plugins: {legend: {display: false}, tooltip: {callbacks: {label: c => new Intl.NumberFormat('de-DE', {style:'currency', currency:'EUR'}).format(c.raw)}}}, scales: {y: {ticks: {callback: v => v.toLocaleString('de-DE') + ' €'}, grid: {color: '#e5e7eb'}}, x: {grid: {display: false}}}}});
</script>
<script src="./script.js"></script>
</body>
</html>
