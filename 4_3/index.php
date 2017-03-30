<?php
session_start();
if (!isset($_SESSION['user'])){die("<a href='register.php'>Войдите на сайт</a>");}
$description = isset($_POST["description"]) ? (string)$_POST["description"] : '';
$pdo = new PDO("mysql:host=127.0.0.1;dbname=ganja;charset=utf8", "ganja", "");
$action = isset($_GET['action']) ? $_GET['action'] : null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $description !== '' && $action !== 'edit'){
    $sql = "INSERT INTO task (id, user_id, assigned_user_id, description, is_done, date_added) VALUES (null, :id, null, :description, 0, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $_SESSION['user']['id'], 'description' => $description]);
    $tasks = $stmt->fetch();
    header('Location: index.php');
    die;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $description !== '' && $action === 'edit'){
    $sql = "UPDATE task SET description = :description WHERE id = :id LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['description' => $description, 'id' => (int)$_GET['id']]);
    $tasks = $stmt->fetch();
    header('Location: index.php');
    die;
}
if ($action === 'delete'){
    $sql = "DELETE FROM task WHERE id = :id LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => (int)$_GET['id']]);
    $tasks = $stmt->fetch();
    header('Location: index.php');
    die;
}
if ($action === 'done'){
    $sql = "UPDATE task SET is_done = 1 WHERE id = :id LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => (int)$_GET['id']]);
    $tasks = $stmt->fetch();
    header('Location: index.php');
    die;
}
if ($action === 'edit'){
    $sql = "SELECT description FROM task WHERE id = :id LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => (int)$_GET['id']]);
    $tasks = $stmt->fetch();
    $description = $tasks['description'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["assigned_user_id"])){
    $data = explode('_', $_POST["assigned_user_id"]);
    $sql = "UPDATE task SET assigned_user_id = {$data[1]} WHERE id = {$data[3]} LIMIT 1;";
    $pdo->query($sql);
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

    <h1>Здравствуйте, <?= $_SESSION['user']['login'] ?>! Вот ваш список дел:</h1>
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
            <th>Ответственный</th>
            <th>Автор</th>
            <th>Закрепить задачу за пользователем</th
        </tr>
    <?php
    $sql = "SELECT t.id, u.login AS autor, au.login as assigned, t.description, t.is_done, t.date_added
            FROM task AS t
            JOIN user AS u ON u.id=t.user_id
            LEFT JOIN user AS au on au.id=t.assigned_user_id";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["sort_by"])) {
        if ($_POST["sort_by"] === 'date_created'){$sql = $sql . " ORDER BY t.date_added";}
        if ($_POST["sort_by"] === 'is_done'){$sql = $sql . " ORDER BY t.is_done";}
        if ($_POST["sort_by"] === 'description'){$sql = $sql . " ORDER BY t.description";}
    } else {$sql = $sql . " ORDER BY t.id";}
    foreach ($pdo->query($sql) as $row):
        if ($row['autor'] !== $_SESSION['user']['login']) {continue;}?>
    <tr>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= $row['date_added'] ?></td>
        <td><span style='color: <?php if ($row['is_done'] == 1){echo 'green;\'>Выполнено';} else {echo 'red;\'>В процессе';} ?><!--'--></span></td>
        <td>
            <a href='?id=<?= $row['id'] ?>&action=edit'>Изменить</a>
            <?php if ($row['assigned'] === null) { ?>
            <a href='?id=<?= $row['id'] ?>&action=done'>Выполнить</a>
            <?php } ?>
            <a href='?id=<?= $row['id'] ?>&action=delete'>Удалить</a>
        </td>
        <td><?php if ($row['assigned'] !== null){echo $row['assigned'];} else {echo 'Вы';} ?></td>
        <td><?= $row['autor'] ?></td>
        <td>
            <form method='POST'>
            <select name='assigned_user_id'>
                <?php
                $sql_id = "SELECT id, login FROM user WHERE id <> {$_SESSION['user']['id']};";
                foreach ($pdo->query($sql_id) as $row_id):?>
                <option value=<?php echo '\'user_' . $row_id['id']  .'_task_' . $row['id'] . '\'>' . $row_id['login']; ?>
                <?php endforeach ?>
                </option>
            </select>
            <input type='submit' name='assign' value='Переложить ответственность' />
            </form>
        </td>
    </tr>
    <?php endforeach ?>
    </table>
    <p><strong>Также, посмотрите, что от Вас требуют другие люди:</strong></p>
    <table>
    <tr>
        <th>Описание задачи</th>
        <th>Дата добавления</th>
        <th>Статус</th>
        <th></th>
        <th>Ответственный</th>
        <th>Автор</th>
    </tr>
    <?php
    $sql = "SELECT t.id, u.login AS autor, au.login as assigned, t.description, t.is_done, t.date_added
            FROM task AS t
            JOIN user AS u ON u.id=t.user_id
            LEFT JOIN user AS au on au.id=t.assigned_user_id";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["sort_by"])) {
        if ($_POST["sort_by"] === 'date_created'){$sql = $sql . " ORDER BY t.date_added";}
        if ($_POST["sort_by"] === 'is_done'){$sql = $sql . " ORDER BY t.is_done";}
        if ($_POST["sort_by"] === 'description'){$sql = $sql . " ORDER BY t.description";}
    } else {$sql = $sql . " ORDER BY t.id";}
    foreach ($pdo->query($sql) as $row):
        if ($row['assigned'] !== $_SESSION['user']['login']) {continue;}?>
    <tr>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= $row['date_added'] ?></td>
        <td><span style='color: <?php if ($row['is_done'] == 1){echo 'green;\'>Выполнено';} else {echo 'red;\'>В процессе';} ?><!--'--></span></td>
        <td>
            <a href='?id=<?= $row['id'] ?>&action=edit'>Изменить</a>
            <a href='?id=<?= $row['id'] ?>&action=done'>Выполнить</a>
            <a href='?id=<?= $row['id'] ?>&action=delete'>Удалить</a>
        </td>
        <td><?= $row['assigned'] ?></td>
        <td><?= $row['autor'] ?></td>
    </tr>
    <?php endforeach ?>
    </table>
    </br>
    <a href="logout.php">Выход</a><br/>
</body>
</html>
