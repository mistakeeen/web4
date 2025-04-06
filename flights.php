<?php
require_once 'conn.php';




if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_flight'])) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    $airline_id = $_POST['airline_id'];
    $flight_number = $_POST['flight_number'];
    $departure_airport_id = $_POST['departure_airport_id'];
    $arrival_airport_id = $_POST['arrival_airport_id'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $plane_type = $_POST['type'];
    $free_seats = $_POST['seats'];

    $query = "
        INSERT INTO flight (aviacompany_id, `Номер рейса`, `Аэропорт вылета`, `Аэропорт назначения`, `Время вылета`, `Время прибытия`, `Тип самолета`, `Кол-во доступных мест`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("isiisssi", $airline_id, $flight_number, $departure_airport_id, $arrival_airport_id, $departure_time, $arrival_time, $plane_type, $free_seats);

    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $mysqli->error);
    }
    $stmt->execute();
    if (!$stmt) {
        die("Ошибка выполнения запроса: " . $mysqli->error);

    }
}

$sort = $_GET['sort'] ?? 'asc';
$filter_airline = $_GET['airline'] ?? '';


$query = "
    SELECT f.flight_id, a.Название AS airline, f.`Номер рейса`, dep.Название AS `Аэропорт вылета`, arr.Название AS `Аэропорт назначения`, 
           f.`Время вылета`, f.`Время прибытия`, f.`Тип самолета`, f.`Кол-во доступных мест`
    FROM flight f
    JOIN aviacompany a ON f.aviacompany_id = a.aviacompany_id
    JOIN airport dep ON f.`Аэропорт вылета` = dep.airport_id
    JOIN airport arr ON f.`Аэропорт назначения` = arr.airport_id
    WHERE (? = '' OR a.Название = ?)
    ORDER BY f.`Кол-во доступных мест` $sort
";

$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $mysqli->error);
}
$stmt->bind_param("ss", $filter_airline, $filter_airline);
$stmt->execute();
$result = $stmt->get_result();
$aircomp = $mysqli->query("SELECT aviacompany_id, Название FROM aviacompany");
$airports = $mysqli->query("SELECT airport_id, Название FROM airport");
if ($aircomp->num_rows == 0) {
    die("Нет данных в таблице airlines.");
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Информация о рейсах</title>
</head>
<body>
    <h1>Информация о рейсах</h1>
    <form method="GET">
        <label for="airline">Авиакомпания:</label>
        <select name="airline" id="airline">
            <option value="">Все</option>
            <?php
            $airlines = $mysqli->query("SELECT Название FROM aviacompany");
            while ($row = $airlines->fetch_assoc()) {
                $selected = ($row['Название'] == $filter_airline) ? 'selected' : '';
                echo "<option value='{$row['Название']}' $selected>{$row['Название']}</option>";
            }
            ?>
        </select>
        <label for="sort">Сортировка по количеству билетов:</label>
        <select name="sort" id="sort">
            <option value="asc" <?= $sort == 'asc' ? 'selected' : '' ?>>По возрастанию</option>
            <option value="desc" <?= $sort == 'desc' ? 'selected' : '' ?>>По убыванию</option>
        </select>
        <button type="submit">Применить</button>
    </form>

    <table border="1">
        <tr>
            <th>ID рейса</th>
            <th>Авиакомпания</th>
            <th>Номер рейса</th>
            <th>Аэропорт вылета</th>
            <th>Аэропорт назначения</th>
            <th>Время вылета</th>
            <th>Время прибытия</th>
            <th>Тип самолета</th>
            <th>Кол-во доступных мест</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['flight_id'] ?></td>
            <td><?= $row['airline'] ?></td>
            <td><?= $row['Номер рейса'] ?></td>
            <td><?= $row['Аэропорт вылета'] ?></td>
            <td><?= $row['Аэропорт назначения'] ?></td>
            <td><?= $row['Время вылета'] ?></td>
            <td><?= $row['Время прибытия'] ?></td>
            <td><?= $row['Тип самолета'] ?></td>
            <td><?= $row['Кол-во доступных мест'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Добавить новый рейс</h2>
    <form method="POST">
    <label for="airline_id">Аэропорт прибытия:</label>
       <?php 
       $airlines_result = $mysqli->query("SELECT aviacompany_id, Название FROM aviacompany");
       echo "<select name='airline_id'>";
       while ($row = $airlines_result->fetch_assoc()) {
           echo "<option value='{$row['aviacompany_id']}'>{$row['Название']}</option>";
       }
       echo "</select>";
       ?>
        </select><br>

        <label for="flight_number">Номер рейса:</label>
        <input type="text" name="flight_number" id="flight_number" required><br>

        <label for="departure_airport_id">Аэропорт вылета:</label>
        <select name="departure_airport_id" id="departure_airport_id" required>
            <?php $airports->data_seek(0); ?>
            <?php while ($row = $airports->fetch_assoc()): ?>
                <option value="<?= $row['airport_id'] ?>"><?= $row['Название'] ?></option>
            <?php endwhile; ?>
        </select><br>

        <label for="arrival_airport_id">Аэропорт прибытия:</label>
        <select name="arrival_airport_id" id="arrival_airport_id" required>
            <?php $airports->data_seek(0); ?>
            <?php while ($row = $airports->fetch_assoc()): ?>
                <option value="<?= $row['airport_id'] ?>"><?= $row['Название'] ?></option>
            <?php endwhile; ?>
        </select><br>

        <label for="departure_time">Время вылета:</label>
        <input type="datetime-local" name="departure_time" id="departure_time" required><br>

        <label for="arrival_time">Время прибытия:</label>
        <input type="datetime-local" name="arrival_time" id="arrival_time" required><br>

        
        <label for="type">Тип самолета:</label>
        <input type="text" name="type" id="type" required><br>

        <label for="seats">Количество доступных мест:</label>
        <input type="number" step="1" name="seats" id="seats" required><br>

        <button type="submit" name="add_flight">Добавить рейс</button>
    </form>
</body>
</html>