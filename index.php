<?php
  session_start();
  require_once('config.inc.php');
  if empty($_SESSION['episodes']){
    require_once('episodic.php');
    $episodic=new Episodic($config['episodic']);
    $_SESSION['episodes']=$episodic->episodes;
  }

  include('header.php');
  include('body.php');
  include('footer.php');

?>
