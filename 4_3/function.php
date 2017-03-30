<?php
session_start();
date_default_timezone_set('UTC');
define('FILE_ERRORS', __DIR__ . '/error.json');
define('CAPTCHA_FILE', __DIR__ . '/captcha.json');

function getFlug(){
    $data = getData(FILE_ERRORS);
    $ip = getClientIp();
    if (isset($data[$ip])){
        return $data[$ip];
    }
    return 0;
}

function getData($fileName)
{
    $data = [];
    if (file_exists($fileName)) {
        $data = json_decode(file_get_contents($fileName), true);
        if (!$data) {
            return [];
        }
    }
    return $data;
}

function getClientIp()
{
    return $_SERVER['REMOTE_ADDR'];
}

function isPOST()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function getParam($name, $defaultValue = null)
{
    return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $defaultValue;
}

function auth($login, $password)
{
    $userData = getUser($login);
    $hash = hashPassword($login, $password);
    if (!$userData || $userData['password'] != $hash) {
        return false;
    }
    $_SESSION['user'] = ['id' => (int)$userData['id'], 'login' => $userData['login']];
    return true;
}

function getUser($login)
{
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=ganja;charset=utf8", "ganja", "");
    $sql = "SELECT * FROM user";
    foreach ($pdo->query($sql) as $row){
        if ($row['login'] === $login){
            return $row;
        }
    }
    return false;
}

function hashPassword($login, $password)
{
    return md5($login . ':::' . $password);
}

function setError($code){
    $data = getData(FILE_ERRORS);
    $ip = getClientIp();
    if ($code === 0){
        $data[$ip] = 0;
        file_put_contents(FILE_ERRORS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return 0;
    }
    if ($code === 8){
        $data['blocklist'][$ip] = time();
        $data[$ip] = 9;
        file_put_contents(FILE_ERRORS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return 9;
    }
    foreach ($data as $key => $value) {
        if ($key === $ip){
            $data[$ip] = $value + 1;
            file_put_contents(FILE_ERRORS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return $value;
        }
    }
    $data[$ip] = 1;
    file_put_contents(FILE_ERRORS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    return 1;
}

function registration($login, $password){
    if ($userData = getUser($login)){
        return false;
    } else {
        $_SESSION['user'] = setUser($login, $password);
        return true;
    }
}

function setUser($login, $password){//FIXME
    $hash = hashPassword($login, $password);
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=ganja;charset=utf8", "ganja", "");
    $sql = "INSERT INTO user (id, login, password) VALUES (null, :login, :password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['login' => $login, 'password' => $hash]);
    $tasks = $stmt->fetch();
    $sql = "SELECT * FROM user";
    foreach ($pdo->query($sql) as $row){
        if ($row['login'] === $login){
            return ['id' => (int)$row['id'], 'login' => $row['login']];
        }
    }
}

function logout()
{
    session_destroy();
}

//функции к captcha.php

function generateCaptchaText($length = 6)
{
    $symbols = '12345678890qwertyuiopasdfghjklzxcvbnm';
    $result = [];
    for($i = 0; $i < $length; $i++) {
        $result[$i] = $symbols[mt_rand(0, strlen($symbols) - 1)];
    }
    return implode('', $result);
}

function saveCaptcha($code)
{
    $ip = getClientIp();
    $data = getCaptchaCodes();
    $data[$ip] = $code;
    file_put_contents(CAPTCHA_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function getCaptchaCodes()
{
    if (!file_exists(CAPTCHA_FILE)) {
        return [];
    }
    $data = file_get_contents(CAPTCHA_FILE);
    $data = json_decode($data, true);
    if (!$data) {
        $data = [];
    }
    return $data;
}

function renderCaptcha($code)
{
    $im = imagecreatetruecolor(320, 240);
    // RGB
    $backColor = imagecolorallocate($im, 255, 224, 221);
    $textColor = imagecolorallocate($im, 129, 15, 90);
    $fontFile = __DIR__ . '/assets/FONT.TTF';
    $imBox = imagecreatefrompng(__DIR__ . '/assets/captcha.png');
    imagefill($im, 0, 0, $backColor);
    imagecopy($im, $imBox, 0, 0, 0, 0, 320, 240);
    imagettftext($im, 25, 0, 30, 30, $textColor, $fontFile, $code);
    imagepng($im);
    imagedestroy($im);
}

function checkCaptcha($code) {
    $ip = getClientIp();
    $codes = getCaptchaCodes();
    if(isset($codes[$ip]) && strcmp($codes[$ip], $code) === 0) {
        return true;
    } else {
        return false;
    }
}

function getTimeBlock(){
  $data = getData(FILE_ERRORS);
  $ip = getClientIp();
  foreach ($data['blocklist'] as $key => $value) {
    if ($key === $ip){
      return $value + 3600 - time();
    }
  }
}
