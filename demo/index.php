<?php

// This is a page to try the decoder through a web browser

require_once dirname(__FILE__) . '/../src/TafDecoder.inc.php';
include('util.php');
use TafDecoder\TafDecoder;
use utilphp\util;

if(isset($_GET['taf'])) {
	$raw_taf = htmlspecialchars(trim($_GET['taf']));
} else {
	$raw_taf = '';
}

$decoder = new TafDecoder();
$d = $decoder->parse($raw_taf);

?>

<!DOCTYPE html>
<html>
    <head>
        <!-- Bootstrap over CDN -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
          
          <div class="header">
            <ul class="nav nav-pills pull-right">
                <li class="active">Live demo</li>
            </ul>
            <h3 class="text-muted">php-taf-decoder</h3>
            <br>
          </div>

          <div class="jumbotron">
            <h2>Decode any raw TAF:</h2>
            <br>
            
            <!-- taf form -->
            <form class="form-inline" action="index.php" method="get">
              <div class="form-group" >
                <div class="input-group">
                  <div class="input-group-addon input-lg">TAF</div>
                  <input type="text" name="taf" class="form-control input-lg" style="width:600px" value="<?php echo($raw_taf);?>">
                </div>
                <input type="submit" class="btn btn-primary btn-lg" value="Decode">
              </div>
            </form>

            <?php if(strlen($raw_taf) > 0) { ?>
                <br>
                <div style="text-align:center">
                    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                </div>
                <br>
                <?php if ($d->isValid()){ ?>
                     <div class="alert alert-success">
                        <b>Valid format</b>
                    </div>
                <?php }else{ ?>
                   <div class="alert alert-danger">
                        <b>Invalid format:</b><br>
                        <ul>
                        <?php
                           foreach($d->getDecodingExceptions() as $e){
                               echo('<li>'.$e->getMessage());
                               echo(', on chunk "'.$e->getChunk().'"</li>');
                           }
                           $d->resetDecodingExceptions();
                        ?>
                        </ul>
                    </div>
                <?php } ?>
                <div><?php
                    $raw_dump = util::var_dump($d,true,2);

                    $to_delete=array(
                        'private:TafDecoder\\Entity\\DecodedTaf:',
                        'private:TafDecoder\\Entity\\',
                        'TafDecoder\\Entity\\',
                        'Value:'
                    );
                    $clean_dump = str_replace($to_delete,'',$raw_dump);
                    echo $clean_dump;
                ?></div>
            </div>
            <?php } else { ?>
            </div>
                <br>
                <div class="alert alert-info">
                Need inspiration ? What about:
                <ul>
                    <li><a href="./index.php?taf=TAF+TAF+LIRU+032244Z+0318%2F0406+26026G42KT+9000+BKN020">TAF TAF LIRU 032244Z 0318/0406 26026G42KT 9000 BKN020</a></li>
                    <li><a href="./index.php?taf=TAF+KJFK+080609Z+0610%2F0812+03017G28KT+6+1%2F4SM+-SHDZRA+SCT005+BKN025CB+BKN250">TAF KJFK 080609Z 0610/0812 03017G28KT 6 1/4SM -SHDZRA SCT005 BKN025CB BKN250</a></li>
                    <li><a href="./index.php?taf=TAF+KJFK+080609Z+0610%2F0812+03017G28KT+P6SM+VCFGRA+BKN020+OVC080">TAF KJFK 080609Z 0610/0812 03017G28KT P6SM VCFGRA BKN020 OVC080</a></li>
                    <li><a href="./index.php?taf=TAF++++++++AMD+LFBO+080527Z+0806%2F0912+19007KT+CAVOK++++++++BECMG+0810%2F0812+27010KT++++++++BECMG+0818%2F0820+VRB03KT++++++++BECMG+0900%2F0902+16008KT++++++++TEMPO+0903%2F0909+4000+-RA+BKN025">TAF <br>&nbsp;&nbsp;&nbsp;&nbsp;AMD LFBO 080527Z 0806/0912 19007KT CAVOK <br>&nbsp;&nbsp;&nbsp;&nbsp;BECMG 0810/0812 27010KT <br>&nbsp;&nbsp;&nbsp;&nbsp;BECMG 0818/0820 VRB03KT <br>&nbsp;&nbsp;&nbsp;&nbsp;BECMG 0900/0902 16008KT</a></li>
                    <li><a href="./index.php?taf=TAF+TAF+LIRU+032244Z+0318%2F0206+CNL">TAF LIRU 032244Z 0318/0206 CNL</a> &lt;- this one has errors on purpose!</li>
                </ul>
                </div>
            <?php } ?>
        </div>

    
    </body>
</html>


