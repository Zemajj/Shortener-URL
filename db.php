<?php
$servername = ""; // адрес сервера
$username = ""; // имя пользователя
$password = ""; // пароль
$dbname = ""; // имя базы данных

// Создаем соединение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
} else {
    //echo "Успех";
}

// Закрываем соединенние

//$conn->close();
