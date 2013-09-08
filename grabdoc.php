<?php

$docDomain = "http://tor.sourcearchive.com";
$docPath = "/documentation/";
$indexFile ="/structor__options__t.html";
$docDir = "docs/";
$versionsFile = "versions.txt";

$versions = getVersionsList();

if(!isset($_GET['torversion'])) {

  $options = '';
  foreach($versions as $pos => $version) {
    $options .="<option value='$version'>$version</option>\n";
  }
  ?><!doctype html>
<html>
<head>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" >
  $(document).ready(function() {
    $("#docpicker").submit(function() {
      $("#console").html($("#loading").html());
      if($("#torversion").val()=="") return false;
      $.get('?'+$(this).serialize(), function(data) {
        $("#console").html(data);
      });
      return false;
    });
  });
</script>
<style type="text/css">
  body, h3, p { font-family:arial; }
  #docpicker { font-family:arial;font-size:0.75em;padding:0;margin:0; }
  #console { font-size:0.61em;width:500px;height:300px;overflow:auto; }
  .container {
    padding:1em 2em;
    width:500px;
    position:absolute;
    left:50%;
    margin-left:-250px;
    background-color: #EEEEEE;
    border-color: transparent;
    border-radius: 10px 10px 10px 10px;
    box-shadow: 0 5px 5px black, 0 5px 5px black, 0 5px 5px black, 0 5px 5px black;
    margin-top: 1.5em; }
  .container p { font-size:0.83em; }
  body {
    background-color: white;
    background-repeat:no-repeat;
    color:black;
    /* Safari 4-5, Chrome 1-9 */
    background: -webkit-gradient(radial, center center, 0, center center, 460, from(#fff), to(#222));
    /* Safari 5.1, Chrome 10+ */
    background: -webkit-radial-gradient(circle, #222, #fff);
    /* Firefox 3.6+ */
    background: -moz-radial-gradient(circle, #abc, #def);
    /* IE 10 */
    background: -ms-radial-gradient(circle, #222, #fff);
    /* Opera 11.10+ */
    background: -o-radial-gradient(circle, #222, #fff);
  }
#underlay {
  position:absolute;
  opacity:0.6;
  width:250xp;
  height:250px;
}
#underlay img {
  border-radius: 15px;
}
.loadingImg {
  width:100%;
  height:auto;
  text-align:center;
}
.loadingImg img {
  margin-top:30%;
}
</style>
</head>
<body>
  <div id="underlay">
    <img src="/img/tator.png" width="100" />
  </div>
  <div id="loading" style="display:none">
    <div class="loadingImg"><img src="img/loading.gif" /></div>
  </div>
  <div class="container">
    <h3>TorDoc option/names descriptions updater</h3>
    <p>
      This form updates /doc/ folder by retrieving TOR version numbers, and by grabbing option/names descriptions from the related online documentation.<br />
      Descriptions are used to give better user experience in the getInfo() utility (e.g tooltips in <a href="getinfo.php">getinfo.php</a>).
    </p>
    <form id="docpicker">
	Available versions :
	<select id="torversion" name="torversion"><option value="">Pick a version</option><?php echo $options;?></select><br>
        Force update :
        <input type=checkbox name=forceupdate value=1>
        <input type='submit' value="go!" />
    </form>
    <div id=console></div>
  </div>
</body>
</html><?php
  exit;
} else {
  if(!in_array($_GET['torversion'], $versions)) {
    die("bad version name");
  } else {
    $torversion = $_GET['torversion'];
  }
}


// $torversion = '0.2.2.33';

$optionsDoc = file_get_contents($docDomain.$docPath.$torversion.$indexFile);

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->loadHtml($optionsDoc);
$tds = $dom->getElementsByTagName('td');

$overwrite = isset($_GET['forceupdate']) ? (bool)$_GET['forceupdate']: false; // overwrite existing docs

$definitions = array();

foreach($tds as $td) {
  if($td->getAttribute("class")!="memItemRight") continue;
  $a = $td->getElementsBytagName("a");
  if(count($a)>0) {
    foreach($a as $link) {
      if($link->nodeValue=='' || $link->nodeValue=='tor') continue;
      $url = 'http://tor.sourcearchive.com/documentation/'.$torversion.'/'.$link->getAttribute("href");
      $name = trim($link->nodeValue);
      if(file_exists($docDir.$name.'.txt') && !$overwrite) {
        //echo "<font color=orange>[cached] <b>$name</b></font><br>";
        continue;
      }
      $docContent = file_get_contents($url);
      echo "[".strlen($docContent)."]";
//      die($docContent);
      $dom2 = new DOMDocument();
      $dom2->preserveWhiteSpace = false;
      $dom2->loadHtml($docContent);
      $divs = $dom2->getElementsByTagName('div');
      echo "<a href='$url'>$name</a> : ";
      foreach($divs as $div) {
        if($div->getAttribute("class")=="memdoc") {
          $p = $div->getElementsByTagName("p");
          foreach($p as $text) {
            if($text->nodeValue!=''){
              file_put_contents($docDir.$name.'.txt', $text->nodeValue);
              echo "<font color=green>[+] ".$text->nodeValue.'</font>';
            } else {
              echo "[!] empty description, skipping file creation";
            }
            break;
          }
        }
      }
      echo '<br>';
    }
  }
}

//print_r($definitions);

echo "<br />Finished";

function getVersionsList() {
  global $docDomain, $docDir, $versionsFile;
  $versions = array();

  if(file_exists($docDir.$versionsFile)) {
    $versions = explode("\n", file_get_contents($docDir.$versionsFile));
  } else {
    $txtversions = '';
    $dom = new DomDocument();
    $dom->preserveWhiteSpace = false;
    $dom->loadHtml(file_get_contents($docDomain.'/'));
    $links = $dom->getElementsByTagname("a");
    foreach($links as $link) {
      $href = $link->getAttribute("href");
      if(eregi("documentation", $href)) {
        $urlparts = explode("/", $href);
        $version = $urlparts[4];
        $versions[] = $version;
        $txtversions .=$version."\n";
      }
    }
    file_put_contents($docDir.$versionsFile, $txtversions); 
  }
  return $versions;
}
