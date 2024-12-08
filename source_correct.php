<?php
session_start(); // Начинаем сессию

// Проверяем, была ли инициализирована переменная для неудачных попыток
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0; // Инициализируем счетчик попыток
}

// Проверяем, заблокирован ли пользователь
if ($_SESSION['login_attempts'] >= 3) {
    die("<p>Вы заблокированы после 3 неудачных попыток входа. Пожалуйста, попробуйте позже.</p>");
}

if (isset($_GET['Login'])) {
    // Получаем имя пользователя
    $user = $_GET['username'];
    // Получаем пароль
    $pass = $_GET['password'];
    $pass = md5($pass);
    
    // Проверяем базу данных
    $query = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';";
    $result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or die('<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>');

    if ($result && mysqli_num_rows($result) == 1) {
        // Получаем данные пользователя
        $row = mysqli_fetch_assoc($result);
        $avatar = $row["avatar"];
        
        // Успешный вход
        $html .= "<p>Welcome to the password protected area {$user}</p>";
        $html .= "<img src=\"{$avatar}\" />";
        
        // Сбрасываем счетчик попыток
        $_SESSION['login_attempts'] = 0;
    } else {
        // Неудачный вход
        $_SESSION['login_attempts']++; // Увеличиваем счетчик неудачных попыток
        $html .= "<pre><br />Username and/or password incorrect.</pre>";
    }
    
    ((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"])) ? false : $___mysqli_res));
}
?>

