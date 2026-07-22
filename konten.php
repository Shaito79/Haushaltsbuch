<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/dao/KontoDAO.php';

$kontoDAO = new KontoDAO();

// CREATE & UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($action === 'create' && !empty($name)) {
        $kontoDAO->createKonto($name);
    } elseif ($action === 'update' && !empty($name)) {
        $id = (int)$_POST['id'];
        $kontoDAO->updateKonto($id, $name);
    }
    header("Location: konten.php");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $kontoDAO->deleteKonto((int)$_GET['delete']);
    header("Location: konten.php");
    exit;
}

// EDIT (Konto zum Bearbeiten laden)
$editKonto = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($kontoDAO->getAllKonten() as $k) {
        if ($k['id'] == $editId) {
            $editKonto = $k;
            break;
        }
    }
}

// READ
$konten = $kontoDAO->getAllKonten();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Konten verwalten</title>
</head>
<body>
    <h1>Konten verwalten</h1>

    <!-- FORMULAR (Create / Update) -->
    <form action="konten.php" method="POST">
        <input type="hidden" name="action" value="<?= $editKonto ? 'update' : 'create' ?>">
        <?php if ($editKonto): ?>
            <input type="hidden" name="id" value="<?= $editKonto['id'] ?>">
        <?php endif; ?>

        <input type="text" name="name" placeholder="Konto-Name" value="<?= htmlspecialchars($editKonto['name'] ?? '') ?>" required>
        <button type="submit"><?= $editKonto ? 'Speichern' : 'Hinzufügen' ?></button>
        <?php if ($editKonto): ?>
            <a href="konten.php">Abbrechen</a>
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