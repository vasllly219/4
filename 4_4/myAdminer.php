<?php
session_start();
if (!isset($_SESSION['user'])){
    header('Location: authorization.php');
    die;
}
$table = 'Таблица';
$flug = false;
$tableName = isset($_POST["tableName"]) ? (string)$_POST["tableName"] : '';
$collumName = isset($_POST["collumName"]) ? (string)$_POST["collumName"] : '';
$action = isset($_GET['action']) ? $_GET['action'] : null;
$pdo = new PDO("mysql:host=127.0.0.1;dbname={$_SESSION['user']['dbname']};charset=utf8", $_SESSION['user']['login'], $_SESSION['user']['password']);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $tableName !== ''){
    $sql = "CREATE TABLE `$tableName` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $pdo->query($sql);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $collumName !== '' && $action !== 'edit'){
    $sql = "ALTER TABLE " . $_GET["table"] . " ADD " . $collumName . " " . $_POST["type"] . ";";
    $pdo->query($sql);
}
if (isset($_GET['table'])){
    $sql = "SHOW TABLES LIKE " . $pdo->quote($_GET['table']);
    foreach ($pdo->query($sql) as $row){
        $table = $_GET['table'];
        $flug = true;
    }
}
if ($action === 'delete'){
    $sql = "ALTER TABLE " . $_GET["table"] . " DROP COLUMN " . $_GET['collum'] . ";";
    $pdo->query($sql);
    header("Location: myAdminer.php?table=" . $_GET["table"]);
    die;
}
if ($action === 'edit'){
    $collumName = $_GET['collum'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $collumName !== '' && $action === 'edit'){
    $sql = "ALTER TABLE " . $_GET["table"] . " CHANGE " . $_GET['collum'] . " " . $_GET['collum'] . " " . $_POST["type"] . ";";
    $pdo->query($sql);
    header("Location: myAdminer.php?table=" . $_GET["table"]);
    die;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>myAdminer</title>
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
    <?php if ($flug) { ?>
        <h1>Таблица <?= $table ?>:</h1>
    <?php } else { ?>
        <h1>Здравствуйте, <?= $_SESSION['user']['login'] ?>!</h1>
        <p>Вот список таблиц из базы <?= $_SESSION['user']['dbname'] ?>:</p>
        <p>Убедительная просьба не трогать таблицы: task, tasks, user</p>
    <?php } ?>

    <div style="clear: both"></div>

    <table>
    <tr>
        <?php if ($flug) { ?>
            <th>Колонка</th>
            <th>Тип</th>
            <th></th>
        <?php } else { ?>
            <th>Таблица</th>
        <?php } ?>
    </tr>
    <?php
    if ($flug){
        $sql = "DESCRIBE $table;";
        foreach ($pdo->query($sql) as $row): ?>
        <tr>
            <td><?= $row[0] ?></td>
            <td><?= $row['Type'] ?></td>
            <td>
                <a href='?table=<?= $table ?>&action=edit&collum=<?= $row[0] ?>'>Изменить</a>
                <a href='?table=<?= $table ?>&action=delete&collum=<?= $row[0] ?>'>Удалить</a>
            </td>
        </tr>
        <?php endforeach;
    } else {
        $sql = "SHOW TABLES;";
        foreach ($pdo->query($sql) as $row): ?>
        <tr>
            <td><a href='?table=<?= $row[0] ?>'><?= $row[0] ?></a></td>
        </tr>
        <?php endforeach;
    } ?>
    </table>
    </br>
    <?php if(!$flug){ ?>
    <div style="float: left">
    <form method="POST">
       <input type="text" name="tableName" placeholder="Имя таблицы" value="<?= $tableName ?>" />
       <input type="submit" name="save" value="Добавить" />
    </form>
    </div>
    <?php } else { ?>
        <div style="float: left">
        <form method="POST">
           <input type="collumName" name="collumName" placeholder="Имя колонки" value="<?= $collumName ?>" />
           <label for="sort">Тип:</label>
           <select name="type">
               <option value="INT">INT</option>
               <option value="VARCHAR(50)">VARCHAR(50)</option>
               <option value="TIMESTAMP">TIMESTAMP;</option>
           </select>
           <input type="submit" name="save" value="<?php if ($action !== 'edit'){echo 'Добавить';} else {echo 'Сохранить';}?>" />
        </form>
        </br>
    <?php }
    if($flug){echo '<a href="myAdminer.php">Назад</a><br/>';}?>
</body>
</html>
