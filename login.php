<?php
session_start();
require_once 'conn.php';
require_once 'auth.php';

$authResult = handleLogin($mysqli);
$error_message = $authResult['error'];
$success_message = $authResult['success'];

$page_title = "Авторизация";
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
        input[type="password"] {
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
        
        footer {
            background-color: #003580;
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: auto;
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
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="account.php" class="<?php echo $current_page == 'profile' ? 'active' : ''; ?>">Личный кабинет</a></li>
                    <?php else: ?>
                    <li><a href="login.php" class="<?php echo $current_page == 'login' ? 'active' : ''; ?>">Авторизация</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="auth-container">
            <h1>Вход в личный кабинет</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn">Войти</button>
                <a href="register.php" class="btn btn-register">Зарегистрироваться</a>
            </form>
        </div>
    </div>
    
    <?php
    $mysqli->close();
    ?>
    
</body>
</html>