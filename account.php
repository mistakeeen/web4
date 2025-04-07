<?php
require_once 'conn.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$user_query = "SELECT ФИО, email, Телефон, Паспорт FROM users WHERE user_id = $user_id";
$user_result = $mysqli->query($user_query);
$user_data = $user_result->fetch_assoc();
$change_data = 0;

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['logout']))
{
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    session_destroy();
    header("Location: login.php");
}

$tickets_query = "SELECT 
                    t.ticket_id,
                    t.`Номер места`,
                    t.`ФИО_пассажира`,
                    t.`Паспортные_данные`,
                    t.`Багаж`,
                    t.`Питание`,
                    f.`Номер рейса`,
                    ac.`Название` AS 'Авиакомпания',
                    dep_air.`Город` AS 'Город вылета',
                    dep_air.`Название` AS 'Аэропорт вылета',
                    arr_air.`Город` AS 'Город назначения',
                    arr_air.`Название` AS 'Аэропорт назначения',
                    f.`Дата вылета`,
                    f.`Время вылета`,
                    f.`Дата прибытия`,
                    f.`Время прибытия`,
                    f.`Стоимость`,
                    u.ФИО AS 'Пассажир' 
                FROM 
                    ticket t
                JOIN 
                    flight f ON t.flight_id = f.flight_id
                JOIN 
                    aviacompany ac ON f.aviacompany_id = ac.aviacompany_id
                JOIN 
                    airport dep_air ON f.`Аэропорт вылета` = dep_air.airport_id
                JOIN 
                    airport arr_air ON f.`Аэропорт назначения` = arr_air.airport_id
                JOIN 
                    users u ON t.user_id = u.user_id 
                WHERE 
                    t.user_id = $user_id";
$tickets_result = $mysqli->query($tickets_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change'])) {
    $change_data = 1;
}
$update_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $change_data = 0;
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    
    $errors = [];
    
 
    if (empty($name)) {
        $errors[] = "Имя обязательно для заполнения";
    }
    
    if (!empty($new_password)) {

        $password_query = "SELECT Пароль FROM users WHERE user_id = $user_id";
        $password_result = $mysqli->query($password_query);
        $password_data = $password_result->fetch_assoc();
        
        if ($current_password != $password_data['Пароль']) {
            $errors[] = "Текущий пароль введен неверно";
        }
    }
    
    if (empty($errors)) {

        $update_query = "UPDATE users SET ФИО = '$name', Телефон = '$phone'";
        
        if (!empty($new_password)) {
            $update_query .= ", Пароль = '$new_password'";
        }
        
        $update_query .= " WHERE user_id = $user_id";
        
        if ($mysqli->query($update_query)) {
            $update_message = '<div class="alert success">Данные успешно обновлены!</div>';
            $_SESSION['user_name'] = $name;
            $user_result = $mysqli->query($user_query);
            $user_data = $user_result->fetch_assoc();
        } else {
            $update_message = '<div class="alert error">Ошибка при обновлении данных: ' . $mysqli->error . '</div>';
        }
    } else {
        $update_message = '<div class="alert error">' . implode('<br>', $errors) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет | Авиабилеты</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
    
        header {
            background-color: #003580;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .user-menu a:hover {
            text-decoration: underline;
        }
        
        .logout-btn {
            background-color: #feba02;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .main-content {
            display: flex;
            margin: 30px 0;
            gap: 30px;
        }
        
        .sidebar {
            width: 250px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 15px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #003580;
            color: white;
        }
        
        .content {
            flex: 1;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 30px;
        }
        
        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #003580;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-form .form-group {
            margin-bottom: 20px;
        }
        
        .profile-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .profile-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .profile-form .form-row {
            display: flex;
            gap: 20px;
        }
        
        .profile-form .form-row .form-group {
            flex: 1;
        }
        
        .save-btn {
            background-color: #003580;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
    
        
        .tickets-list {
            margin-top: 30px;
        }
        
        .ticket-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: box-shadow 0.3s;
        }

        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .ticket-title {
            font-size: 18px;
            font-weight: bold;
            color: #003580;
        }
        
        .ticket-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .ticket-details {
            display: flex;
            gap: 30px;
            margin-bottom: 15px;
        }
        
        .ticket-info {
            flex: 1;
        }
        
        .ticket-info h4 {
            margin-bottom: 10px;
            color: #666;
        }
        
        .ticket-price {
            font-size: 20px;
            font-weight: bold;
            color: #003580;
        }
        
        .ticket-actions {
            margin-top: 15px;
        }
        
        .ticket-btn {
            background-color: transparent;
            border: 1px solid #003580;
            color: #003580;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .ticket-btn:hover {
            background-color: #003580;
            color: white;
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        footer {
            background-color: #003580;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
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
                    <form method="GET">
                    <button type="submit" name="logout" class="logout-btn">Выйти</button>
                </form>
                    </ul>
                    </nav>
                    
                
        </div>
    </header>
    
    <div class="container">
        <div class="main-content">
            
            <main class="content">
                <h2 class="section-title">Настройки профиля</h2>
                
                <?php echo $update_message; ?>
                
                <form class="profile-form" method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Имя</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['ФИО']); ?>" <?php echo $change_data == 1 ? '' : 'disabled';?>>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" <?php echo $change_data == 1 ? '' : 'disabled';?>>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Телефон</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['Телефон'] ?? ''); ?>" <?php echo $change_data == 1 ? '' : 'disabled';?>>
                        </div>
                        <div class="form-group">
                            <label for="pass_data">Паспортные данные</label>
                            <input type="text" id="passdata" name="passdata" value="<?php echo htmlspecialchars($user_data['Паспорт'] ?? ''); ?>" <?php echo $change_data == 1 ? '' : 'disabled';?>>
                        </div>
                    </div>
                    
                    <div class="form-row" style="display: <?php echo $change_data == 1 ? '' : 'none';?>;">
                        <div class="form-group">
                            <label for="current_password">Текущий пароль (для изменения)</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">Новый пароль</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                    </div>
                    
                    <button <?php echo $change_data == 1 ? '' : 'hidden';?> type="submit" name="update_profile" class="save-btn">Сохранить изменения</button>
                    <button <?php echo $change_data == 1 ? 'hidden' : '';?> type="submit" name="change" class="save-btn">Изменить данные</button>
                </form>
                
                <div class="tickets-list">
                    <h2 class="section-title">Мои билеты</h2>
                    
                    <?php if ($tickets_result->num_rows > 0): ?>
                        <?php while ($ticket = $tickets_result->fetch_assoc()): ?>
                            <div class="ticket-card">
                                <div class="ticket-header">
                                    <div class="ticket-title">
                                        Рейс <?php echo htmlspecialchars($ticket['Номер рейса']); ?>: 
                                        <?php echo htmlspecialchars($ticket['Город вылета']); ?> → 
                                        <?php echo htmlspecialchars($ticket['Город назначения']); ?>
                                    </div>
                                    
                                </div>
                                
                                <div class="ticket-details">
                                    <div class="ticket-info">
                                        <h4>Вылет</h4>
                                        <p><?php echo date('d.m.Y', strtotime($ticket['Дата вылета']))." ".date('H:i', strtotime($ticket['Время вылета'])); ?></p>
                                        <br>
                                        <div>
                                        <h4>Пассажир</h4>
                                        <p><?php echo $ticket['ФИО_пассажира']; ?></p>
                                        </div>
                                    </div>
                                    <div class="ticket-info">
                                        <h4>Прилет</h4>
                                        <p><?php echo date('d.m.Y', strtotime($ticket['Дата прибытия']))." ".date('H:i', strtotime($ticket['Время прибытия'])); ?></p>
                                    </div>
                                    <div class="ticket-info">
                                        <h4>Доп.услуги</h4>
                                        <p><?php echo "Багаж - "; echo $ticket['Багаж'] == 'Да' ? 'Да' : 'Нет'; ?></p>
                                        <p><?php echo "Питание - "; echo $ticket['Питание'] == 'Да' ? 'Да' : 'Нет'; ?></p>
                                    </div>
                                    <div class="ticket-info">
                                        <h4>Стоимость</h4>
                                        <p class="ticket-price"><?php echo number_format($ticket['Стоимость'], 0, '', ' '); ?> ₽</p>
                                    </div>
                                    
                                </div>
                            
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>У вас нет купленных билетов.</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    
    <?php $mysqli->close(); ?>
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