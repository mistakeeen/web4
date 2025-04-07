<?php
session_start();

require_once 'conn.php';


$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registr'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name'])." ".trim($_POST['surname'])." ".trim($_POST['lastname']);
    $pass_data = trim($_POST['pass_data']);
    $phone = trim($_POST['phone']);
    $date = date('Y-m-d H:i:s');
    
    if (empty($email) || empty($password) || empty($name) || empty($pass_data) || empty($phone)){
        $error_message = "Пожалуйста, заполните все поля";
    } else {
        $query = "INSERT INTO users (ФИО, email, Пароль, `Дата регистрации`, Паспорт, Телефон)
        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssssss", $name, $email, $password, $date, $pass_data, $phone);
        if (!$stmt) {
            die("Ошибка подготовки запроса: " . $mysqli->error);
        }
        $stmt->execute();
        if (!$stmt->execute()) {
            die("Ошибка выполнения запроса: " . $stmt->error);
        }
        $_GET['registration'] = 'success';
    }
}

if (isset($_GET['registration']) && $_GET['registration'] === 'success') {
    $success_message = "Регистрация прошла успешно! Теперь вы можете войти.";
}

$page_title = "Регистрация";
$current_page = "login";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        header {
            background-color: #003580;
            color: white;
            padding: 1rem 0;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-right: 20px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background-color 0.3s;
        }
        
        nav ul li a:hover {
            background-color: #0048a7;
        }
        
        nav ul li a.active {
            background-color: #0048a7;
        }
        
        .auth-container {
            max-width: 500px;
            width: 50%;
            margin: 2rem auto;
            background-color: white;
            padding: 2rem;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #003580;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .btn {
            display: inline-block;
            background-color: #003580;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.3s;
            text-align: center;
            width: 100%;
        }
        
        .btn:hover {
            background-color: #0048a7;
        }
        
        .btn-register {
            background-color: #f5f5f5;
            color: #003580;
            border: 1px solid #003580;
            margin-top: 1rem;
        }
        
        .btn-register:hover {
            background-color: #e6e6e6;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            text-align: center;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .links a {
            color: #003580;
            text-decoration: none;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="news.php">Новости</a></li>
                    <li><a href="search.php">Поиск билетов</a></li>
                    <li><a href="contact.php">Контакты</a></li>
                    <li><a href="login.php" class="active">Авторизация</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="auth-container">
            <h1>Регистрация</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name">Имя</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="surname">Фамилия</label>
                    <input type="text" id="surname" name="surname" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Отчество</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="pass_data">Паспортные данные</label>
                    <input type="text" id="pass_data" name="pass_data" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="registr" class="btn">Зарегистрироваться</button>
            </form>
        </div>
    </div>
    
    <?php
    $mysqli->close();
    ?>
    
</body>
<footer style="
    background-color: #003580;
    color: white;
    padding: 30px 0;
    text-align: center;
    margin-top: 50px;
    font-family: Arial, sans-serif;
">
    <div style="max-width: 100%; margin: 0 auto; padding: 0 px;">
        <div style="margin-bottom: 15px;">
            <p style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">Сервис поиска и покупки авиабилетов</p>
        </div>
        <div>
            <p style="font-size: 12px;">Разработчик: Данилов Г. А. user@server.com</p>
        </div>
    </div>
</footer>
</html>