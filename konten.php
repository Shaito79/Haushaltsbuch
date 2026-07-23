<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/dao/KontoDAO.php';

$kontoDAO = new KontoDAO();
$errors = [];

// CREATE & UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    // VALIDIERUNG
    if (empty($name)) {
        $errors[] = "Der Konto-Name darf nicht leer sein.";
    } elseif (mb_strlen($name) > 50) {
        $errors[] = "Der Name darf maximal 50 Zeichen lang sein.";
    }

    // SPEICHERN (mit try-catch)
    if (empty($errors)) {
        try {
            if ($action === 'create') {
                $kontoDAO->createKonto($name);
                header("Location: konten.php");
                exit;
            } elseif ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $kontoDAO->updateKonto($id, $name);
                    header("Location: konten.php");
                    exit;
                } else {
                    $errors[] = "Ungültige ID.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Fehler beim Speichern: " . $e->getMessage();
        }
    }
}

// DELETE (mit try-catch)
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId > 0) {
        try {
            $kontoDAO->deleteKonto($deleteId);
            header("Location: konten.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Dieses Konto kann nicht gelöscht werden, da noch Transaktionen darauf gebucht sind.";
            } else {
                $errors[] = "Fehler beim Löschen: " . $e->getMessage();
            }
        }
    }
}

// EDIT & READ (mit try-catch)
$editKonto = null;
$konten = [];

try {
    if (isset($_GET['edit'])) {
        $editId = (int)$_GET['edit'];
        foreach ($kontoDAO->getAllKonten() as $k) {
            if ($k['id'] == $editId) {
                $editKonto = $k;
                break;
            }
        }
    }
    $konten = $kontoDAO->getAllKonten();
} catch (PDOException $e) {
    $errors[] = "Fehler beim Laden der Konten: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Konten verwalten</title>
</head>
<body>
    <h1>Konten verwalten</h1>

    <!-- FEHLERMELDUNGEN ANZEIGEN -->
    <?php if (!empty($errors)): ?>
        <div style="color: red; margin-bottom: 15px;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- FORMULAR -->
    <form action="konten.php" method="POST">
        <input type="hidden" name="action" value="<?= $editKonto ? 'update' : 'create' ?>">
        <?php if ($editKonto): ?>
            <input type="hidden" name="id" value="<?= $editKonto['id'] ?>">
        <?php endif; ?>

        <input type="text" name="name" placeholder="Konto-Name" value="<?= htmlspecialchars($_POST['name'] ?? $editKonto['name'] ?? '') ?>" required>
        <button type="submit"><?= $editKonto ? 'Speichern' : 'Hinzufügen' ?></button>
        <?php if ($editKonto): ?>
            <a href="konten.php">Abbrechen</a>
        <?php endif; ?>
    </form>

    <hr>

    <!-- TABELLE -->
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Aktionen</th>
        </tr>
        <?php foreach ($konten as $konto): ?>
            <tr>
                <td><?= $konto['id'] ?></td>
                <td><?= htmlspecialchars($konto['name']) ?></td>
                <td>
                    <a href="konten.php?edit=<?= $konto['id'] ?>">Bearbeiten</a> | 
                    <a href="konten.php?delete=<?= $konto['id'] ?>" onclick="return confirm('Wirklich löschen?')">Löschen</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>