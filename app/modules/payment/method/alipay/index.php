<?php
    require_once dirname(__FILE__).'/config.php';
    use library\UsualToolInc\UTInc;
    $posnum=$_GET["posnum"];
    if(UTInc::IsApp()):
        UTInc::GoUrl("tomb/pay.php?posnum=".$posnum,"");
    else:
        UTInc::GoUrl("topc/pay.php?posnum=".$posnum,"");
    endif;
?>