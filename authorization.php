<?php
session_start();

$email = $password = '';
$error = '';

function getDatabaseConnection()
{
    $conn = new mysqli("localhost", "dvenadcatr", "2v8ECS6U#UQ2NP9S", "dvenadcatr");
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    return $conn;
}

function authenticateUser($email, $password)
{
    global $error;
    $conn = getDatabaseConnection();

    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hash);
        $stmt->fetch();

        if (password_verify($password, $hash)) {
            $_SESSION['email'] = $email;
            header("Location: you.php");
            exit();
        } else {
            $error = "Неверный пароль";
        }
    } else {
        $error = "Неверные учетные данные";
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    authenticateUser($email, $password);
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Авторизация</title>
</head>

<body>
    <div class="container">
        <h1>Авторизация</h1>

        <?php if (!empty($error)): ?>
            <span class="error-message"><?php echo htmlspecialchars($error); ?></span>
        <?php endif; ?>

        <form action="authorization.php" method="POST">
            <label for="email">E-mail:</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">

            <label for="password">Пароль:</label>
            <input type="password" name="password" required>

            <input type="submit" value="Войти">
        </form>

        <a href="index.php" class="button">Вернуться на главную</a>
    </div>
</body>

</html>