<?php
session_start();
require_once 'conn.php';

$page_title = "Контакты";
$current_page = "contacts";
$feedback_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(htmlspecialchars($_POST['name'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));

    if (empty($name) || empty($email) || empty($message)) {
        $error_message = "Пожалуйста, заполните все обязательные поля.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Пожалуйста, введите корректный email адрес.";
    } else {
        $feedback_sent = true;
        $query = "
        INSERT INTO feedback (name, email, date, message)
        VALUES (?, ?, ?, ?)
        ";
        $date = date('Y-m-d H:i:s');
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss", $name, $email, $date, $message);
        if (!$stmt) {
            die("Ошибка подготовки запроса: " . $mysqli->error);
        }
        $stmt->execute();
        if (!$stmt) {
            die("Ошибка выполнения запроса: " . $mysqli->error);
    
        }
        $stmt->close();
    }
}
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
        
        .contact-section {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .contact-info, .feedback-form {
            background-color: white;
            padding: 2rem;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 300px;
        }
        
        h1, h2 {
            color: #003580;
            margin-bottom: 1rem;
        }
        
        .contact-details {
            margin-top: 1.5rem;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .contact-icon {
            width: 40px;
            height: 40px;
            background-color: #003580;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        form input, form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        form textarea {
            height: 150px;
            resize: vertical;
        }
        
        .required:after {
            content: " *";
            color: red;
        }
        
        .submit-btn {
            background-color: #003580;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #0048a7;
        }
        
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        footer {
            background-color: #003580;
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: 2rem;
        }
        
        .map-container {
            margin-top: 2rem;
            height: 300px;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
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
                    <li><a href="contacts.php" class="active">Контакты</a></li>
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
        
        <div class="contact-section">
            <div class="contact-info">
                <h2>Наши контактные данные</h2>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <div>
                            <strong>Адрес:</strong><br>
                            г. Москва, ул. Авиационная, д. 10, офис 305
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div>
                            <strong>Телефоны:</strong><br>
                            +7 (800) 999-00-99 (Звонок бесплатный)
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div>
                            <strong>Email:</strong><br>
                            info@aviasearch.ru<br>
                            support@aviasearch.ru
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div>
                            <strong>Часы работы:</strong><br>
                            Пн-Пт: 9:00 - 20:00<br>
                            Сб-Вс: 10:00 - 18:00
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="feedback-form">
                <h2>Форма обратной связи</h2>
                
                <?php if ($feedback_sent): ?>
                    <div class="success-message">
                        Спасибо! Ваше сообщение отправлено. Мы свяжемся с вами в ближайшее время.
                    </div>
                <?php elseif (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="contact.php">
                    <div>
                        <label for="name" class="required">Ваше имя</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo isset($name) ? $name : ''; ?>">
                    </div>
                    
                    <div>
                        <label for="email" class="required">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($email) ? $email : ''; ?>">
                    </div>
                    
                    <div>
                        <label for="message" class="required">Ваше сообщение</label>
                        <textarea id="message" name="message" required><?php echo isset($message) ? $message : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">Отправить сообщение</button>
                </form>
            </div>
        </div>
        
    </div>
    
</body>
</html>