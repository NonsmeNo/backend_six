<?php
//ХЭШШШШШШШШ
header('Content-Type: text/html; charset=UTF-8');

$db = new PDO('mysql:host=localhost;dbname=u52945', 'u52945', '3219665',
  [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
   $messages = array();

  if (!empty($_COOKIE['save'])) {

    setcookie('save', '', 100000);//удаление
    setcookie('login', '', 100000);
    setcookie('pass', '', 100000);
    $messages[] = '<div class="saves">Спасибо, результаты сохранены!</div>';

    // Если в куках есть пароль, то выводим сообщение.
    if (!empty($_COOKIE['pass'])) {
      $messages[] = sprintf('<div class="collogin"><a href="login.php">войти</a> <br> 
      с логином <strong>%s</strong>
      <br> и паролем <strong>%s</strong> <br> для изменения данных.</div>',
        strip_tags($_COOKIE['login']),
        strip_tags($_COOKIE['pass']));
    }
  }

  $errors = array();
  $errors['name'] = !empty($_COOKIE['name_error']);
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['biography'] = !empty($_COOKIE['biography_error']);
  $errors['gender'] = !empty($_COOKIE['gender_error']);
  $errors['limbs'] = !empty($_COOKIE['limbs_error']);
  $errors['agree'] = !empty($_COOKIE['agree_error']);
  $errors['ability'] = !empty($_COOKIE['ability_error']);
  $errors['birth'] = !empty($_COOKIE['birth_error']);

  
  // Выдаем сообщения об ошибках.
  if ($errors['name']) {
    // Удаляем куку, указывая время устаревания в прошлом.
    setcookie('name_error', '', 100000);
    // Выводим сообщение.
    $messages[] = '<div class="error">Имя не может быть пустым, должно содержать только буквы, начинаться с заглавной буквы и не должнно содержать пробелы</div>';
  }
  
  if ($errors['email']) {
    setcookie('email_error', '', 100000);

    $messages[] = '<div class="error">Введен пустой или некорректный E-mail. E-mail может содержать только латинские буквы, цифры, а также символы - _ .</div>';
  }

  if ($errors['biography']) {
    setcookie('biography_error', '', 100000);

    $messages[] = '<div class="error">Добавьте вашу биографию</div>';
  }

  if ($errors['gender']) {
    setcookie('gender_error', '', 100000);

    $messages[] = '<div class="error">Выберите пол</div>';
  }

  if ($errors['birth']) {
    setcookie('birth_error', '', 100000);

    $messages[] = '<div class="error">Дата рождения не может быть пустой, она должна быть меньше нынешней даты, и год должен быть не меньше 1900</div>';
  }

  if ($errors['limbs']) {
    setcookie('limbs_error', '', 100000);

    $messages[] = '<div class="error">Выберите число конечностей</div>';
  }

  if ($errors['ability']) {
    setcookie('ability_error', '', 100000);

    $messages[] = '<div class="error">Выберите сверхспособности</div>';
  }

  if ($errors['agree']) {

    $messages[] = '<div class="error">Вы не ознакомились с контрактом</div>';
    setcookie('agree_error', '', 100000);

  }

  $values = array();
  $values['name'] = empty($_COOKIE['name_value']) ? '' : strip_tags($_COOKIE['name_value']);
  $values['email'] = empty($_COOKIE['email_value']) ? '' : strip_tags($_COOKIE['email_value']);
  $values['biography'] = empty($_COOKIE['biography_value']) ? '' : strip_tags($_COOKIE['biography_value']);
  $values['gender'] = empty($_COOKIE['gender_value']) ? '' : strip_tags($_COOKIE['gender_value']);
  $values['limbs'] = empty($_COOKIE['limbs_value']) ? '' : strip_tags($_COOKIE['limbs_value']);
  $values['agree'] = empty($_COOKIE['agree_value']) ? '' : strip_tags($_COOKIE['agree_value']);
  $values['birth'] = empty($_COOKIE['birth_value']) ? '' : strip_tags(($_COOKIE['birth_value']));
  $values['ability'] = empty($_COOKIE['ability_value']) ?  array() : unserialize($_COOKIE['ability_value']);


  // Если нет предыдущих ошибок ввода, есть кука сессии, начали сессию и
  // ранее в сессию записан факт успешного логина.
  if (count(array_filter($errors)) === 0 && !empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
    $stmt = $db->prepare("SELECT * FROM application_5 where user_id=?");
    $stmt -> execute([$_SESSION['uid']]);
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $values['name'] = empty($row[0]['name']) ? '' : strip_tags($row[0]['name']);
    $values['email'] = empty($row[0]['email']) ? '' : strip_tags($row[0]['email']);
    $values['biography'] = empty($row[0]['biography']) ? '' : strip_tags($row[0]['biography']);
    $values['gender'] = empty($row[0]['gender']) ? '' : strip_tags($row[0]['gender']);
    $values['limbs'] = empty($row[0]['limbs']) ? '' : strip_tags($row[0]['limbs']);
    $values['birth'] = empty($row[0]['birth']) ? '' :strip_tags($row[0]['birth']);
    //$values['agree'] = empty($result[0]['agree']) ? '' : strip_tags($result[0]['agree']);

    $stmt = $db->prepare("SELECT * FROM application_ability_5 where application_id=(SELECT id FROM application_5 where user_id=?)");
    $stmt -> execute([$_SESSION['uid']]);
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($row as $one_ab) {

      switch ($one_ab['ability_id']) {
        case 1:
          $values['ability'][0] = empty($one_ab) ? :'immortality';
            break;
        case 2:
          $values['ability'][1] = empty($one_ab) ? :'passingWalls';
            break;
        case 3:
          $values['ability'][2] = empty($one_ab) ? :'levitation';
            break;
      }
    }
   
    $messages[] = sprintf('<div class="collogin">ВЫПОЛНЕН ВХОД <br> 
    с логином <strong>%s</strong> и uid <strong>%s</strong>
    <br>вы можете изменить свои данные</div>',
    $_SESSION['login'],
    $_SESSION['uid']);

  }

  include('form.php');
}










//-----------------------------------------------------------------------------------------
// Иначе если POST (нужно проверить данные на пустоту или правильный ввод и сохранить их в файл)

else {

  // окончание сессии
  if (isset($_POST['exit']) && $_POST['exit'] == 'true') {
    session_destroy();
    setcookie(session_name(), '', 100000);
    setcookie('PHPSESSID', '', 100000, '/');
   
    header('Location: ./');
    exit();
  }

  // Проверяем ошибки
  $errors = FALSE;
  if (empty($_POST['name']) || !preg_match('/^[A-ZЁА-Я][a-zа-яёъ]+$/u', $_POST['name'])) {

    setcookie('name_error', '1', time() + 86400); 
    $errors = TRUE;
  }
  else {
    setcookie('name_value', $_POST['name'], time() + 86400 * 30); 
  }



  if (empty($_POST['email']) || !preg_match('/^[A-Z0-9a-z-_.]+[@][a-z]+[.][a-z]+$/', $_POST['email'])) {
    setcookie('email_error', '1', time() + 86400);
    $errors = TRUE;
  }
  else {
    setcookie('email_value', $_POST['email'], time() + 30 * 86400);
  }

  $today = date('Y-m-d');
  if (!empty($_POST['birth'])) {
    $expire = $_POST['birth'];
  }

  $today_dt = new DateTime($today);
  $expire_dt = new DateTime($expire);

  if (empty($_POST['birth']) || !preg_match('/[12][90][0-9][0-9][-][0-1][0-9]-[0-3][0-9]/', $_POST['birth']) || ($today_dt < $expire_dt) ) {
    setcookie('birth_error', '1', time() + 86400);
    $errors = TRUE;
  }
  else {
    setcookie('birth_value', $_POST['birth'], time() + 30 * 86400);
  }


  if (empty($_POST['biography'])) {
    setcookie('biography_error', '1', time() + 86400);
    $errors = TRUE;
  }
  else {
    setcookie('biography_value', $_POST['biography'], time() + 30 * 86400);
  }



  if (empty($_POST['gender'])) {
    setcookie('gender_error', '1', time() + 86400);
    $errors = TRUE;
  }
  else {
    setcookie('gender_value', $_POST['gender'], time() + 30 * 86400);
  }



  if (empty($_POST['limbs'])) {
    setcookie('limbs_error', '1', time() + 86400);
    $errors = TRUE;
  }
  else {
    setcookie('limbs_value', $_POST['limbs'], time() + 30 * 86400);
  }




  if (empty($_POST['agree'])) {
    setcookie('agree_error', '1', time() + 86400);
    setcookie('agree_value', '0', time() + 30 * 86400);
    $errors = TRUE;
  }
  else {
    setcookie('agree_value', '1', time() + 30 * 86400);
  }


  

  if (empty($_POST['ability'])) {
    setcookie('ability_error', '1', time() + 86400);
    $errors = TRUE;
  }
  else {
    $array = array();
    foreach ($_POST['ability'] as $ability)
    {
      switch ($ability) {
        case "immortality":
            $array[0] = $ability;
            break;
        case "passingWalls":
            $array[1] = $ability;
            break;
        case "levitation":
            $array[2] = $ability;
            break;
    }
    }   

    setcookie('ability_value', serialize($array), time() + 30 * 86400);
  }

  if ($errors) {
    header('Location: index.php');
    exit();
  }
  else {
    setcookie('name_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('biography_error', '', 100000);
    setcookie('gender_error', '', 100000);
    setcookie('limbs_error', '', 100000);
    setcookie('agree_error', '', 100000);
    setcookie('ability_error', '', 100000);
    setcookie('birth_error', '', 100000);
  }
  // Проверяем меняются ли ранее сохраненные данные или отправляются новые.
  if (!empty($_COOKIE[session_name()]) &&
  session_start() && !empty($_SESSION['login'])) {


      $stmt = $db->prepare("UPDATE application_5 SET name = ?, email = ?, biography = ?,gender = ?, limbs = ?, birth = ?  WHERE user_id = ?");
      $stmt -> execute([$_POST['name'], $_POST['email'],$_POST['biography'] , $_POST['gender'], $_POST['limbs'], $_POST['birth'], $_SESSION['uid']]);

      $stmt = $db->prepare("SELECT * FROM application_ability_5 where application_id=(SELECT id FROM application_5 where user_id=?)");
      $stmt -> execute([$_SESSION['uid']]);
      $row_2 = $stmt->fetchAll(PDO::FETCH_ASSOC);


      $flag=false;

        foreach ($_POST['ability'] as $ability)
        {
          switch ($ability) {
            case "immortality":
                if ($array[0] != $ability) {
                  $flag=true;
                  break;
                }
            case "passingWalls":
              if ($array[1] != $ability) {
                $flag=true;
                break;
              }
            case "levitation":
              if ($array[2] != $ability) {
                $flag=true;
                break;
            }
          }
        }




      if($flag) { //если меняются данные, то удаляем старые данные из бд и вставляем новые
        $stmt = $db->prepare("DELETE FROM application_ability_5 WHERE application_id=(SELECT id FROM application_5 where user_id=?) ");
        $stmt -> execute([$_SESSION['uid']]);

        $stmt = $db->prepare("SELECT id FROM application_5 where user_id=? ");
        $stmt -> execute([$_SESSION['uid']]);
        $row_3 = $stmt->fetchAll(PDO::FETCH_ASSOC);


        foreach ($_POST['ability'] as $ability)
        {
          $stmt = $db->prepare("INSERT INTO application_ability_5 (application_id, ability_id)
          VALUES (:application_id, (SELECT id FROM ability WHERE name=:ability_name))");
          $stmt->bindParam(':application_id', $row_3[0]["id"]);
          $stmt->bindParam(':ability_name', $ability);
          $stmt->execute();
        }   
        
      }

  }

  else {
    // Генерируем уникальный логин и пароль.
    $login = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, rand(3,9)).rand(1000, 999999);;
    $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzQWERTYUIOPASDFGHJKLZXCVBNM0123456789*-+!#$%&_'), 0, rand(10,15));;
    //$pass = uniqid();
    // Сохраняем в Cookies.
    setcookie('login', $login);
    setcookie('pass', $pass);

    // TODO: Сохранение данных формы, логина и хеш md5() пароля в базу данных.
    //-------------------------------Сохранение в базу данных.----------------------
  try {
    $stmt = $db->prepare("INSERT INTO users_5 (login, password) VALUES (?,?)");
        $stmt->execute([$login, password_hash($pass, PASSWORD_DEFAULT)]);

      $uid = $db->lastInsertId();

    $stmt = $db->prepare("INSERT INTO application_5 (name, email, biography, gender, limbs, birth, user_id) 
    VALUES (:name, :email, :biography, :gender, :limbs, :birth, :user_id)");
    $stmt->bindParam(':name', $_POST['name']);
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->bindParam(':biography', $_POST['biography']);
    $stmt->bindParam(':gender', $_POST['gender']);
    $stmt->bindParam(':limbs', $_POST['limbs']);
    $stmt->bindParam(':birth', $_POST['birth']);
    $stmt->bindParam(':user_id', $uid);
    $stmt->execute();

    $application_id = $db->lastInsertId();

    foreach ($_POST['ability'] as $ability)
    {
      $stmt = $db->prepare("INSERT INTO application_ability_5 (application_id, ability_id)
      VALUES (:application_id, (SELECT id FROM ability WHERE name=:ability_name))");
      $stmt->bindParam(':application_id', $application_id);
      $stmt->bindParam(':ability_name', $ability);
      $stmt->execute();
    }   
  }

  catch(PDOException $e) {
    print('ошибка при отправке данных: ' .$e->getMessage());
    exit();
  }
}

  //--------------------------------------------------------------------------

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');

  // Делаем перенаправление.
  header('Location: index.php');

}