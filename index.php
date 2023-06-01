<?php
header('Content-Type: text/html; charset=UTF-8');
include('functions.php');

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
  $errors=value_errors();
  $messages = messages_errors($messages, $errors);


  $values = array();
  $values['name'] = empty($_COOKIE['name_value']) ? '' : strip_tags($_COOKIE['name_value']);
  $values['email'] = empty($_COOKIE['email_value']) ? '' : strip_tags($_COOKIE['email_value']);
  $values['biography'] = empty($_COOKIE['biography_value']) ? '' : strip_tags($_COOKIE['biography_value']);
  $values['gender'] = empty($_COOKIE['gender_value']) ? '' : strip_tags($_COOKIE['gender_value']);
  $values['limbs'] = empty($_COOKIE['limbs_value']) ? '' : strip_tags($_COOKIE['limbs_value']);
  $values['birth'] = empty($_COOKIE['birth_value']) ? '' : strip_tags(($_COOKIE['birth_value']));
  $values['ability'] = empty($_COOKIE['ability_value']) ?  array() : unserialize($_COOKIE['ability_value']);
  $values['agree'] = empty($_COOKIE['agree_value']) ? '' : strip_tags($_COOKIE['agree_value']);


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
   
    $messages[] = sprintf('<div class="collogin">ВЫПОЛНЕН ВХОД<br> 
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
  $data = [
    'name' => $_POST['name'],
      'email' => $_POST['email'],
      'biography' => $_POST['biography'],
      'gender' => $_POST['gender'],
      'limbs' => $_POST['limbs'],
      'birth' => $_POST['birth'],
      'agree' => $_POST['agree']
];

$allability = $_POST['ability'];
$errors = check_data($data, $allability);

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

    $stmt = $db->prepare("SELECT id FROM application_5 WHERE user_id = ?");
    $stmt -> execute([$_SESSION['uid']]);

    $row_3 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $app_id = $row_3[0]["id"];
    
    $data = [
      'name' => $_POST['name'],
      'email' => $_POST['email'],
      'biography' => $_POST['biography'],
      'gender' => $_POST['gender'],
      'limbs' => $_POST['limbs'],
      'birth' => $_POST['birth']
    ];
    
    $allability = $_POST['ability'];
    update_application_5($db, $app_id, $data, $allability);
   
  }

  else {

    // Генерируем уникальный логин и пароль.
    $login = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, rand(3,9)).rand(1000, 999999);;
    $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzQWERTYUIOPASDFGHJKLZXCVBNM0123456789*-+!#$%&_'), 0, rand(10,15));
    
    // Сохраняем в Cookies.
    setcookie('login', $login);
    setcookie('pass', $pass);

    // TODO: Сохранение данных формы, логина и хеш пароля в базу данных.
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