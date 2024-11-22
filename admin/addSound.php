<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $arr = array(
    "url" => null,
    "type" => null
  );
  /*handle check admin*/
  if (!$_POST["type"] ||!in_array($_POST["type"], array("negative",
    "positive"))) handleError("", 400);

  $arr["type"] = $_POST["type"];

  if (!$_POST["url"] &&!$_FILES["sound"]) handleError("", 400);


  if ($_POST["url"] && filter_var($_POST["url"], FILTER_VALIDATE_URL)) {
    $arr["url"] = htmlspecialchars($_POST["url"]);
    $arr["via"] = "url";

  } else if ($_FILES["sound"]) {
    /*upload sound*/
    $target_dir = "../inc/static/sounds/";
    $fileType = strtolower(pathinfo($_FILES["sound"]["name"], PATHINFO_EXTENSION));

    $allowedExtensions = array('mp3', 'm4a', 'ogg', "wave");
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['sound']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($fileType, $allowedExtensions)) {
      HandleError("", 500);
    }

    /* ulpoad file*/

    $file_name = rand(10000000, 99999999)."_".time()."_".rand(1000, 9999).".".$fileType;
    $target_file = $target_dir.$file_name;
    if (!move_uploaded_file($_FILES["sound"]["tmp_name"], $target_file)) {
      HandleError("", 500);
    }
    $arr["via"] = "upload";
    $arr["url"] = $api_file_url."/inc/static/sounds/$file_name";
  }


  $query = $database->prepare("INSERT INTO sounds(url,type,via)
VALUES(:url,:type,:via)");
  $query->bindParam(":url", $arr["url"]);
  $query->bindParam(":type", $arr["type"]);
  $query->bindParam(":via", $arr["via"]);
  $query->execute();

  if ($query->rowCount() <= 0) HandleError("", 500);



}else {
  http_response_code(405);

}