<?php
// include_once 'basiclib.php';
if (!isset($_GET['id'])){
  echo json_encode((Object)Array());
  exit;
}
$dir = $_GET['id'];
if (file_exists($dir)){
  chdir($dir);
  include 'index.php';
}
?>
