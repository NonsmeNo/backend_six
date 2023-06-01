<?php

    global $db;
    $user = 'u52945';
    $pass = '3219665';
        $db = new PDO('mysql:host=localhost;dbname=u52945', $user, $pass, [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        

//функция изменения таблиц application_5 и application_ability_5
function update_application_5 ($db, $id, $data, $allability) {


    $stmt = $db->prepare("UPDATE application_5 SET name = ?, email = ?, biography = ?,gender = ?, limbs = ?, birth = ?  WHERE id = ?");
    $stmt -> execute([$data['name'], $data['email'],$data['biography'] , $data['gender'], $data['limbs'], $data['birth'], $id]);

    $stmt = $db->prepare("SELECT * FROM application_ability_5 where application_id=(SELECT id FROM application_5 where user_id=?)");
    $stmt -> execute([$id]);
    $row_2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    $flag = false;
    foreach ($allability as $ability) {

        foreach ($row_2 as $one_row) {
            if ($one_row != $ability) {
                $found = true;
            }
        }

    }

    if($flag) { //если меняются данные, то удаляем старые данные из бд и вставляем новые
        $stmt = $db->prepare("DELETE FROM application_ability_5 WHERE application_id = ?");
        $stmt -> execute([$id]);
        

        foreach ($allability as $ability)
        {
            $stmt = $db->prepare("INSERT INTO application_ability_5 (application_id, ability_id)
            VALUES (:application_id, :ability_id");
            $stmt->bindParam(':application_id', $id);
            $stmt->bindParam(':ability_name', $ability);
            $stmt->execute();
        }
    }   
            
  }
// функция проверки ошибок
function check_data($data, $allability, $row=null) {
    $errors = false;
    
    if (empty($data['name']) || !preg_match('/^[A-ZЁА-Я][a-zа-яёъ]+$/u', $data['name'])) {
        setcookie('name_error'.$row, '1', time() + 86400); 
        $errors = TRUE;
    } else {
        setcookie('name_value', $data['name'], time() + 86400 * 30); 
    }
    

    if (empty($data['email']) || !preg_match('/^[A-Z0-9a-z-_.]+[@][a-z]+[.][a-z]+$/', $data['email'])) {
        setcookie('email_error'.$row, '1', time() + 86400);
        $errors = TRUE;
      }
      else {
        setcookie('email_value', $data['email'], time() + 30 * 86400);
      }
    
    
    $today = date('Y-m-d');
    if (!empty($data['birth'])) {
      $expire = $data['birth'];
    }
  
    $today_dt = new DateTime($today);
    $expire_dt = new DateTime($expire);
  
    if (empty($data['birth']) || !preg_match('/[12][90][0-9][0-9][-][0-1][0-9]-[0-3][0-9]/', $data['birth']) || ($today_dt < $expire_dt) ) {
      setcookie('birth_error'.$row, '1', time() + 86400);
      $errors = TRUE;
    }
    else {
      setcookie('birth_value', $data['birth'], time() + 30 * 86400);
    }

    if (empty($data['biography'])) {
        setcookie('biography_error'.$row, '1', time() + 86400);
        $errors = TRUE;
    } else {
        setcookie('biography_value', $data['biography'], time() + 30 * 86400);
    }   

    if (empty($data['gender'])) {
        setcookie('gender_error'.$row, '1', time() + 86400);
        $errors = TRUE;
    } else {
        setcookie('gender_value', $data['gender'], time() + 30 * 86400);
    }

    if (empty($data['limbs'])) {
        setcookie('limbs_error'.$row, '1', time() + 86400);
        $errors = TRUE;
      }
      else {
        setcookie('limbs_value', $data['limbs'], time() + 30 * 86400);
      }


    if (empty($allability)) {
        setcookie('ability_error'.$row, '1', time() + 86400);
        $errors = TRUE;
    } else {
        $array = array();
        foreach ($allability as $ability)
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
    
    
    if (empty($data['agree'])) {
        setcookie('agree_error'.$row, '1', time() + 86400);
        setcookie('agree_value', '0', time() + 30 * 86400);
        $errors = TRUE;
      }
      else {
        setcookie('agree_value', '1', time() + 30 * 86400);
      }

    return $errors;
}

// установка значений элементам массива errors
function value_errors($counter=null){

    $errors = array();
    $errors['name'] = !empty($_COOKIE['name_error'.$counter]);
    $errors['email'] = !empty($_COOKIE['email_error'.$counter]);
    $errors['biography'] = !empty($_COOKIE['biography_error'.$counter]);
    $errors['gender'] = !empty($_COOKIE['gender_error'.$counter]);
    $errors['limbs'] = !empty($_COOKIE['limbs_error'.$counter]);
    $errors['agree'] = !empty($_COOKIE['agree_error'.$counter]);
    $errors['ability'] = !empty($_COOKIE['ability_error'.$counter]);
    $errors['birth'] = !empty($_COOKIE['birth_error'.$counter]);

    return $errors;
}

//функция вывода сообщений об ошибках
function messages_errors($messages, $errors, $counter=null){
   
     // Выдаем сообщения об ошибках.
    if ($errors['name']) {
        setcookie('name_error'.$counter, '', 100000); // Удаляем куку, указывая время устаревания в прошлом.
        $messages[] = '<div class="error">Имя не может быть пустым, должно содержать только буквы, начинаться с заглавной буквы и не должнно содержать пробелы</div>';
    }
    
    if ($errors['email']) {
        setcookie('email_error'.$counter, '', 100000);
        $messages[] = '<div class="error">Введен пустой или некорректный E-mail. E-mail может содержать только латинские буквы, цифры, а также символы - _ .</div>';
    }

    if ($errors['biography']) {
        setcookie('biography_error'.$counter, '', 100000);
        $messages[] = '<div class="error">Добавьте вашу биографию</div>';
    }

    if ($errors['gender']) {
        setcookie('gender_error'.$counter, '', 100000);
        $messages[] = '<div class="error">Выберите пол</div>';
    }

    if ($errors['birth']) {
        setcookie('birth_error'.$counter, '', 100000);
        $messages[] = '<div class="error">Дата рождения не может быть пустой, она должна быть меньше нынешней даты, и год должен быть не меньше 1900</div>';
    }

    if ($errors['limbs']) {
        setcookie('limbs_error'.$counter, '', 100000);
        $messages[] = '<div class="error">Выберите число конечностей</div>';
    }

    if ($errors['ability']) {
        setcookie('ability_error'.$counter, '', 100000);
        $messages[] = '<div class="error">Выберите сверхспособности</div>';
    }

    if ($errors['agree']) {
        setcookie('agree_error'.$counter, '', 100000);
        $messages[] = '<div class="error">Вы не ознакомились с контрактом</div>';
    }

    return $messages;
}



