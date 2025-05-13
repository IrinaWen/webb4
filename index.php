<?php
header('Content-Type: text/html; charset=UTF-8');

// Установка времени жизни сессии (1 час)
session_set_cookie_params(3600);
session_start();

// Функция для получения значения поля.  Сначала проверяет сессию, потом куки.
function getFieldValue($fieldName) {
    // Сначала проверяем сессию
    if (isset($_SESSION['oldValues'][$fieldName])) {
        return htmlspecialchars($_SESSION['oldValues'][$fieldName]);
    }
    
    // Если в сессии нет, проверяем куки
    if (isset($_COOKIE['form_data'])) {
        $formData = json_decode($_COOKIE['form_data'], true);
        if (isset($formData[$fieldName])) {
            return htmlspecialchars($formData[$fieldName]);
        }
    }
    
    return ''; // Если нигде нет, возвращаем пустую строку
}

function getCheckboxValues($fieldName) {
  // Сначала проверяем сессию
  if (isset($_SESSION['oldValues'][$fieldName]) && is_array($_SESSION['oldValues'][$fieldName])) {
      return $_SESSION['oldValues'][$fieldName];
  }

  // Если в сессии нет, проверяем куки
  if (isset($_COOKIE['form_data'])) {
      $formData = json_decode($_COOKIE['form_data'], true);
      if (isset($formData[$fieldName]) && is_array($formData[$fieldName])) {
          return $formData[$fieldName];
      }
  }

  return []; // Если нигде нет, возвращаем пустой массив
}


// Обработка POST-запроса (отправка формы)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Массивы для хранения ошибок
    $errors = false;
    $formErrors = [];
    $fieldErrors = [];
    
    // Валидация ФИО
    if (empty($_POST['fio'])) {
        $fieldErrors['fio'] = 'Поле ФИО обязательно для заполнения.';
        $errors = true;
    } elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s-]+$/u', $_POST['fio'])) {
        $fieldErrors['fio'] = 'ФИО может содержать только буквы, пробелы и дефисы.';
        $errors = true;
    } elseif (strlen($_POST['fio']) > 150) {
        $fieldErrors['fio'] = 'ФИО не должно превышать 150 символов.';
        $errors = true;
    }
    
    // Валидация телефона
    if (empty($_POST['tel'])) {
        $fieldErrors['tel'] = 'Поле телефона обязательно для заполнения.';
        $errors = true;
    } elseif (!preg_match('/^[\d\s\-\+\(\)]+$/', $_POST['tel'])) {
        $fieldErrors['tel'] = 'Телефон должен содержать цифры, пробелы, +, - или скобки.';
        $errors = true;
    } elseif (strlen($_POST['tel']) < 6 || strlen($_POST['tel']) > 20) {
        $fieldErrors['tel'] = 'Телефон должен содержать от 6 до 20 символов.';
        $errors = true;
    }
    
    // Валидация email
    if (empty($_POST['email'])) {
        $fieldErrors['email'] = 'Поле email обязательно для заполнения.';
        $errors = true;
    } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = 'Пожалуйста, введите корректный email.';
        $errors = true;
    }
    
    // Валидация даты рождения 
if (empty($_POST['date'])) {
    $fieldErrors['date'] = 'Поле даты рождения обязательно для заполнения.';
    $errors = true;
} else {
    // Проверяем формат
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['date'])) {
        $fieldErrors['date'] = 'Пожалуйста, введите дату в формате ГГГГ-ММ-ДД.';
        $errors = true;
    } else {
        // Разбираем дату на компоненты
        list($year, $month, $day) = explode('-', $_POST['date']);
        
        // Проверяем валидность даты
        if (!checkdate($month, $day, $year)) {
            $fieldErrors['date'] = 'Некорректная дата.';
            $errors = true;
        } else {
            $birthDate = new DateTime($_POST['date']);
            $today = new DateTime();
            $minDate = new DateTime('1900-01-01');
            
            if ($birthDate > $today) {
                $fieldErrors['date'] = 'Дата рождения не может быть в будущем.';
                $errors = true;
            } elseif ($birthDate < $minDate) {
                $fieldErrors['date'] = 'Дата рождения не может быть раньше 1900 года.';
                $errors = true;
            } elseif ($today->diff($birthDate)->y < 18) {
                $fieldErrors['date'] = 'Вы должны быть старше 18 лет.';
                $errors = true;
            }
        }
    }
}
   
    
    // Валидация пола
    if (empty($_POST['gender']) || !in_array($_POST['gender'], ['Мужской', 'Женский'])) {
        $fieldErrors['gender'] = 'Пожалуйста, выберите пол.';
        $errors = true;
    }
    
    // Валидация языков программирования
    if (empty($_POST['plang']) || !is_array($_POST['plang'])) {
        $fieldErrors['plang'] = 'Пожалуйста, выберите хотя бы один язык программирования.';
        $errors = true;
    } else {
        foreach ($_POST['plang'] as $lang) {
            if (!($lang > 0 && $lang <= 12)) {
                $fieldErrors['plang'] = 'Выбран недопустимый язык программирования.';
                $errors = true;
                break;
            }
        }
    }
    
    // Валидация биографии
    if (empty($_POST['bio'])) {
        $fieldErrors['bio'] = 'Поле биографии обязательно для заполнения.';
        $errors = true;
    } elseif (strlen($_POST['bio']) > 500) {
        $fieldErrors['bio'] = 'Биография не должна превышать 500 символов.';
        $errors = true;
    }
    
    // Валидация чекбокса
    if (empty($_POST['check'])) {
        $fieldErrors['check'] = 'Необходимо подтвердить согласие с контрактом.';
        $errors = true;
    }
    
      // Если есть ошибки, сохраняем их в сессию и возвращаем на форму
      if ($errors) {
        $_SESSION['formErrors'] = $fieldErrors;
        $_SESSION['fieldErrors'] = $fieldErrors;
        $_SESSION['oldValues'] = $_POST;
        
        header('Location: index.php');
        exit();
    }
    
    $user = 'u69120'; // Заменить на ваш логин uXXXXX
    $pass = '7228987'; // Заменить на пароль
    $db = new PDO('mysql:host=localhost;dbname=u69120', $user, $pass,
      [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); // Заменить test на имя БД, совпадает с логином uXXXXX
    
    // Подготовленный запрос. Не именованные метки.
    try {
      $stmt = $db->prepare("INSERT INTO apply ( fio, phone, email, birthdate, gender, biography, contract_accepted ) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$_POST['fio'], $_POST['tel'], $_POST['email'], $_POST['date'], $_POST['gender'], $_POST['bio'], $_POST['check'] ]);
  
      $new_id = $db->lastInsertId();
  
      foreach ($_POST['plang'] as $language) {
        $stmt = $db->prepare("INSERT INTO apply_pl ( apply_id, progr_lang_id ) VALUES (?, ?)");
      $stmt->execute([$new_id, $language]);
      }
        
        // Сохраняем данные в куки на 1 год
        $formData = [
            'fio' => $_POST['fio'],
            'tel' => $_POST['tel'],
            'email' => $_POST['email'],
            'date' => $_POST['date'],
            'gender' => $_POST['gender'],
            'bio' => $_POST['bio'],
            'plang' => $_POST['plang']
        ];
        
        setcookie('form_data', json_encode($formData), time() + 3600 * 24 * 365, '/');
        
        // Перенаправляем с флагом успешного сохранения
        header('Location: index.php?save=1');
        exit();
    } catch (PDOException $e) {
        $_SESSION['formErrors'] = ['Ошибка при сохранении данных: ' . $e->getMessage()];
        $_SESSION['oldValues'] = $_POST;
        header('Location: index.php');
        exit();
    }
}

// Получение старых значений (сначала из сессии, потом из куки)
$fioValue = getFieldValue('fio');
$telValue = getFieldValue('tel');
$emailValue = getFieldValue('email');
$dateValue = getFieldValue('date');
$genderValue = getFieldValue('gender');
$bioValue = getFieldValue('bio');
$plangValues = getCheckboxValues('plang');

$fieldErrors = (!empty($_SESSION['fieldErrors']))?$_SESSION['fieldErrors']:[];
$formErrors = (!empty($_SESSION['formErrors']))?$_SESSION['formErrors']:[];

// Очистка сообщений об ошибках после их отображения
if (!empty($_SESSION['formErrors'])) {
    unset($_SESSION['formErrors']);
}
if (!empty($_SESSION['fieldErrors'])) {
    unset($_SESSION['fieldErrors']);
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма регистрации</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background: #f0f8ff; 
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .form-container {
        background: #ffffff;
        padding: 30px; 
        border-radius: 10px; 
        width: 95%; 
        max-width: 600px; 
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); 
        overflow: hidden; 
    }

    .form-container h2 {
        color: #333333; 
        margin-bottom: 25px; 
        text-align: center; 
    }

    .form-row {
        margin-bottom: 25px; 
    }

    .form-container input,
    .form-container textarea,
    .form-container select {
        width: 100%;
        padding: 12px; 
        margin-top: 8px; 
        border: 1px solid #cccccc; 
        border-radius: 6px; 
        box-sizing: border-box; 
    }

    .form-container button {
        width: 100%;
        padding: 14px; 
        background: #007bff; 
        color: #ffffff; 
        border: none;
        border-radius: 6px; 
        cursor: pointer;
        margin-top: 25px; 
        transition: background-color 0.3s ease; 
    }

    .form-container button:hover {
        background: #0056b3; 
    }

    .gender-container {
        display: flex;
        margin-top: 8px; 
    }

    .form-check {
        margin-right: 20px;
        display: flex;
        align-items: center;
    }

    .form-group {
        display: flex;
        align-items: center;
        margin-top: 15px; 
    }

    .form-group label {
        margin-left: 8px; 
        margin-bottom: 0;
        color: #555555; 
    }

    .form-check-input {
        margin-right: 10px;
    }

    .error {
        color: #dc3545; 
        font-size: 0.9em;
        margin-top: 8px; 
    }

    .error-field {
        border-color: #dc3545 !important; 
    }

    .form-messages {
        margin-bottom: 25px; 
        padding: 15px;
        border: 1px solid #f8d7da;
        background-color: #fef2f2; 
        color: #721c24;
        border-radius: 6px; 
    }

    .form-container label {
        color: #555555; 
        display: block; 
        margin-bottom: 5px;
    }
</style>
</head>
<body>
    <div class="form-container">
        <h2>Регистрация</h2>
        
        <?php if (!empty($formErrors)): ?>
        <div class="form-messages">
            <p>Пожалуйста, исправьте ошибки в форме:</p>
            <ul>
                <?php foreach ($formErrors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($_GET['save'])): ?>
            <div style="color: green; margin-bottom: 10px;">Данные успешно сохранены!</div>
        <?php endif; ?>
        
        <form id="registrationForm" action="index.php" method="POST">
            <div class="form-row">
                <label for="fio">ФИО:</label>
                <input type="text" name="fio" class="form-control <?php echo !empty($fieldErrors['fio']) ? 'error-field' : ''; ?>" 
                       id="fio" placeholder="Иванов Иван Иванович" required
                       value="<?php echo getFieldValue('fio'); ?>">
                <?php if (!empty($fieldErrors['fio'])): ?>
                <div class="error"><?php echo htmlspecialchars($fieldErrors['fio']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <label for="tel">Номер телефона:</label>
                <input type="tel" name="tel" class="form-control <?php echo !empty($fieldErrors['tel']) ? 'error-field' : ''; ?>" 
                       id="tel" placeholder="Введите ваш номер" required
                       value="<?php echo getFieldValue('tel'); ?>">
                <?php if (!empty($fieldErrors['tel'])): ?>
                <div class="error"><?php echo htmlspecialchars($fieldErrors['tel']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <label for="email">Email:</label>
                <input type="email" name="email" class="form-control <?php echo !empty($fieldErrors['email']) ? 'error-field' : ''; ?>" 
                       id="email" placeholder="Введите вашу почту" required
                       value="<?php echo getFieldValue('email'); ?>">
                <?php if (!empty($fieldErrors['email'])): ?>
                <div class="error"><?php echo htmlspecialchars($fieldErrors['email']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <label for="date">Дата рождения:</label>
                <input type="date" name="date" class="form-control <?php echo !empty($fieldErrors['date']) ? 'error-field' : ''; ?>" 
                       id="date" required
                       value="<?php echo getFieldValue('date'); ?>">
                <?php if (!empty($fieldErrors['date'])): ?>
                <div class="error"><?php echo htmlspecialchars($fieldErrors['date']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <label>Пол:</label>
                <div class="gender-container">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" id="radio-male" value="Мужской" required
                            <?php echo (getFieldValue('gender') == 'Мужской') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="radio-male">Мужской</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" id="radio-female" value="Женский" required
                            <?php echo (getFieldValue('gender') == 'Женский') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="radio-female">Женский</label>
                    </div>
                </div>
                <?php if (!empty($fieldErrors['gender'])): ?>
                <div class="error"><?php echo htmlspecialchars($fieldErrors['gender']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <label for="plang">Любимый язык программирования:</label>
                <select class="form-control <?php echo !empty($fieldErrors['plang']) ? 'error-field' : ''; ?>" 
                        name="plang[]" id="plang" multiple required>
                    <?php
                    $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python','Java','Haskell', 'Clojure', 'Prolog', 'Scala','Go'];
                    $plangValues = getCheckboxValues('plang');
                    foreach ($languages as $key=>$lang) {
                        $selected = (in_array($key+1, $plangValues)) ? 'selected' : '';
                        echo "<option value=\"".($key+1)."\" $selected>$lang</option>";
                    }
                    ?>
                </select>
                <?php if (!empty($fieldErrors['plang'])): ?>
                <div class="error"><?php echo htmlspecialchars($fieldErrors['plang']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <label for="bio">Биография:</label>
                <textarea class="form-control <?php echo !empty($fieldErrors['bio']) ? 'error-field' : ''; ?>" 
                          name="bio" id="bio" rows="3" placeholder="Расскажите о себе" required><?php 
                    echo getFieldValue('bio'); 
                ?></textarea>
                <?php if (!empty($fieldErrors['bio'])): ?>
                <div class="error"><?php echo htmlspecialchars($fieldErrors['bio']); ?></div>
                <?php endif; ?>
            </div>

            <div>
                <input type="checkbox" class="form-check-input" name="check" id="check" value="1"
                    <?php echo (!empty($plangValues)) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="check">С контрактом ознакомлен(а)</label>
                <?php if (!empty($fieldErrors['check'])): ?>
                <div class="error"><?php echo htmlspecialchars($fieldErrors['check']); ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>
