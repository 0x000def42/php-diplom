<?
  class Notify {
    function call($mysqli){
      $sql = "SELECT *
        FROM consultations
        WHERE post_modified < DATE_SUB(CURDATE(), INTERVAL -1 WEEK) and notified = false";
      $res = $mysqli->query($sql);
      while ($row = $res->fetch_assoc()){
        $id = $row['id'];
        mail(
          $row['email'], 
          'Оставьте отзыв', 
          'Вы можете оставить отзыв перейдя по ссылке http://localhost:3000?code='.$row['secret_code']."#feedback-form"
        );

        $new_sql = "UPDATE consultations SET notified = true WHERE id = '$id'";
        $mysqli->query($new_sql);
      }
    }

  }