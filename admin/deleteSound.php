<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* handle check admin */
  if (!(int)$_GET["sound_id"] || (int)$_GET["sound_id"] <=
    0) HandleError("", 500);

  $id = (int)$_GET["sound_id"];

  $soundQuery = $database->prepare("SELECT
  *
FROM sounds WHERE id=:id");
  $soundQuery->execute(["id" => $id]);
  if ($soundQuery->rowCount() <= 0)HandleError("", 500);
  $sound = $soundQuery->fetch(PDO::FETCH_ASSOC);

  /*unlink img */
  if ($sound["via"] == "upload") unlink('../'.explode("/api", $sound["url"])[1]);

  $database->prepare("DELETE FROM sounds WHERE id=:id")->execute(["id" =>
    $id]);





}else {
  http_response_code(405);

}