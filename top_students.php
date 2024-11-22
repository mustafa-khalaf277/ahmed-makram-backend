<?php
include("./inc/connect.php");
include("./inc/functions.php");





if ($_SERVER["REQUEST_METHOD"] === "GET") {
  if ((int)$_GET["id"] === 0) HandleError("", 404);
  /*var*/
  $id = (int)$_GET["id"];
  $examQuery = $database->prepare("
          SELECT
            exams.title,
            exams.marks,
            exams.questions_count,
            courses.title as course
          FROM
            exams,
            courses
          WHERE
            exams.id = $id
  ");
  $examQuery->execute();
  if ($examQuery->rowCount() <= 0) HandleError("", 404);
  $exam = $examQuery->fetch(PDO::FETCH_ASSOC);



  $studentsQuary = $database->prepare("
                SELECT
                  users.name,
                  exam_results.exam_marks
                FROM
                  users,
                  exams,
                  exam_results
                WHERE
                  exams.id = :exam_id AND
                  exams.show_top_students =1 AND
                  users.id = exam_results.user_id
                LIMIT 10
    ");
  $studentsQuary->bindParam(":exam_id", $id);
  $studentsQuary->execute();
  $students = $studentsQuary->fetchAll(PDO::FETCH_ASSOC);
  $exam["students"] = $students;
  print_r(json_encode(Array(
    "status" => "success",
    "data" => $exam
  )));
  die();

} else {
  http_response_code(405);

}