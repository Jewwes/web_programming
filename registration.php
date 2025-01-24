<?php

$errors = [];
$name = $email = $password = '';

function validateInput($name, $email, $password)
{
    $errors = [];
    if (empty($name)) {
        $errors['name'] = "Имя не может быть пустым";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Неверный формат E-mail";
    }
    if (empty($password)) {
        $errors['password'] = "Пароль не может быть пустым";
    }
    return $errors;
}

function getDatabaseConnection()
{
    $conn = new mysqli("localhost", "dvenadcatr", "2v8ECS6U#UQ2NP9S", "dvenadcatr");
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    return $conn;
}

function isEmailRegistered($conn, $email)
{
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function registerUser($conn, $name, $email, $password)
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $passwordHash);
    return $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $errors = validateInput($name, $email, $password);
    if (empty($errors)) {
        $conn = getDatabaseConnection();
        if (isEmailRegistered($conn, $email)) {
            $errors['email'] = "Этот E-mail уже зарегистрирован";
        } else if (!registerUser($conn, $name, $email, $password)) {
            $errors['registration'] = "Не удалось зарегистрировать пользователя.";
        } else {
            header("Location: index.php");
            exit();
        }
        $conn->close();
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Регистрация</title>
</head>
<body>
    <div class="container">
        <h1>Регистрация</h1>

        <form action="registration.php" method="POST">
            <label for="name">Имя:</label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars($name); ?>">

            <label for="email">E-mail:</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="error-message"><?php echo $errors['email']; ?></div>
            <?php endif; ?>

            <label for="password">Пароль:</label>
            <input type="password" name="password" required>

            <input type="submit" value="Зарегистрироваться">
        </form>

        <a href="index.php" class="button">Вернуться на главную</a>
    </div>
</body>

</html>
