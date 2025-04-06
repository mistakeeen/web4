<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conn.php';

$flight_id = isset($_GET['flight_id']) ? (int)$_GET['flight_id'] : 0;


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


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_purchase'])) {

    $bag = $_POST['baggage'];
    $lun = $_POST['lunch'];
    $seat_number = "00";
    $status = 'Забронирован';
    if ($flight['Кол-во доступных мест'] <= 0) {
        $error = "Извините, места закончились";
    } else {

        
        try {
            $insert = $mysqli->prepare("
                INSERT INTO ticket (flight_id, user_id, `Номер места`, `Статус`, `Багаж`, `Питание`) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insert->bind_param("iissss", $flight_id, $_SESSION['user_id'], $seat_number, $status, $bag, $lun);
            if (!$insert) {
                die("Ошибка подготовки запроса: " . $mysqli->error);
            }

            if (!$insert->execute()) {
                die("Ошибка выполнения запроса: " . $insert->error);
            }
            // $update = $mysqli->prepare("
            //     UPDATE flight 
            //     SET `Кол-во доступных мест` = `Кол-во доступных мест` - 1 
            //     WHERE flight_id = ?
            // ");
            // $update->bind_param("i", $flight_id);
            // $update->execute();
            
            // Подтверждение транзакции
            $msg = '<div class="alert success">Билет успешно приобретен!</div>';
            // Перенаправление на страницу успешной покупки
           // header("Location: purchase_success.php?ticket_id=" . $mysqli->insert_id);
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
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">Страница покупки билета</div>
            <div class="user-menu">
                <a href="search.php">Назад</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="main-content">
            
            <main class="content">
                <h2 class="section-title">Информация о билете</h2>
                
                <?php echo $msg; ?>
                
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
                </div>

                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post" class="add-form">
                    <div>
                        <label>Багаж:</label>
                        <input type="checkbox" name="baggage" value="Да" >
                        <label>Питание:</label>
                        <input type="checkbox" name="lunch" value="Да">
                    </div>
                    <h2 class="section-title">Данные пассажира</h2>
                    <form method="post" class="passenger-form">
                    <input type="hidden" name="confirm_purchase" value="1">
                    
                    <div>
                        <label>ФИО:</label>
                        <input type="text" name="passenger_name" required 
                            value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>">
                    </div>
                    
                    <div>
                        <label>Email:</label>
                        <input type="text" name="passenger_surname" required
                            value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
                    </div>
                    
                    <br>
                    <strong><p id="numberDisplay">Итоговая Стоимость: <?php echo $flight['Стоимость'] ?></p></strong>
                    <button type="submit" >Подтвердить покупку</button>
                </form>
            </main>
        </div>
    </div>

    
    <?php $mysqli->close(); ?>
</body>
</html>