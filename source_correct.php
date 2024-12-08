<?php
session_start(); // Начинаем сессию

// Проверяем, была ли инициализирована переменная для неудачных попыток
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0; // Инициализируем счетчик попыток
}

// Параметры блокировки
$total_failed_login = 3; // Максимальное количество неудачных попыток
$lockout_time = 15; // Время блокировки в минутах
$account_locked = false;

// Проверяем, заблокирован ли пользователь
if ($_SESSION['login_attempts'] >= $total_failed_login) {
    // Проверяем время блокировки
    if (isset($_SESSION['lockout_time'])) {
        $timeout = $_SESSION['lockout_time'] + ($lockout_time * 60);
        if (time() < $timeout) {
            $account_locked = true; // Пользователь заблокирован
        } else {
            // Сбрасываем блокировку
            unset($_SESSION['lockout_time']);
            $_SESSION['login_attempts'] = 0; // Сбрасываем счетчик попыток
        }
    } else {
        // Устанавливаем время блокировки
        $_SESSION['lockout_time'] = time();
        $account_locked = true; // Пользователь заблокирован
    }
}

if (isset($_POST['Login']) && isset($_POST['username']) && isset($_POST['password'])) {
    // Проверяем, заблокирован ли пользователь
    if ($account_locked) {
        die("<p>Вы заблокированы после 3 неудачных попыток входа. Пожалуйста, попробуйте позже.</p>");
    }

    // Получаем имя пользователя
    $user = $_POST['username'];
    $user = stripslashes($user);
    $user = mysqli_real_escape_string($db, $user); // Используем $db для mysqli_real_escape_string

    // Получаем пароль
    $pass = $_POST['password'];
    $pass = stripslashes($pass);
    $pass = mysqli_real_escape_string($db, $pass); // Используем $db для mysqli_real_escape_string
    $pass = md5($pass); // Хешируем пароль

    // Проверяем базу данных (если имя пользователя совпадает с паролем)
    $data = $db->prepare('SELECT * FROM users WHERE user = :user AND password = :password LIMIT 1;');
    $data->bindParam(':user', $user, PDO::PARAM_STR);
    $data->bindParam(':password', $pass, PDO::PARAM_STR);
    $data->execute();
    $row = $data->fetch(PDO::FETCH_ASSOC); // Получаем данные в виде ассоциативного массива

    // Если вход успешен
    if ($data->rowCount() == 1) {
        // Получаем данные пользователя
        $avatar = $row['avatar'];

        // Успешный вход
        echo "<p>Добро пожаловать в защищенную область <em>{$user}</em></p>";
        echo "<img src=\"{$avatar}\" />";

        // Сбрасываем счетчик неудачных попыток
        $_SESSION['login_attempts'] = 0;
        unset($_SESSION['lockout_time']); // Удаляем время блокировки, если оно есть
    } else {
        // Неудачный вход
        $_SESSION['login_attempts']++; // Увеличиваем счетчик неудачных попыток

        // Если достигнуто максимальное количество попыток
        if ($_SESSION['login_attempts'] >= $total_failed_login) {
            $_SESSION['lockout_time'] = time(); // Устанавливаем время блокировки
            echo "<pre><br />Имя пользователя и/или пароль неверны. Вы заблокированы после 3 неудачных попыток входа.</pre>";
        } else {
            echo "<pre><br />Имя пользователя и/или пароль неверны.</pre>";
        }
    }

    // Устанавливаем время последнего входа
    $data = $db->prepare('UPDATE users SET last_login = NOW() WHERE user = :user LIMIT 1;');
    $data->bindParam(':user', $user, PDO::PARAM_STR);
    $data->execute();
}
?>

