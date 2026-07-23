<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/dao/ThemaDAO.php';

$themaDAO = new ThemaDAO();
$errors = [];

// CREATE & UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    // VALIDIERUNG
    if (empty($name)) {
        $errors[] = "Der Thema-Name darf nicht leer sein.";
    } elseif (mb_strlen($name) > 50) {
        $errors[] = "Der Name darf maximal 50 Zeichen lang sein.";
    }

    // SPEICHERN (mit try-catch)
    if (empty($errors)) {
        try {
            if ($action === 'create') {
                $themaDAO->createThema($name);
                header("Location: themen.php");
                exit;
            } elseif ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $themaDAO->updateThema($id, $name);
                    header("Location: themen.php");
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
            $themaDAO->deleteThema($deleteId);
            header("Location: themen.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Dieses Thema kann nicht gelöscht werden, da es noch in Transaktionen verwendet wird.";
            } else {
                $errors[] = "Fehler beim Löschen: " . $e->getMessage();
            }
        }
    }
}

// EDIT & READ (mit try-catch)
$editThema = null;
$themen = [];

try {
    if (isset($_GET['edit'])) {
        $editId = (int)$_GET['edit'];
        foreach ($themaDAO->getAllThemen() as $t) {
            if ($t['id'] == $editId) {
                $editThema = $t;
                break;
            }
        }
    }
    $themen = $themaDAO->getAllThemen();
} catch (PDOException $e) {
    $errors[] = "Fehler beim Laden der Themen: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Themen verwalten</title>
</head>
<body>
    <h1>Themen verwalten</h1>

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
    <form action="themen.php" method="POST">
        <input type="hidden" name="action" value="<?= $editThema ? 'update' : 'create' ?>">
        <?php if ($editThema): ?>
            <input type="hidden" name="id" value="<?= $editThema['id'] ?>">
        <?php endif; ?>

        <input type="text" name="name" placeholder="Thema-Name" value="<?= htmlspecialchars($_POST['name'] ?? $editThema['name'] ?? '') ?>" required>
        <button type="submit"><?= $editThema ? 'Speichern' : 'Hinzufügen' ?></button>
        <?php if ($editThema): ?>
            <a href="themen.php">Abbrechen</a>
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
        <?php foreach ($themen as $thema): ?>
            <tr>
                <td><?= $thema['id'] ?></td>
                <td><?= htmlspecialchars($thema['name']) ?></td>
                <td>
                    <a href="themen.php?edit=<?= $thema['id'] ?>">Bearbeiten</a> | 
                    <a href="themen.php?delete=<?= $thema['id'] ?>" onclick="return confirm('Wirklich löschen?')">Löschen</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>