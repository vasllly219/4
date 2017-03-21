<?php
$isbn = '';
$name = '';
$author = '';
if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    $isbn = isset($_GET["isbn"]) ? (string)$_GET["isbn"] : null;
    $name = isset($_GET["name"]) ? (string)$_GET["name"] : null;
    $author = isset($_GET["author"]) ? (string)$_GET["author"] : null;
}
$pdo = new PDO("mysql:host=127.0.0.1;dbname=global;charset=utf8", "ganja", "");
$sql = "SELECT * FROM books WHERE isbn LIKE " . $pdo->quote('%' . $isbn . '%') . " AND name LIKE " . $pdo->quote('%' . $name . '%') . " AND author LIKE " . $pdo->quote('%' . $author . '%');
//$sql = "SELECT * FROM books WHERE isbn LIKE :isbn AND name LIKE :name AND author LIKE :author);
//$statement = $pdo->prepare($sql);
//$statement->execute(array(":isbn" => '%' . $isbn . '%', ":name" => '%' . $name . '%', ":author" => '%' . $author . '%'));
//var_dump($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Библиотека</title>
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
    <h1>Библиотека успешного человека</h1>
    <form method="GET">
        <input type="text" name="isbn" placeholder="ISBN" value="<?= $isbn ?>" />
        <input type="text" name="name" placeholder="Название книги" value="<?= $name ?>" />
        <input type="text" name="author" placeholder="Автор книги" value="<?= $author ?>" />
        <input type="submit" value="Поиск" />
    </form>
    </br>
    <table>
    <tr>
        <th>Название</th>
        <th>Автор</th>
        <th>Год выпуска</th>
        <th>Жанр</th>
        <th>ISBN</th>
    </tr>
    <?php foreach ($pdo->query($sql) as $row): ?>
    <tr>
        <td><?= $row['name'] ?></td>
        <td><?= $row['author'] ?></td>
        <td><?= $row['year'] ?></td>
        <td><?= $row['genre'] ?></td>
        <td><?= $row['isbn'] ?></td>
    </tr>
    <?php endforeach ?>
    </table>
</body>
</html>
