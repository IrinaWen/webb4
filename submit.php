<?php
require 'config.php';

// Настройки отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Логирование входящих данных
error_log("Получены данные: " . print_r($_POST, true));

$patterns = [
    'fio' => [
        'regex' => '/^[А-Яа-яЁёA-Za-z\s\-]{2,150}$/u',
        'error' => 'Только буквы, пробелы и дефисы (2-150 символов)'
    ],
    'phone' => [
        'regex' => '/^\+?[\d\s\-\(\)]{6,20}$/',
        'error' => 'Формат: +7 (999) 123-45-67'
    ],
    'email' => [
        'regex' => '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i',
        'error' => 'Некорректный email'
    ]
];

$errors = [];
$values = $_POST;

// Валидация полей по паттернам
foreach ($patterns as $field => $rule) {
    if (empty($_POST[$field])) {
        $errors[$field] = 'Обязательное поле';
    } elseif (!preg_match($rule['regex'], $_POST[$field])) {
        $errors[$field] = $rule['error'];
    }
}

// Валидация даты рождения 
    if (empty($_POST['birthdate'])) {
        $errors['birthdate'] = 'Поле даты рождения обязательно для заполнения.';
    } else {
        // Проверяем формат
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['birthdate'])) {
            $errors['birthdate'] = 'Пожалуйста, введите дату в формате ГГГГ-ММ-ДД.';
        } else {
            // Разбираем дату на компоненты
            list($year, $month, $day) = explode('-', $_POST['birthdate']);
            
            // Проверяем валидность даты
            if (!checkdate($month, $day, $year)) {
                $errors['birthdate'] = 'Некорректная дата.';
            } else {
                $birthDate = new DateTime($_POST['birthdate']);
                $today = new DateTime();
                $minDate = new DateTime('1900-01-01');
                
                if ($birthDate > $today) {
                    $errors['birthdate'] = 'Дата рождения не может быть в будущем.';
                } elseif ($birthDate < $minDate) {
                    $errors['birthdate'] = 'Дата рождения не может быть раньше 1900 года.';
                } elseif ($today->diff($birthDate)->y < 18) {
                    $errors['birthdate'] = 'Вы должны быть старше 18 лет.';
                }
            }
        }
    }


 // Валидация пола
 if (empty($_POST['gender']) || !in_array($_POST['gender'], ['Мужской', 'Женский'])) {
    $errors['gender'] = 'Пожалуйста, выберите пол.';
}

// Проверка языков программирования
if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $errors['languages'] = 'Выберите минимум 1 язык';
}


// Валидация языков программирования
if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $errors['languages'] = 'Пожалуйста, выберите хотя бы один язык программирования.';
} else {
    foreach ($_POST['languages'] as $lang) {
        if (!($lang > 0 && $lang <= 12)) {
            $errors['languages'] = 'Выбран недопустимый язык программирования.';
            break;
        }
    }
}

// Проверка биографии
if (strlen($_POST['biography'] ?? '') > 1000) {
    $errors['biography'] = 'Максимум 1000 символов';
}

// Проверка согласия
if (!isset($_POST['contract_accepted'])) {
    $errors['contract_accepted'] = 'Необходимо согласие';
}

// Если есть ошибки - возвращаем
if (!empty($errors)) {
    setFormErrors($errors);
    setFormData($values);
    header("Location: index.php");
    exit;
}

try {
    $stmt = $db->prepare("INSERT INTO apply ( fio, phone, email, birthdate, gender, biography, contract_accepted ) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birthdate'], $_POST['gender'], $_POST['biography'], isset($_POST['contract_accepted'])?1:0 ]);

    $new_id = $db->lastInsertId();

    foreach ($_POST['languages'] as $language) {
      $stmt = $db->prepare("INSERT INTO apply_pl ( apply_id, progr_lang_id ) VALUES (?, ?)");
    $stmt->execute([$new_id, $language]);
    }
      
      // Сохраняем данные в куки на 1 год
      $formData = [
          'fio' => $_POST['fio'],
          'phone' => $_POST['phone'],
          'email' => $_POST['email'],
          'birthdate' => $_POST['birthdate'],
          'gender' => $_POST['gender'],
          'biography' => $_POST['biography'],
          'languages' => $_POST['languages']
      ];
      
      setcookie('form_data', json_encode($formData), time() + 3600 * 24 * 365, '/');
      
      // Перенаправляем с флагом успешного сохранения
      header('Location: index.php?save=1');
      exit();
  } catch (PDOException $e) {
      $errors['db'] = 'Ошибка при сохранении данных: ' . $e->getMessage();
      $values = $_POST;
      setFormErrors($errors);
      setFormData($values);
      header("Location: index.php");
        exit();
  }

?>