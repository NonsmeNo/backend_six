<?php
include('functions.php');
// HTTP-авторизации
// HTTP-аутентификации
// PHP хранит логин и пароль в суперглобальном массиве $_SERVER.

// нужно прочитать отправленные ранее пользователями данные и вывести в таблицу.
// Реализовать просмотр и удаление всех данных.

if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){ 
  $stmt = $db->prepare("SELECT * FROM admin where login=?"); //получаем данные из таблицы admin
  $stmt -> execute([$_SERVER['PHP_AUTH_USER']]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC); 
  }

  if (!password_verify($_SERVER['PHP_AUTH_PW'], $result['password']) || !$result) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $messages2 = array();

    if(isset($_POST["row"])) {

            $ids = $_POST["id"];
            $rows = $_POST["row"];
            
        
        if (isset($_POST['delete'])) { // после нажатия кнопки "УДАЛИТЬ" удаляем данные пользователя из БД
            
            foreach ($rows as $row) {

            $stmt = $db->prepare("DELETE FROM application_5 WHERE id=?");
            $stmt -> execute([$ids[$row]]);
            
            $stmt = $db->prepare("DELETE FROM application_ability_5 WHERE application_id=?");
            $stmt -> execute([$ids[$row]]);
            }

            $messages2[] = 'Элементы удалены';
        }
        
        if (isset($_POST['update'])) { // после нажатия кнопки "ИЗМЕНИТЬ" проверяем правильность введения новых данных
            $errors=array();
            foreach ($rows as $row) {
                $data = [
                    'name' => $_POST['name'][$row],
                    'email' => $_POST['email'][$row],
                    'birth' => $_POST['birth'][$row],
                    'gender' => $_POST['gender' . $row],
                    'limbs' => $_POST['limbs' . $row],
                    'biography' => $_POST['biography'][$row],
                    'agree' => 1
                ];

                $ability = $_POST['ability' . $row];
                $errors[$row]= check_data($data, $ability, $row);

            }
            if (count(array_filter($errors))!=0) {
                // При наличии ошибок перезагружаем страницу и завершаем работу скрипта.
                header('Location: admin.php');
                exit();
            }
            else{
                foreach ($rows as $row) {

                    $data = [
                        'name' => $_POST['name'][$row],
                        'email' => $_POST['email'][$row],
                        'birth' => $_POST['birth'][$row],
                        'gender' => $_POST['gender' . $row],
                        'limbs' => $_POST['limbs' . $row],
                        'biography' => $_POST['biography'][$row]
                    ];
                    
                    $ability = $_POST['ability' . $row];
                    
                    update_application_5($db, $ids[$row], $data, $ability);
                    
                }
                
                $messages2[] = 'Результаты сохранены.';
            }
        }
    } else {
        $messages2[] = 'Вы не выбрали ни одного элемента, который хотите сохранить или удалить!';
    }
}

    print('Вы авторизовались как admin.');

$stmt = $db->prepare("SELECT count(*) FROM application_ability_5 WHERE ability_id=?");

    print("<br>");
    $stmt -> execute(['1']);
    print("Количество людей со способностью бессмертие: ");
    print($stmt->fetchAll(PDO::FETCH_ASSOC)[0]["count(*)"]);

    print("<br>");
    $stmt -> execute(['2']);
    print("Количество людей со способностью прохождение сквозь стены: ");
    print($stmt->fetchAll(PDO::FETCH_ASSOC)[0]["count(*)"]);

    print("<br>");
    $stmt -> execute(['3']);
    print("Количество людей со способностью левитация: ");
    print($stmt->fetchAll(PDO::FETCH_ASSOC)[0]["count(*)"]);


    $stmt = $db->prepare("SELECT * FROM application_5");
    $stmt -> execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>









<html>
    <head>
    <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="stylesheet" href="style.css">
        <title>Администратор</title>
    </head>

    <body>
        
        <?php 
        if (!empty($messages2)) {
            print('<div id="messages">');
            // Выводим все сообщения.
            foreach ($messages2 as $message) {
                print($message);
                
            }
            print('</div>');
        }
        ?>

        <?php
        $counter = 0;
        foreach ($result as $res): 
        ?>

        <?php
        $errors = array();
        $errors = value_errors($counter);
        $messages = array();
        $messages = messages_errors($messages, $errors, $counter);

        if (!empty($messages)) {
            print('<div id="messages">');
            // Выводим все сообщения.
            foreach ($messages as $message) {
                print($message);
                
            }
            
            print("<div class='error'>Поля, содержащие ошибки были подсвечены. Последние данные, не содержащие ошибок, были подгружены из бд</div>");
            
            print('</div>');
        }
        $counter++; 
        ?>

        <?php endforeach; ?>

        <form class="form_admin" action="" method="POST">
            <table class="table table-bordered">
                <tr>
                    <th scope="col">id</th>
                    <th scope="col">имя</th>
                    <th scope="col">email</th>
                    <th scope="col">дата рождения</th>
                    <th scope="col">пол</th>
                    <th scope="col">конечности</th>
                    <th scope="col">способности</th>
                    <th scope="col">биография</th>

                    <th scope="col">uid</th>
                    <th scope="col">Выбрать</th>
                </tr>

                <?php
                $counter = 0;
                foreach ($result as $res): 
                ?>

                <?php
                $errors = array();
                $errors=value_errors($counter);
                ?>

                <tr>
                    <td><?= $res["id"] ?></td>

                    <input name="id[]" value="<?= strip_tags($res["id"]) ?>" type="hidden">

                    <td>
                        <input name="name[]" placeholder="Введите имя" value="<?= strip_tags($res["name"])  ?>">
                    </td>

                    <td>
                        <input name="email[]" type="email" placeholder="Введите email" value="<?= strip_tags($res["email"]) ?>">
                    </td>

                    <td>
                        <input name="birth[]" type="date" value="<?=strip_tags($res["birth"]) ?>">
                    </td>



                    <td> 
                        <label>
                            <input type="radio" name="gender<?= $counter ?>" 
                            value="M" <?php if ($res["gender"]=="M") {print 'checked';} ?>>МУЖСКОЙ
                        </label> 

                        <label>
                            <input type="radio" name="gender<?= $counter ?>"
                            value="F" <?php if ($res["gender"]=="F") {print 'checked';} ?>>ЖЕНСКИЙ
                        </label>
                    </td>
                                    
                    <td> 
                        <label>
                            <input type="radio" name="limbs<?= $counter ?>"
                            value="2" <?php if ($res["limbs"]=="1") {print 'checked';} ?>>1
                        </label> 

                        <label>
                            <input type="radio" name="limbs<?= $counter ?>"
                            value="2" <?php if ($res["limbs"]=="2") {print 'checked';} ?>>2
                        </label> 

                        <label>
                            <input type="radio" name="limbs<?= $counter ?>"
                            value="2" <?php if ($res["limbs"]=="3") {print 'checked';} ?>>3
                        </label> 

                        <label>
                            <input type="radio" name="limbs<?= $counter ?>"
                            value="4" <?php if ($res["limbs"]=="4") {print 'checked';} ?>>4
                        </label>
                    </td>


                    <?php
                        $stmt = $db->prepare("SELECT * FROM application_ability_5 where application_id=?");
                        $stmt -> execute([$res["id"]]);
                        $result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <td> 
                        <select name="ability<?= $counter ?>[]" multiple="multiple">
                            <option value="immortality" <?php if(!empty($result2)) {if ($result2[0]['ability_id']=='1') {print 'selected';}} ?>>Бессмертие</option>
                            
                            <option value="passingWalls" <?php if(!empty($result2)) {if ((isset($result2[0]['ability_id']) && $result2[0]['ability_id'] == '2') ||
                            (isset($result2[1]['ability_id']) && $result2[1]['ability_id'] == '2')) {print 'selected';}} ?>>Прохождение сквозь стены</option>

                            <option value="levitation" <?php if(!empty($result2)) {if ((isset($result2[0]['ability_id']) && $result2[0]['ability_id'] == '3') ||
                            (isset($result2[1]['ability_id']) && $result2[1]['ability_id'] == '3') ||
                            (isset($result2[2]['ability_id']) && $result2[2]['ability_id'] == '3')) {print 'selected';}} ?>>Левитация</option>
                        </select>
                    </td>

                    <td>
                        <textarea  name="biography[]" ><?= strip_tags($res["biography"]) ?></textarea>
                    </td>

                    <td>
                        <?= $res["user_id"] ?>
                    </td>

                    <td>
                        <input type="checkbox" name="row[]" value="<?= $counter ?>">
                    </td>
                    
                    <?php $counter++ ?>
                </tr>

                <?php endforeach; ?>
            </table>

            <button type="submit" name="update" value="upd">ИЗМЕНИТЬ</button>
            <button id="del_adm" type="submit" name="delete" value="del">УДАЛИТЬ</button>

        </form>

        <?php $stmt = $db->prepare("SELECT * FROM application_ability_5");
        $stmt -> execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>

    </body>
</html>
