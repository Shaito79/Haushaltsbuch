<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/dao/TagDAO.php';

$tagDAO = new TagDAO();

// CREATE & UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($action === 'create' && !empty($name)) {
        $tagDAO->createTag($name);
    } elseif ($action === 'update' && !empty($name)) {
        $id = (int)$_POST['id'];
        $tagDAO->updateTag($id, $name);
    }
    header("Location: tags.php");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $tagDAO->deleteTag((int)$_GET['delete']);
    header("Location: tags.php");
    exit;
}

// EDIT (Tag zum Bearbeiten laden)
$editTag = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($tagDAO->getAllTags() as $t) {
        if ($t['id'] == $editId) {
            $editTag = $t;
            break;
        }
    }
}

// READ
$tags = $tagDAO->getAllTags();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Tags verwalten</title>
</head>
<body>
    <h1>Tags verwalten</h1>

    <!-- FORMULAR (Create / Update) -->
    <form action="tags.php" method="POST">
        <input type="hidden" name="action" value="<?= $editTag ? 'update' : 'create' ?>">
        <?php if ($editTag): ?>
            <input type="hidden" name="id" value="<?= $editTag['id'] ?>">
        <?php endif; ?>

        <input type="text" name="name" placeholder="Tag-Name" value="<?= htmlspecialchars($editTag['name'] ?? '') ?>" required>
        <button type="submit"><?= $editTag ? 'Speichern' : 'Hinzufügen' ?></button>
        <?php if ($editTag): ?>
            <a href="tags.php">Abbrechen</a>
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
        <?php foreach ($tags as $tag): ?>
            <tr>
                <td><?= $tag['id'] ?></td>
                <td><?= htmlspecialchars($tag['name']) ?></td>
                <td>
                    <a href="tags.php?edit=<?= $tag['id'] ?>">Bearbeiten</a> | 
                    <a href="tags.php?delete=<?= $tag['id'] ?>" onclick="return confirm('Wirklich löschen?')">Löschen</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>