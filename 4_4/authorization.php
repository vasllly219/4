<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dbname = isset($_REQUEST['dbname']) ? $_REQUEST['dbname'] : null;
    $login = isset($_REQUEST['login']) ? $_REQUEST['login'] : null;
    $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;
    $hash = md5($login . ':::' . $password);
    if ($dbname === 'ganja' && $login ==='ganja' && $hash ==='f55c0696360ebcef312d83f072278e08') {
        $_SESSION['user'] = ['dbname' => $dbname, 'login' => $login, 'password' => $password];
        header('Location: myAdminer.php');
        die;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>myAdminer</title>
</head>
<div>
<body>
<form method="POST">
    	<h1>Вход в базу</h1>
        <input type="text" name="dbname" placeholder="dbname" required="" autofocus=""></br>
    	<input type="text" name="login" placeholder="Login" required="" autofocus=""></br>
    	<input type="password" name="password" placeholder="Password" required="" autofocus=""></br>
        <button type="submit">Войти</button>
</form>
</div>
</body>
</html>
