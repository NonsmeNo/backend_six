<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="style.css">
    <title>backend_five</title>
</head>
<body>

<div class="content">

    <?php
        if (!empty($messages)) {
            print('<div id="messages">');
            foreach ($messages as $message) {
                print($message);
            }
            print('</div>');
        }
    ?>


    <form  class="form1" action="index.php" method="POST">

        <h2>Регистрация</h2>

        <div class="fields">
            <div class="item">
                <label for="name">Имя</label><br>
                <input type="text" name="name" value="<?php print $values['name'];?>" placeholder="Введите ваше имя">
            </div>
            <div class="item">
                    <label for="email">E-mail</label><br>
                    <input type="text" name="email" value="<?php print $values['email'];?>" placeholder="Введите e-mail">     
            </div>
            <div class="item">
                Ваша биография<br>
            <textarea name="biography" value="<?php print $values['biography'];?>" placeholder="Расскажите о себе..." ><?php print $values['biography'];?></textarea>
            </div>
        </div>

        <div class="inlblock">
            <div class="colblock">

                <div class="gender">
                    Ваш пол:<br> 
                    <div class="changeGen">
                        <label>
                            <input type="radio" name="gender" value="F" <?php if ($values['gender']=='F') {print 'checked';} ?> >
                            Женский &#9792;
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="gender" value="M"  <?php if ($values['gender']=='M') {print 'checked';} ?> >
                            Мужской &#9794;
                        </label>
                    </div>
                </div>  

                <div class="birth">
                    <label>
                        Дата Рождения:<br>
                        <input name="birth" type="date" value="<?php print $values['birth'];?>">
                    </label> 
                </div>
            

                
            </div>
        
            <div class="colblock">

                <div class="limbss">
                Количество <br> Ваших конечностей: <br> 
                    <div class="rad">
                        <label>
                            <input type="radio" name="limbs" value="1" <?php if ($values['limbs']=='1') {print 'checked';}?> > 1
                        </label>
                        <label>
                            <input type="radio" name="limbs" value="2" <?php if ($values['limbs']=='2') {print 'checked';}?>> 2
                        </label>
                        <label>
                            <input type="radio" name="limbs" value="3" <?php if ($values['limbs']=='3') {print 'checked';}?>> 3
                        </label>
                        <label>
                            <input type="radio" name="limbs" value="4" <?php if ($values['limbs']=='4') {print 'checked';}?>> 4
                        </label>
                    </div>
                </div>

                <div class="superpowers">   
                    <label>
                        Ваши <br> сверхспособности:<br>
                        <div class="powers">
                            <select name="ability[]" multiple="multiple">
                                <option value="immortality" <?php if(!empty($values['ability'][0])) {if ($values['ability'][0]=="immortality") {print 'selected';}} ?>> Бессмертие </option>
                                <option value="passingWalls" <?php if(!empty($values['ability'][1])) {if ($values['ability'][1]=="passingWalls") {print 'selected';}} ?>> Прохождение сквозь стены </option>
                                <option value="levitation" <?php if(!empty($values['ability'][2])) {if ($values['ability'][2]=="levitation") {print 'selected';}} ?>> Левитация </option>
                            </select>
                        </div>
                    </label> 
                </div>
        
            </div>
        
        </div>
        
        <div class="сheck">
            <input class="сheckbox" type="checkbox" id="agree" name="agree" <?php if ($values['agree']== "1") {print 'checked';}?>>
            <label for="agree">С контрактом ознакомлен(a)</label>
        </div>

        <div>
            <button  type="submit">Отправить</button>
        </div>

    </form>

    <?php
if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])){
    echo '
        <form class="form2" action="" method="POST" >
            <button name="exit" value="true" type="submit">Выйти</button>
        </form>
    ';
}
?>

</div>

</body>
</html>