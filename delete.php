<?php
require_once 'db.php'; // подключение бд
global $conn;

// Проверка и содержит ли   массив -> short_code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['short_code'])) {
    $short_code = $_POST['short_code'];

    // Подготовка SQL-запроса для удаления
    $stmt = $conn->prepare("DELETE FROM `urls` WHERE short_code = ?");
    $stmt->bind_param('s', $short_code);

    if ($stmt->execute()) {
        // Успешное удаление, можно перенаправить обратно или показать сообщение
        header("Location: index.php?message=Запись удалена");
        exit();
    } else {
        // Ошибка при удалении
        echo "Ошибка при удалении ссылки";
    }
}
