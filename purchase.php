<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conn.php';

// Получаем количество билетов и ID рейса
$ticket_count = isset($_POST['ticket_count']) ? (int)$_POST['ticket_count'] : 1;
$flight_id = isset($_POST['flight_id']) ? (int)$_POST['flight_id'] : 0;

if ($flight_id === 0) {
    die("Рейс не указан");
}

// Получаем информацию о рейсе
$stmt = $mysqli->prepare("
    SELECT f.*, 
           dep.Город AS departure_city, dep.Название AS departure_airport,
           arr.Город AS arrival_city, arr.Название AS arrival_airport,
           ac.Название AS company_name
    FROM flight f
    JOIN airport dep ON f.`Аэропорт вылета` = dep.airport_id
    JOIN airport arr ON f.`Аэропорт назначения` = arr.airport_id
    JOIN aviacompany ac ON f.aviacompany_id = ac.aviacompany_id
    WHERE f.flight_id = ?
");
$stmt->bind_param("i", $flight_id);
$stmt->execute();
$flight = $stmt->get_result()->fetch_assoc();

if (!$flight) {
    die("Рейс не найден");
}

$msg = '';
$error = '';

// Обработка формы покупки
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_purchase'])) {
    $bag = isset($_POST['baggage']) ? 'Да' : 'Нет';
    $lun = isset($_POST['lunch']) ? 'Да' : 'Нет';
    $passenger_names = $_POST['passenger_name'];
    $passports = $_POST['passport'];
    
    // Проверяем доступное количество мест
    if ($flight['Кол-во доступных мест'] < $ticket_count) {
        $error = "Извините, недостаточно свободных мест";
    } else {
        $mysqli->begin_transaction();
        
        try {
            // Создаем билеты
            for ($i = 0; $i < $ticket_count; $i++) {
                $seat_number = str_pad($i+1, 2, '0', STR_PAD_LEFT);
                
                $insert = $mysqli->prepare("
                    INSERT INTO ticket (flight_id, user_id, `Номер места`, `Багаж`, `Питание`, `ФИО_пассажира`, `Паспортные_данные`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $insert->bind_param(
                    "iisssss", 
                    $flight_id, 
                    $_SESSION['user_id'], 
                    $seat_number, 
                    $bag, 
                    $lun,
                    $passenger_names[$i],
                    $passports[$i]
                );
                $insert->execute();
                $insert->close();
            }
            
            // Обновляем количество доступных мест
            $update = $mysqli->prepare("
                UPDATE flight 
                SET `Кол-во доступных мест` = `Кол-во доступных мест` - ? 
                WHERE flight_id = ?
            ");
            $update->bind_param("ii", $ticket_count, $flight_id);
            $update->execute();
            $update->close();
            
            $mysqli->commit();
            $msg = '<div class="alert success">Успешно приобретено '.$ticket_count.' билет(а)!</div>';
        } catch (Exception $e) {
            $mysqli->rollback();
            $error = "Ошибка при покупке билета: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Покупка билетов | Авиабилеты</title>
    <style>
        /* Стили остаются такими же, как в вашем исходном коде */
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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
            color: #333;
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
        
        .save-btn:hover {
            background-color: #0048a7;
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
        
        .ticket-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
        .content input,
        .content select,
        .content button{
            padding: 10px;
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .content button {
            background-color: #003580;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        .content button:hover{
            background-color: #0048a7;
        }
        
        .passenger-form {
            margin-top: 20px;
        }
        
        .passenger-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .passenger-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #003580;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">Покупка билетов (<?php echo $ticket_count; ?> шт.)</div>
            <div class="user-menu">
                <a href="search.php">Назад</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="main-content">
            <main class="content">
                <h2 class="section-title">Информация о рейсе</h2>
                
                <?php if ($msg): ?>
                    <div class="alert success"><?php echo $msg; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="flight-info">
                    <p><strong>Рейс:</strong> <?php echo htmlspecialchars($flight['company_name']); ?> 
                    №<?php echo htmlspecialchars($flight['Номер рейса']); ?></p>
                    <p><strong>Маршрут:</strong> <?php echo htmlspecialchars($flight['departure_city']); ?> 
                    (<?php echo htmlspecialchars($flight['departure_airport']); ?>) → 
                    <?php echo htmlspecialchars($flight['arrival_city']); ?> 
                    (<?php echo htmlspecialchars($flight['arrival_airport']); ?>)</p>
                    <p><strong>Дата вылета:</strong> <?php echo date('d.m.Y', strtotime($flight['Дата вылета'])); ?></p>
                    <p><strong>Время вылета:</strong> <?php echo date('H:i', strtotime($flight['Время вылета'])); ?></p>
                    <p><strong>Дата прибытия:</strong> <?php echo date('d.m.Y', strtotime($flight['Дата прибытия'])); ?></p>
                    <p><strong>Время прибытия:</strong> <?php echo date('H:i', strtotime($flight['Время прибытия'])); ?></p>
                    <p><strong>Количество билетов:</strong> <?php echo $ticket_count; ?></p>
                    <p><strong>Стоимость одного билета:</strong> <?php echo $flight['Стоимость']; ?> руб.</p>
                    <p><strong>Общая стоимость:</strong> <?php echo $flight['Стоимость'] * $ticket_count; ?> руб.</p>
                </div>

                <h2 class="section-title">Дополнительные услуги</h2>
                <form method="post" class="services-form">
                    <div>
                        <label>
                            <input type="checkbox" name="baggage" value="Да">
                            Багаж (+500 руб. к стоимости каждого билета)
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="checkbox" name="lunch" value="Да">
                            Питание (+300 руб. к стоимости каждого билета)
                        </label>
                    </div>
                    
                    <h2 class="section-title">Данные пассажиров</h2>
                    
                    <?php for ($i = 1; $i <= $ticket_count; $i++): ?>
                    <div class="passenger-item">
                        <div class="passenger-title">Пассажир #<?php echo $i; ?></div>
                        <div class="form-group">
                            <label>ФИО:</label>
                            <input type="text" name="passenger_name[]" required>
                        </div>
                        <div class="form-group">
                            <label>Паспортные данные:</label>
                            <input type="text" name="passport[]" required>
                        </div>
                    </div>
                    <?php endfor; ?>
                    
                    <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
                    <input type="hidden" name="ticket_count" value="<?php echo $ticket_count; ?>">
                    <input type="hidden" name="confirm_purchase" value="1">
                    
                    <button type="submit" class="save-btn">Подтвердить покупку всех билетов</button>
                </form>
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