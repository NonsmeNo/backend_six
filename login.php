<?php

/**
 * Файл login.php для не авторизованного пользователя выводит форму логина.
 * При отправке формы проверяет логин/пароль и создает сессию,
 * записывает в нее логин и id пользователя.
 * После авторизации пользователь перенаправляется на главную страницу
 * для изменения ранее введенных данных.
 **/

header('Content-Type: text/html; charset=UTF-8');
$db = new PDO('mysql:host=localhost;dbname=u52945', 'u52945', '3219665',
  [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);


// Начинаем сессию.
session_start();
// В суперглобальном массиве $_SESSION хранятся переменные сессии.
// Будем сохранять туда логин после успешной авторизации
if (!empty($_SESSION['login'])) {
  header('Location: ./');
}



// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Авторизация</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
<form class="form1" action="" method="POST">

  <h2>Авторизация</h2>

  <div class="fields">
      <div class="item">
          <label for="name">Логин</label><br>
          <input name="login" type="text" placeholder="Введите логин" />
      </div>
      <div class="item">
              <label for="email">Пароль</label><br>
              <input name="pass" type="text" placeholder="Введите пароль" />     
      </div>
  </div>

    <div>
      <button  type="submit">Войти</button>
    </div>
</form>
</body>


</html>
<?php
}
// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {

  // TODO: Проверть есть ли такой логин и пароль в базе данных.
  // Выдать сообщение об ошибках.

  try {
      $stmt = $db->prepare("SELECT * FROM users_5 where login=?");
      $stmt -> execute([$_POST['login']]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $flag=false;
      if(password_verify($_POST['pass'], $row['password']))
      {
          $_SESSION['login'] = $_POST['login']; 
          $_SESSION['uid'] =$row["id"];
          header('Location: ./');
      }
   
        
    }
    catch(PDOException $e){
      print('Ошибка при авторизации: ' . $e->getMessage());
      exit();

  }
 
}
