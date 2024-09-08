<?php
require_once 'db.php';
global $conn;

// Генерация короткого кода
function generateShortcode($length = 9)
{
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

// Переменные для хранения сообщений
$short_code = '';
$message = '';

// Обработка формы для создания сокращенного URL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $long_url = filter_var($_POST['url'], FILTER_SANITIZE_URL);

    // Проверка на корректность URL
    if (filter_var($long_url, FILTER_VALIDATE_URL)) {
        // Проверка, существует ли уже этот длинный URL
        $stmt = $conn->prepare("SELECT short_code FROM `urls` WHERE long_url = ?");
        $stmt->bind_param('s', $long_url);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Длинный URL уже существует, извлекаем короткий код
            $row = $result->fetch_assoc();
            $short_code = $row['short_code'];
            $message = "Сокращенный URL уже существует: <a href='?c=$short_code'>https://yourdomain.com/?c=$short_code</a>";
        } else {
            // Генерируем новый короткий код
            $short_code = generateShortcode();
            // Вставка в базу данных
            $stmt = $conn->prepare("INSERT INTO `urls` (long_url, short_code) VALUES (?, ?)");
            $stmt->bind_param('ss', $long_url, $short_code);
            if ($stmt->execute()) {

            } else {
                $message = "Ошибка при вставке URL";
            }
        }
    } else {
        $message = "Некорректный URL";
    }
}

// Перенаправление по короткому коду
if (isset($_GET['c'])) {
    $short_code = $_GET['c'];
    $stmt = $conn->prepare("SELECT long_url FROM `urls` WHERE short_code = ?");
    $stmt->bind_param("s", $short_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $long_url = $row['long_url'];
        header("Location: $long_url");
        exit();
    } else {
        $message = "Сокращенный URL не найден";
    }
}

// Извлечение всех ссылок из базы данных
$stmt = $conn->prepare("SELECT long_url, short_code FROM `urls`");
$stmt->execute();
$result = $stmt->get_result();
$all_links = $result->fetch_all(MYSQLI_ASSOC);
?>

<!Doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Shortener URL</title>
</head>
<body>
<div class="container">
    <h1>Shortener URL</h1>
    <form method="post">
        <input type="url" name="url" placeholder="Введите URL" required>
        <input type="submit" name="shortener" value="Сократить">
    </form>
    <?php if (!empty($message)): ?>
        <div class="message">
            <?php echo($message); ?>
        </div>
    <?php endif; ?>

<!-- Отображение всех сокращенных ссылок -->

    <h2>Сокращенные ссылки:</h2>
    <ul>
        <?php foreach ($all_links as $link): ?>
            <li>
                <div class="linkss">
                    <a href="?c=<?php echo($link['short_code']); ?>">
                        https://yourdomain.com/?c=<?php echo($link['short_code']); ?>
                    </a>
                </div>
                <div class="button">
                    <form method="post" action="delete.php" style="display:inline;">
                        <input type="hidden" name="short_code" value="<?php echo($link['short_code']); ?>">
                        <input type="submit" value="Удалить">
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
</body>
</html>