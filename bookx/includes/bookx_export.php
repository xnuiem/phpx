<?php
//This file does nothing but pass the export back as an attachement.

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=BookxBookList.txt");
header("Content-Type: text/plain");


$body = file_get_contents("../export/" . $_GET["file"]);
print($body);
exit;








?>
