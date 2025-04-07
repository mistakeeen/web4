<?php

$page_title = "Сервис авиабилетов";
$current_page = "home";
session_start()
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
        
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5));
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 5rem 0;
            margin-bottom: 2rem;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .search-box {
            background-color: white;
            padding: 2rem;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .about, .services {
            background-color: white;
            padding: 2rem;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .search-box input,
        .search-box select,
        .search-box button{
            padding: 10px;
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-box button {
            background-color: #003580;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        .search-box button:hover{
            background-color: #0048a7;
        }
        h2 {
            color: #003580;
            margin-bottom: 1rem;
        }
        
        .service-item {
            margin-bottom: 1.5rem;
        }
        
        .service-item h3 {
            color: #0048a7;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php" class="<?php echo $current_page == 'home' ? 'active' : ''; ?>">Главная</a></li>
                    <li><a href="news.php" class="<?php echo $current_page == 'news' ? 'active' : ''; ?>">Новости</a></li>
                    <li><a href="search.php" class="<?php echo $current_page == 'search' ? 'active' : ''; ?>">Поиск билетов</a></li>
                    <li><a href="contact.php" class="<?php echo $current_page == 'contact' ? 'active' : ''; ?>">Контакты</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="account.php" class="<?php echo $current_page == 'profile' ? 'active' : ''; ?>">Личный кабинет</a></li>
                    <?php else: ?>
                    <li><a href="login.php" class="<?php echo $current_page == 'login' ? 'active' : ''; ?>">Авторизация</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <section class="hero">
        <div class="container">
            <h1>Поиск билетов</h1>
            
            <div class="search-box">
                <form action="search.php" method="GET">
                    <input type="text" name="from" placeholder="Откуда" required>
                    <input type="text" name="to" placeholder="Куда" required>
                    <input type="date" name="departure" required>
                    <button type="submit" name="search">Найти билеты</button>
                </form>
            </div>
        </div>
    </section>
    
    <div class="container">
        <section class="about">
            <h2>О компании</h2>
            <p>Мы - ведущий сервис по поиску и бронированию авиабилетов, работающий с 20XX года. Наша миссия - сделать путешествия доступными для каждого, предоставляя самые выгодные цены на авиабилеты по всему миру.</p>
            <p>Мы сотрудничаем с более чем 1000 авиакомпаний и агентств, чтобы предложить вам самый широкий выбор рейсов по оптимальным ценам. Наша система сравнивает цены в реальном времени, помогая вам сэкономить до 60% стоимости билетов.</p>
        </section>
    </div>
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