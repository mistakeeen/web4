<?php
function handleLogin($mysqli) {
    $error_message = '';
    $success_message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        if (empty($email) || empty($password)) {
            $error_message = "Пожалуйста, заполните все поля";
        } else {
            $query = "SELECT user_id, email, Пароль, ФИО FROM users WHERE email = '$email'";
            $result = $mysqli->query($query);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if ($password == $user['Пароль']) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['ФИО'];
                    
                    header("Location: account.php");
                    exit();
                } else {
                    $error_message = "Неверный email или пароль";
                }
            } else {
                $error_message = "Произошла ошибка";
            }
        }
    }

    if (isset($_GET['registration']) && $_GET['registration'] === 'success') {
        $success_message = "Регистрация прошла успешно! Теперь вы можете войти.";
    }

    return [
        'error' => $error_message,
        'success' => $success_message
    ];
}
?>