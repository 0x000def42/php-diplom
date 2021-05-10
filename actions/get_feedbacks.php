<?
  class GetFeedbacks {
    function call($mysqli){
      $list = []; // Объявляем массив, который будем заполнять данными из бд
      $page = $_GET['page'] ?? 0;
      $offset = $page * 5;
      /*
        Элемент ответа будет содержать message, date и name.
        Из-за того, что в таблице отзывов нет имени пользователя (потому что он есть в таблице консультаций)
        мы воспользуемся join конструкцией.
        Так же ограничи количество строк 10.
        А так же выберем только по 1 отзыву для 1 консультаций с помощью group by.
      */
      $res = $mysqli->query(
        "SELECT feedbacks.*, consultations.name
        FROM feedbacks 
        LEFT JOIN consultations on feedbacks.consultation_id = consultations.id 
        GROUP BY feedbacks.consultation_id
        LIMIT 5 OFFSET $offset");
      while ($row = $res->fetch_assoc())
      // До тех пор, пока из ответа от mysql вытаскиваются строки..
      {
        // Мы в массив $list запихиваем новый ассоциативный массив, состоящий из 3 ключей
        array_push($list, [
          'id' => $row['id'],
          'message' => $row['message'],
          'date' => $row['date'],
          'name' => $row['name']
        ]);
      }
      // А потом сериализуем массив в json и отправляем клиенту
      echo json_encode($list);
    }
  }