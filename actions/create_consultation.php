<?
  class CreateConsultation {
    function call($mysqli){

      // Setup post data
      $postData = file_get_contents('php://input');
      /* 
        Мы работаем с форматом application/json, нам не доступна переменная $_POST, потому что php 
        её заполняет только при работе с multipart/form-data. 
        Поэтому мы считываем данных с помощью file_get_contents, а потом десериализуем json строку в ассоциативный массив
      */
      $data = json_decode($postData, true);

      // Extract parameters
      $name = $data["name"] ?? "";
      $phone = $data["phone"] ?? "";
      $email = $data["email"] ?? "";
      $notified = 0;
      // Вытаскиваем из $data переменные $name, $phone, $mail, если их нет - оставляем переменные пустыми
      // Check parameters
      if($name === "" || $phone === "" || $email === "" ){ // Проверяем обе переменных на пустоту
        echo json_encode(["error" => "name or phone or email is empty"]); // И выводим ошибку, если одна из них оказалась пустой
        exit;
      }

      // Insert consultation to db
      $secret_code = bin2hex(openssl_random_pseudo_bytes(10)); // Формируем случайную строку
      $sql = "INSERT INTO consultations (name, phone, email, secret_code, notified) values ('$name', '$phone', '$email', '$secret_code', $notified);";
      // Формируем sql запрос для вставки в таблицу консультация строки
      $mysqli->query($sql); // Выполняем запрос
      if ($mysqli->errno) { // Если произошла ошибка
        echo json_encode(["error" => $mysqli->error]); // Рендерим ошибку
        exit; // И обрубаем запроса
      }

      echo json_encode(["ok" => 'ok']); // Если все ок, то возвращаем ок
  }
}