<?php
$description = isset($_POST["description"]) ? (string)$_POST["description"] : '';
$pdo = new PDO("mysql:host=127.0.0.1;dbname=ganja;charset=utf8", "ganja", "neto0904");
$action = isset($_GET['action']) ? $_GET['action'] : null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $description !== '' && $action !== 'edit'){
    $sql = "INSERT INTO tasks (id, description, is_done, date_added) VALUES (null, ?, 0, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$description]);
    $tasks = $stmt->fetch();
    header('Location: index.php');
    die;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $description !== '' && $action === 'edit'){
    $sql = "UPDATE tasks SET description = ? WHERE id = ? LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$description, (int)$_GET['id']]);
    $tasks = $stmt->fetch();
    header('Location: index.php');
    die;
}
if ($action === 'delete'){
    $sql = "DELETE FROM tasks WHERE id = ? LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int)$_GET['id']]);
    $tasks = $stmt->fetch();
    header('Location: index.php');
    die;
}
if ($action === 'done'){
    $sql = "UPDATE tasks SET is_done = 1 WHERE id = ? LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int)$_GET['id']]);
    $tasks = $stmt->fetch();
    header('Location: index.php');
    die;
}
if ($action === 'edit'){
    $sql = "SELECT description FROM tasks WHERE id = ? LIMIT 1;;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int)$_GET['id']]);
    $tasks = $stmt->fetch();
    $description = $tasks['description'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>TODO</title>
</head>
<body>
    <style>
        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        table td, table th {
            border: 1px solid #ccc;
            padding: 5px;
        }

        table th {
            background: #eee;
        }
    </style>

    <h1>Список дел на сегодня</h1>
    <div style="float: left">
        <form method="POST">
            <input type="text" name="description" placeholder="Описание задачи" value="<?= $description ?>" />
            <input type="submit" name="save" value="<?php if ($action !== 'edit'){echo 'Добавить';} else {echo 'Сохранить';}?>" />
        </form>
    </div>
    <div style="float: left; margin-left: 20px;">
        <form method="POST">
            <label for="sort">Сортировать по:</label>
            <select name="sort_by">
                <option value="date_created">Дате добавления</option>
                <option value="is_done">Статусу</option>
                <option value="description">Описанию</option>
            </select>
            <input type="submit" name="sort" value="Отсортировать" />
        </form>
    </div>
    <div style="clear: both"></div>
    </br>
    <table>
        <tr>
            <th>Описание задачи</th>
            <th>Дата добавления</th>
            <th>Статус</th>
            <th></th>
        </tr>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["sort_by"])) {
        if ($_POST["sort_by"] === 'date_created'){$sql = "SELECT * FROM tasks ORDER BY date_added";}
        if ($_POST["sort_by"] === 'is_done'){$sql = "SELECT * FROM tasks ORDER BY is_done";}
        if ($_POST["sort_by"] === 'description'){$sql = "SELECT * FROM tasks ORDER BY description";}
    } else {$sql = "SELECT * FROM tasks ORDER BY id";}
    foreach ($pdo->query($sql) as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= $row['date_added'] ?></td>
        <td><span style='color: <?php if ($row['is_done'] == 1){echo 'green;\'>Выполнено';} else {echo 'red;\'>В процессе';} ?></span></td>
        <td>
            <a href='?id=<?= $row['id'] ?>&action=edit'>Изменить</a>
            <a href='?id=<?= $row['id'] ?>&action=done'>Выполнить</a>
            <a href='?id=<?= $row['id'] ?>&action=delete'>Удалить</a>
        </td>
    </tr>
    <?php endforeach ?>
    </table>
</body>
</html>
