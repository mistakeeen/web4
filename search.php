<?php

require_once 'conn.php';
$page_title = "Поиск авиабилетов";
$current_page = "search";


$filter_company = isset($_GET['filter_company']) ? $_GET['filter_company'] : '';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['from'])) {
    $from = $_GET['from'];
    $dest = $_GET['to'];
    $dep_time = $_GET['departure'];
    

    $sql = "SELECT 
                f.flight_id,
                f.`Номер рейса`,
                ac.`Название` AS 'Авиакомпания',
                dep_air.`Название` AS 'Аэропорт вылета',
                dep_air.`Город` AS 'Город вылета',
                arr_air.`Название` AS 'Аэропорт назначения',
                arr_air.`Город` AS 'Город назначения',
                f.`Дата вылета`,
                f.`Время вылета`,
                f.`Дата прибытия`,
                f.`Время прибытия`,
                f.`Кол-во доступных мест`,
                f.`Стоимость`
            FROM 
                flight f
            JOIN 
                airport dep_air ON f.`Аэропорт вылета` = dep_air.airport_id
            JOIN 
                airport arr_air ON f.`Аэропорт назначения` = arr_air.airport_id
            JOIN 
                aviacompany ac ON f.aviacompany_id = ac.aviacompany_id
            WHERE 
                dep_air.`Город` = ? AND 
                arr_air.`Город` = ? AND 
                DATE(f.`Дата вылета`) = ?
                AND f.`Кол-во доступных мест` > 0";
    if (!empty($airline)) {
        $sql .= " AND ac.`Название` = ?";
    }
    $sql .= " ORDER BY f.`Кол-во доступных мест`";
    $sql .= ($sort_order == 'desc') ? " DESC" : " ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sss", $from, $dest, $dep_time);
    $stmt->execute();
    $result = $stmt->get_result();        
    
    
    $stmt->close();
    $search_performed = true;
    session_start();
    $_SESSION['search_params'] = [
        'departure_city' => $from,
        'arrival_city' => $dest,
        'departure_time' => $dep_time
    ];
} else {
    $search_performed = false;
}
session_start();
if (isset($_SESSION['search_params']) && isset($_GET['filter'])) {
    $from = $_SESSION['search_params']['departure_city'];
    $dest = $_SESSION['search_params']['arrival_city'];
    $dep_time = $_SESSION['search_params']['departure_time'];
    $_GET['from'] = $from;
    $_GET['to'] = $dest;
    $_GET['departure'] = $dep_time;
    $search_performed = true;
    $sql = "SELECT 
                f.flight_id,
                f.`Номер рейса`,
                ac.`Название` AS 'Авиакомпания',
                dep_air.`Название` AS 'Аэропорт вылета',
                dep_air.`Город` AS 'Город вылета',
                arr_air.`Название` AS 'Аэропорт назначения',
                arr_air.`Город` AS 'Город назначения',
                f.`Дата вылета`,
                f.`Время вылета`,
                f.`Дата прибытия`,
                f.`Время прибытия`,
                f.`Кол-во доступных мест`,
                f.`Стоимость`
            FROM 
                flight f
            JOIN 
                airport dep_air ON f.`Аэропорт вылета` = dep_air.airport_id
            JOIN 
                airport arr_air ON f.`Аэропорт назначения` = arr_air.airport_id
            JOIN 
                aviacompany ac ON f.aviacompany_id = ac.aviacompany_id
            WHERE 
                dep_air.`Город` = ? AND 
                arr_air.`Город` = ? AND 
                DATE(f.`Дата вылета`) = ?";
    
    // Добавляем фильтр по авиакомпании, если задан
    if (!empty($filter_company)) {
        $sql .= " AND ac.`Название` = ?";
    }
    
    // Добавляем сортировку
    if ($sort_order == 'asc') {
        $sql .= " ORDER BY f.`Стоимость` ASC";
    } elseif ($sort_order == 'desc') {
        $sql .= " ORDER BY f.`Стоимость` DESC";
    }
    
    // Подготовка запроса
    $stmt = $mysqli->prepare($sql);
    
    if (!empty($filter_company)) {
        $stmt->bind_param("ssss", $from, $dest, $dep_time, $filter_company);
    } else {
        $stmt->bind_param("sss", $from, $dest, $dep_time);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

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
        .flight-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.9em;
            overflow: hidden;
        }

        .flight-table thead tr {
            background-color: #003580;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
        }

        .flight-table th,
        .flight-table td {
            padding: 12px 15px;
            text-align: center;
        }


        .flight-table tbody tr:nth-of-type(even) {
            background-color: #f8f9fa;
        }

        .flight-table tbody tr:last-of-type {
            border-bottom: 5px solid #003580;
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
        h1{
            padding-top: 25px;
            text-align: center;
        }
        nav ul li a:hover {
            background-color: #0048a7;
        }
        
        nav ul li a.active {
            background-color: #0048a7;
        }
        
        .search-form {
            background-color: white;
            padding: 2rem;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 2rem 0;
            display: flex;
            justify-content: center; 
            align-items: center; 
            width: 100%;
        }
        
        .search-form input, 
        .search-form select, 
        .search-form button {
            padding: 10px;
            margin-right: 10px;
            margin-bottom: 10px;
            
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .search-form button {
            background-color: #003580;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        .btn_buy{
            padding: 10px;
            margin-right: 10px;
            margin-bottom: 10px;
            
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #003580;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        
        .search-form button:hover {
            background-color: #0048a7;
        }
        .controls input,
        .controls select,
        .controls button{
            padding: 10px;
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .controls button {
            background-color: #003580;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        .controls button:hover{
            background-color: #0048a7;
        }
        .results {
            background-color: white;
            padding: 2rem;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        
        .no-results {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        h1, h2 {
            color: #003580;
            margin-bottom: 1rem;
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
                    <li><a href="search.php" class="active">Поиск билетов</a></li>
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
        <h1>Поиск авиабилетов</h1>
        
        <div class="search-form">
            <form method="GET" action="search.php">
                <input type="text" name="from" placeholder="Откуда" value="<?php echo isset($from) ? $from : ''; ?>" required>
                <input type="text" name="to" placeholder="Куда" value="<?php echo isset($dest) ? $dest : ''; ?>" required>
                <input type="date" name="departure" value="<?php echo isset($dep_time) ? $dep_time : ''; ?>" required>
                <button type="submit" name="search">Найти билеты</button>
            </form>
        </div>
        <?php if ($search_performed): ?>
        <div class="results">
            <h2>Результаты поиска</h2>
            <p>Направление: <?php echo $from; ?> → <?php echo $dest; ?></p>
            <p>Дата вылета: <?php echo $dep_time; ?></p>
            <div class="controls">
                <form method="GET">
                    <label for="filter_company">Фильтр по авиакомпании:</label>
                    <select id="filter_company" name="filter_company">
                        <option value="">Все авиакомпании</option>
                        <?php 
                        $airlines = $mysqli->query("SELECT Название FROM aviacompany");
                        while($company = $airlines->fetch_assoc()): ?>
                            <option value="<?php echo $company['Название']; ?>" 
                                <?php if($filter_company == $company['Название']) echo 'selected'; ?>>
                                <?php echo $company['Название']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <label for="sort_order">Сортировка по цене:</label>
                    <select id="sort_order" name="sort_order">
                        <option value="">По умолчанию</option>
                        <option value="asc" <?php if($sort_order == 'asc') echo 'selected'; ?>>По возрастанию</option>
                        <option value="desc" <?php if($sort_order == 'desc') echo 'selected'; ?>>По убыванию</option>
                    </select>
                    <button type="submit" name="filter">Применить</button>
                </form>
            </div>
            <?php if ($result->num_rows > 0): ?>
            <?php
                echo "<table class=flight-table >";
                echo "<tr>
                        <th>Номер рейса</th>
                        <th>Авиакомпания</th>
                        <th>Вылет из</th>
                        <th>Прилет в</th>
                        <th>Дата вылета</th>
                        <th>Время вылета</th>
                        <th>Дата прибытия</th>
                        <th>Время прибытия</th>
                        <th>Стоимость</th>
                      </tr>";
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>".$row["Номер рейса"]."</td>
                            <td>".$row["Авиакомпания"]."</td>
                            <td>".$row["Город вылета"]." (".$row["Аэропорт вылета"].")</td>
                            <td>".$row["Город назначения"]." (".$row["Аэропорт назначения"].")</td>
                            <td>".$row["Дата вылета"]."</td>
                            <td>".$row["Время вылета"]."</td>
                            <td>".$row["Дата прибытия"]."</td>
                            <td>".$row["Время прибытия"]."</td>
                            <td>".$row["Стоимость"]."</td>";
                            if (isset($_SESSION['user_id'])){
                               echo "<td><form method='post' action='purchase.php' style='display:inline;'>
                                <input type='hidden' name='flight_id' value=".$row['flight_id'].">
                                <input type='number' name='ticket_count' min='1' max=".$row['Кол-во доступных мест']." value='1' style='width: 50px;'>
                               <button type='submit' class='btn_buy'>Купить</button>
                           </form>
                           </td>";
                            }
                          echo "</tr>";
                }
                echo "</table>";
            ?>
        </div>
        <?php else: ?>
            <div class="no-results">
                <p>По заданным параметрам билеты не найдены</p>
            </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="no-results">
            <p>Задайте параметры поиска, чтобы найти доступные авиабилеты</p>
        </div>
        <?php endif; ?>
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