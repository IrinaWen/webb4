<?php
require 'config.php';
$values = getFormData();
$errors = getFormErrors();
clearFormCookies();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Анкета сотрудника</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h1>Анкета сотрудника</h1>

        <?php if (!empty($_GET['save'])): ?>
            <div class="success">Данные успешно сохранены!</div>
        <?php endif; ?>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="error general-error"><?= $errors['general'] ?></div>
        <?php endif; ?>

        <form method="POST" action="submit.php">
            <div class="form-group <?= isset($errors['fio']) ? 'invalid' : '' ?>">
                <label>ФИО:</label>
                <input type="text" name="fio" 
                       value="<?= htmlspecialchars($values['fio'] ?? '') ?>">
                <?php if (isset($errors['fio'])): ?>
                    <div class="error-text"><?= $errors['fio'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['phone']) ? 'invalid' : '' ?>">
                <label>Телефон:</label>
                <input type="tel" name="phone" 
                       value="<?= htmlspecialchars($values['phone'] ?? '') ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="error-text"><?= $errors['phone'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['email']) ? 'invalid' : '' ?>">
                <label>Email:</label>
                <input type="email" name="email" 
                       value="<?= htmlspecialchars($values['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-text"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['birthdate']) ? 'invalid' : '' ?>">
                <label>Дата рождения:</label>
                <input type="date" name="birthdate" 
                       value="<?= htmlspecialchars($values['birthdate'] ?? '') ?>">
                <?php if (isset($errors['birthdate'])): ?>
                    <div class="error-text"><?= $errors['birthdate'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['gender']) ? 'invalid' : '' ?>">
                <label>Пол:</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="gender" value="Мужской" 
                            <?= ($values['gender'] ?? '') == 'Мужской' ? 'checked' : '' ?>> Мужской
                    </label>
                    <label>
                        <input type="radio" name="gender" value="Женский" 
                            <?= ($values['gender'] ?? '') == 'Женский' ? 'checked' : '' ?>> Женский
                    </label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <div class="error-text"><?= $errors['gender'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['languages']) ? 'invalid' : '' ?>">
                <label>Любимые языки:</label>
                <select name="languages[]" multiple size="6">
                    <?php 
                    $allLangs = ['Pascal','C','C++','JavaScript','PHP','Python','Java','Haskell','Clojure','Prolog','Scala','Go'];
                    foreach ($allLangs as $key=>$lang): 
                    ?>
                        <option value="<?= $key+1 ?>" 
                            <?= in_array($key+1, $values['languages'] ?? []) ? 'selected' : '' ?>>
                            <?= $lang ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['languages'])): ?>
                    <div class="error-text"><?= $errors['languages'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['biography']) ? 'invalid' : '' ?>">
                <label>Биография:</label>
                <textarea name="biography"><?= htmlspecialchars($values['biography'] ?? '') ?></textarea>
                <?php if (isset($errors['biography'])): ?>
                    <div class="error-text"><?= $errors['biography'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['contract_accepted']) ? 'invalid' : '' ?>">
                <label class="checkbox">
                    <input type="checkbox" name="contract_accepted" 
                        <?= isset($values['contract_accepted']) ? 'checked' : '' ?>> 
                    Согласие с условиями
                </label>
                <?php if (isset($errors['contract_accepted'])): ?>
                    <div class="error-text"><?= $errors['contract_accepted'] ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">Сохранить</button>
        </form>
    </div>
</body>
</html>
