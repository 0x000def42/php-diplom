<?
  /* Require actions */
  require('./actions/create_consultation.php'); // Подключаем экшен создания консультации
  require('./actions/create_feedback.php'); // Подключаем экшен создания отзыва
  require('./actions/get_feedbacks.php'); // Подключаем экшен получения отзывов
  require('./actions/notify.php');

  header('Content-Type: application/json'); // Устанавливаем заголовок `Content-Type` в значение 'application/json' (приложение отвечает в формате json)

  /* Extract method and action variables */
  $method = $_SERVER["REQUEST_METHOD"]; // Вытаскиваем из суперглобальной переменной $_SERVER ключ REQUEST_METHOD, в нашем случае, возможные варианты значений: GET, POST
  $action = $_GET["action"] ?? null; // Вытаскиваем из суперглобальной переменной $_GET ключ 'action'
  // При запросе, например, http://localhost:8080?query=superaction в переменной $action будет находиться значение superation


  /* Check if $action is empty */
  if(!isset($action) || $action === ''){ // Сначала проверяем переменную $action на null, а потом на пустое значение
    echo json_encode(['error' => "Missing query parameter 'action'"]); // Если $action пуст, то рендерим ошибку и обрываем исполнение.
    /*
      echo - добавляет строку в буфер, который будет отправлен пользователю после окончания обработки запроса
      json_encode - сериализует объект или массив в json строку
    */
    exit;
  }

  /* Handle POST ?action=create_consultation */
  if($action === 'create_consultation' && $method === 'POST'){
    $handler = new CreateConsultation(); // В переменную handler записываем экшемпляр класса, у которого позже вызовем метод 'call'
  }

  /* Handle POST ?action=create_feedback */
  if($action === 'create_feedback' && $method === 'POST'){
    $handler = new CreateFeedback();
  }

  /* Handle GET ?action=get_feedbacks */
  if($action === 'get_feedbacks' && $method === 'GET'){
    $handler = new GetFeedbacks();
  }

  if($action === 'notify' && $method === 'GET'){
    $handler = new Notify();
  }

  /* If handled not setted - render error */
  if(!isset($handler)){ // Если ни одно из выше условий не сработало - в $handler будет null, в это случае мы возвращаем ошибку
    echo json_encode(['error' => "Missing handler for action: '$action', method: '$method'"]);
    exit;
  }
  
  /* If all ok, create db connection */
  $url=parse_url(getenv("CLEARDB_DATABASE_URL"));

  if ($_SERVER['SERVER_NAME'] == "thawing-island-242342379.herokuapp.com") {
    $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
    $host = $url["host"];
    $username = $url["user"];
    $password = $url["pass"];
    $dbname = substr($url["path"], 1);
  } else {
    $host = '0.0.0.0';
    $dbname = 'diplom';
    $username = 'root';
    $password = 'root';
  }
  $mysqli = new mysqli($server, $username, $password, $db); // Используя mysqli подключаемся к базе данных
  // Первый аргумент - хост, второй - имя пользователя, третий - пароль, 4 - название базы данных
  if ($mysqli->connect_error) { // Тут мы проверяем если что-то пошло не так
    echo json_encode(['error' => "Cannot connect to database"]); // И выдаем ошибку
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error); // А так же убиваем сервер
  }

  $handler->call($mysqli); // У выше определенного $handler вызываем метод call

?>