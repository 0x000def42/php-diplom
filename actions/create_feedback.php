<?
  class CreateFeedback {
    function call($mysqli){

      // Setup post data
      $postData = file_get_contents('php://input');
      $data = json_decode($postData, true);

      // Extract parameters
      $message = $data["message"] ?? "";
      $secret_code = $data["secret_code"] ?? "";

      // Check parameters
      if($message === "" || $secret_code === "" ){
        echo json_encode(["error" => "message or secret_code is empty"]);
        exit;
      }

      /*
        На вход мы получаем параметры secret_code и message, нам следует найти id консультации
        с соответствующим secret_code.
      */
      // Find consultation with concrete secret code
      $sql = "SELECT id from consultations where secret_code = '$secret_code'"; // Form sql query
      $res = $mysqli->query($sql); // Fetch sql query
      if ($mysqli->errno) { // Check on error
        echo json_encode(["error" => $mysqli->error]); // Render error
        exit;
      }
      $row = $res->fetch_assoc(); // Fetch row
      if(!isset($row)){ // Check if row exists
        echo json_encode(["error" => "Consultation with this secret code not found"]); //Render error
        exit;
      }
      // Устанавливаем id консультации
      $consultation_id = $row['id']; // Set consultation id variable

      // Insert feedback to db
      $sql = "INSERT INTO feedbacks (message, consultation_id) values ('$message', '$consultation_id');";

      $mysqli->query($sql);
      if ($mysqli->errno) {
        echo json_encode(["error" => $mysqli->error]);
        exit;
      }

      echo json_encode(["ok" => "ok"]);
  }
}