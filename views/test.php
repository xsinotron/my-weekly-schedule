<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="../vendor/colors.css">
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap-theme.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" ></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js" ></script>

    <style>
          .ws-schedule {float: left}
          .ws-schedule .rowheader {
    text-align: center;
    text-transform: uppercase;
}
        .ws-schedule td          {min-width: 120px;}
        .ws-schedule td.ws-item-2{min-width:  60px;width: 60px;}
        .ws-schedule td.ws-item-3{min-width:  40px;width: 40px;}
        .ws-schedule tr          {height:  56px;padding: 0px;}
        .ws-schedule tr.topheader{height:  40px;}
        .ws-schedule tr.datarow1 {height:  56px;}
        .ws-schedule tr.datarow2 {height: 112px;}
        .ws-schedule tr.datarow3 {height: 150px;}
        .ws-schedule tr.datarow4 {height: 200px;}
        .ws-schedule tr.datarow5 {height: 350px;}
        .ws-schedule tr.datarow6 {height: 420px;}
        .ws-schedule tr.datarow7 {height: 490px;}
        .ws-schedule tr.datarow8 {height: 560px;}
          .ws-schedule .ws-row-1 p {
  margin: 0;
  max-height: 20px;
  overflow: hidden;
}
      </style>
    </head>
    <body>
    <?php
    require '../vendor/mustache/src/Mustache/Autoloader.php';
    Mustache_Autoloader::register();

    if ( isset($_REQUEST["format"]) ) $format = $_REQUEST["format"];
    else
    $format = "horizontal";

    $m      = new Mustache_Engine;
    $tpl    = file_get_contents("$format.html");
    $json   = file_get_contents("../js/data-$format.json");
  //print_r(json_decode($json));
  //global $wpdb;
  //$toto = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 0.25 and scheduleid = 2" );
  //print_r($toto);

    echo $m->render($tpl, json_decode($json));
?>
    <script>
        $(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
    </script>
    </body>
</html>
