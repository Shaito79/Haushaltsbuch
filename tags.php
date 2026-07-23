<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/dao/TagDAO.php';

$tagDAO = new TagDAO();
$errors = [];

// CREATE & UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    // VALIDIERUNG
    if (empty($name)) {
        $errors[] = "Der Tag-Name darf nicht leer sein.";
    } elseif (mb_strlen($name) > 50) {
        $errors[] = "Der Name darf maximal 50 Zeichen lang sein.";
    }

    // SPEICHERN (mit try-catch)
    if (empty($errors)) {
        try {
            if ($action === 'create') {
                $tagDAO->createTag($name);
                header("Location: tags.php");
                exit;
            } elseif ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $tagDAO->updateTag($id, $name);
                    header("Location: tags.php");
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

// DELETE (mit try-catch für Foreign Keys)
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId > 0) {
        try {
            $tagDAO->deleteTag($deleteId);
            header("Location: tags.php");
            exit;
        } catch (PDOException $e) {
            // Code 23000 = Foreign Key Constraint (Wird noch verwendet)
            if ($e->getCode() == 23000) {
                $errors[] = "Dieser Tag kann nicht gelöscht werden, da er noch in Transaktionen verwendet wird.";
            } else {
                $errors[] = "Fehler beim Löschen: " . $e->getMessage();
            }
        }
    }
}

// EDIT & READ (mit try-catch)
$editTag = null;
$tags = [];

try {
    if (isset($_GET['edit'])) {
        $editId = (int)$_GET['edit'];
        foreach ($tagDAO->getAllTags() as $t) {
            if ($t['id'] == $editId) {
                $editTag = $t;
                break;
            }
        }
    }
    $tags = $tagDAO->getAllTags();
} catch (PDOException $e) {
    $errors[] = "Fehler beim Laden der Tags: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Tags verwalten</title>
</head>
<body>
    <h1>Tags verwalten</h1>

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
    <form action="tags.php" method="POST">
        <input type="hidden" name="action" value="<?= $editTag ? 'update' : 'create' ?>">
        <?php if ($editTag): ?>
            <input type="hidden" name="id" value="<?= $editTag['id'] ?>">
        <?php endif; ?>

        <input type="text" name="name" placeholder="Tag-Name" value="<?= htmlspecialchars($_POST['name'] ?? $editTag['name'] ?? '') ?>" required>
        <button type="submit"><?= $editTag ? 'Speichern' : 'Hinzufügen' ?></button>
        <?php if ($editTag): ?>
            <a href="tags.php">Abbrechen</a>
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