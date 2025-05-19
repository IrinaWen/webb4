<?php
require 'config.php';
$values = getFormData();
$errors = getFormErrors();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Успешная отправка</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Данные успешно сохранены!</h1>
        
        <div class="info-block">
            <h2>Ваши данные:</h2>
            <p><strong>ФИО:</strong> <?= htmlspecialchars($values['fio'] ?? '') ?></p>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($values['phone'] ?? '') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($values['email'] ?? '') ?></p>
            <p><strong>Дата рождения:</strong> <?= $values['birthdate'] ?? '' ?></p>
            <p><strong>Пол:</strong> 
                <?= isset($values['gender']) ? ($values['gender'] == 'Мужской' ? 'Мужской' : 'Женский') : '' ?>
            </p>
            
            <h3>Выбранные языки программирования:</h3>
            <?php $allLangs = ['Pascal','C','C++','JavaScript','PHP','Python','Java','Haskell','Clojure','Prolog','Scala','Go'];?>
            <ul>
                <?php foreach ($values['languages'] as $lang): ?>
                    <li><?= htmlspecialchars($allLangs[$lang-1]) ?></li>
                <?php endforeach; ?>
            </ul>
            
            <p><strong>Биография:</strong></p>
            <div class="bio-text"><?= nl2br(htmlspecialchars($values['biography'] ?? '')) ?></div>
            
            <p><a href="index.php" class="back-link">Заполнить новую анкету</a>
        </div>
    </div>
</body>
</html>