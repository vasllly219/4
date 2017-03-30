<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=ganja;charset=utf8", "ganja", "neto0904");
$sql = "SELECT t.id, u.login AS autor, t.assigned_user_id, t.description, t.is_done, t.date_added
        FROM task AS t
        JOIN user AS u ON u.id = t.user_id";
/*$sql = "SELECT t.id, u.login AS autor, au.login as assigned, t.description, t.is_done, t.date_added
                FROM task AS t
                JOIN user AS u ON u.id=t.user_id
                JOIN user AS au on au.id=t.assigned_user_id
                ORDER BY t.id";*/
foreach ($pdo->query($sql) as $row){
    var_dump($row);
    echo '</br>';
}
echo '333';
