<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/dao/ThemaDAO.php';

$themaDAO = new ThemaDAO();

// CREATE & UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($action === 'create' && !empty($name)) {
        $themaDAO->createThema($name);
    } elseif ($action === 'update' && !empty($name)) {
        $id = (int)$_POST['id'];
        $themaDAO->updateThema($id, $name);
    }
    header("Location: themen.php");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $themaDAO->deleteThema((int)$_GET['delete']);
    header("Location: themen.php");
    exit;
}

// EDIT (Thema zum Bearbeiten laden)
$editThema = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($themaDAO->getAllThemen() as $t) {
        if ($t['id'] == $editId) {
            $editThema = $t;
            break;
        }
    }
}

// READ
$themen = $themaDAO->getAllThemen();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Themen verwalten</title>
</head>
<body>
    <h1>Themen verwalten</h1>

    <!-- FORMULAR (Create / Update) -->
    <form action="themen.php" method="POST">
        <input type="hidden" name="action" value="<?= $editThema ? 'update' : 'create' ?>">
        <?php if ($editThema): ?>
            <input type="hidden" name="id" value="<?= $editThema['id'] ?>">
        <?php endif; ?>

        <input type="text" name="name" placeholder="Thema-Name" value="<?= htmlspecialchars($editThema['name'] ?? '') ?>" required>
        <button type="submit"><?= $editThema ? 'Speichern' : 'Hinzufügen' ?></button>
        <?php if ($editThema): ?>
            <a href="themen.php">Abbrechen</a>
        <?php endif; ?>
    </form>

    <hr>

    <!-- TABELLE (Read) -->
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