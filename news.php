<?php
session_start();

require_once 'conn.php';


$query = "SELECT id, title, full_text, publish_date FROM news WHERE is_published = 1 ORDER BY publish_date DESC";
$result = $mysqli->query($query);


$page_title = "Новости авиакомпаний и авиаперевозок";
$current_page = "news";
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
        
        .news-section {
            margin: 2rem 0;
            height: 700px;
        }
        
        .news-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .news-item {
            background-color: white;
            border-radius: 5px;
            overflow: hidden;
        }
        
        
        .news-content {
            padding: 1.5rem;
        }
        
        .news-date {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .news-title {
            color: #003580;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .news-text {
            margin-bottom: 1rem;
        }
        
        
        h1 {
            color: #003580;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="news.php" class="active">Новости</a></li>
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
        
        <div class="news-section">
            <?php if ($result->num_rows > 0): ?>
                <div class="news-list">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="news-item">
                            <div class="news-content">
                                <div class="news-date">
                                    <?php echo date('d.m.Y', strtotime($row['publish_date'])); ?>
                                </div>
                                <h3 class="news-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p class="news-text"><?php echo htmlspecialchars($row['full_text']); ?></p>
                                
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>На данный момент новостей нет. Пожалуйста, зайдите позже.</p>
            <?php endif; ?>
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