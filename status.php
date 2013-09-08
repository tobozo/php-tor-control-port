<?php

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", "on");

require("./tor.class.php");

$tor = new Tor ;
$tor->run();

$tor->authenticate();



function getCountryMapByStatus($file="tor-status") {

  global $tor;
  $lines = explode("\n", file_get_contents('tor-status'));

  $countryMap = array();

  foreach($lines as $num =>  $line) {
    $parts = explode(" ", $line);
    if(count($parts)==9) {
      $nodeName = $parts[1];
      $nodeIp   = $parts[count($parts)-3];

      if(filter_var($nodeIp, FILTER_VALIDATE_IP)) {
        // echo $nodeName," : ",$nodeIp,"\n";
        if(preg_match("/Exit/", $lines[$num+1])
        && preg_match("/Running/", $lines[$num+1])
        && preg_match("/Fast/", $lines[$num+1])
        && preg_match("/Stable/", $lines[$num+1])
        && preg_match("/Valid/", $lines[$num+1]))	 {
          $cn = $tor->getcountry($nodeIp);
          if(!$cn) continue; // $cn = "!";
          //echo "$nodeName [$cn] : $nodeIp<br />\n";
          $countryMap[$cn][] = array('name'=>$nodeName, 'ip' => $nodeIp);
        }
      }
    }
  }
  return $countryMap;
}



function getCountryMapByBlutMagie($csv="http://torstatus.blutmagie.de/query_export.php/Tor_query_EXPORT.csv") {

  $cache_file = 'Tor_query_EXPORT.csv';
  $cache_life = '3600'; //caching time, in seconds
  $countryMap = array();

  $filemtime = @filemtime($cache_file);  // returns FALSE if file does not exist
  if (!$filemtime or (time() - $filemtime >= $cache_life)){
    define('CACHE_DATE', date('Y-m-d h:i:s', time()));
    file_put_contents($cache_file,file_get_contents($csv));
  } else {
    define('CACHE_DATE', date('Y-m-d h:i:s', $filemtime));
  }
  $a = fopen($cache_file, 'r');
  $header = false;
  while($line=fgetcsv($a)) {
    if(!$header) {
      $header = $line;
      foreach($header as $pos=>$name) {
        $header[$pos] = preg_replace("/[^a-z0-9]+/i", "", $name);
        //echo '$'.$header[$pos]." ==''\n";
      }
      continue;
    }
    foreach($line as $pos=>$val) {
      $varname = $header[$pos];
      $$varname = $val;
    }
    if(	   $RouterName !=''
//	&& $CountryCode ==''
//	&& $BandwidthKBs ==''
//	&& $UptimeDays ==''
//	&& $IPAddress ==''
//	&& $Hostname ==''
//	&& $ORPort ==''
//	&& $DirPort ==''
//	&& $FlagAuthority ==''
	&& $FlagExit =='1'
	&& $FlagFast =='1'
//	&& $FlagGuard ==''
//	&& $FlagNamed ==''
	&& $FlagStable =='1'
	&& $FlagRunning =='1'
	&& $FlagValid =='1'
//	&& $FlagV2Dir ==''
//	&& $Platform ==''
 	&& $FlagHibernating !='1'
	&& $FlagBadExit !='1'
      ) {
           $countryMap[$CountryCode][] = array('name'=>$RouterName, 'ip' => $IPAddress);
    }
  }
  return $countryMap;
}

$countryMap = getCountryMapByBlutMagie();

//print_r($countryMap);

//$countryMap = getCountryMapByStatus();

if(isset($_GET['cn'])) {
  if(preg_match("/^[a-z]{2}$/i", $_GET['cn'])) {
    if(isset($countryMap[$_GET['cn']])) {
      // lucky

/*

setconf __DisablePredictedCircuits=1      <- disable preemptively creating circuits
setconf MaxOnionsPending=0                <- maximum circuits pending
setconf newcircuitperiod=999999999        <- longer period before creating new circuit
setconf maxcircuitdirtiness=999999999     <- longer period for circuit expiration

closecircuit 2
250 OK
closecircuit 1
250 OK
getinfo circuit-status
250-circuit-status=
250 OK


*/





      die(json_encode(array('response' =>  $countryMap[$_GET['cn']])));

    } else {
      die(json_encode(array('error' => 'country map not set for '.$_GET['cn'])));
    }
  } else {
    die(json_encode(array('error' => 'invalid cn')));
  }
}


?><!doctype html>
<html>
<head>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $("#cn").change(function() {
   $("#nodes").hide().empty();
   $("#controlport").empty();
   if($(this).val()=='') return;
   $.getJSON('?cn='+$(this).val(), function(data) {
     $("<input type='checkbox' id='toggler' name='toggler' checked />").change(function() {
       $("input[class=usernode]").click();
     }).appendTo("#nodes");
     $("<label for='toggler'>Toggle all</label>").css({paddingLeft:"2em",marginLeft:"-1.5em",border:"1px solid lightgreen",fontSize:"0.75em",lineHeight:"1em"}).appendTo("#nodes");
     $(data.response).each(function() {
        this.uniqueName = this.name + (this.ip).replace(/[^a-z0-9]+/gi, '-');
        $("<div><input checked name='"+this.uniqueName+"' type=checkbox data-name='"+this.name+"' data-ip='"+this.ip+"' class='usernode' /><span class='name'>"+this.name+"</span><span class='ip'>"+this.ip+"</span></div>").appendTo("#nodes");
        $("<span id='for_"+this.uniqueName+"'>"+this.name+", </span>").appendTo("#controlport");
     });
     $("input[class=usernode]").change(function() {
       $("#for_"+$(this).attr("name")).toggle();
     });
     $("#nodes").fadeIn(200);
   });
  });
});

</script>
<style type="text/css">

span.name {
  display:inline-block;
  width:250px;
}
span.ip {
  width:250px;
  display:inline-block;
}


#nodes {
  max-height:300px;
  width:550px;
  display:inline-block;
  overflow:auto;
  margin:0;
  padding:0;
}
body {
    font-size:0.83em;
    font-family:verdana;
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
    /* css3 fix attempt */
    background: radial-gradient(center center, #fff 0%, #222 100%);
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#222222'); /* for IE */
background: -webkit-gradient(linear, center center, left top, from(#fff), to(#222)); /* for webkit browsers */
background: -moz-linear-gradient(center,  #fff,  #222); /* for firefox 3.6+ */ 
}
#underlay {
  opacity:0.6;
}
#underlay img {
  border-radius: 15px;
  position:fixed;
  left:8px;
  top:8px;

}
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

</style>
</head>
<body>
<div id="underlay">
  <img src="/img/tator.png" width="100" />
</div>
<div class="container">

Last Cache update : <?php echo CACHE_DATE; ?><br />
<form id="nodeSelector">
<select name="cn" id="cn"><option value="">Pick a country</option><?

$totalNodes = 0;

arsort($countryMap);

foreach($countryMap as $cn => $ary) {
  ?>
   <option value="<?php echo $cn; ?>" style="padding-left:25px;background:url(/img/countries.png/<?php echo strtolower($cn);?>.png) 2px center no-repeat;"><?php echo strtoupper($cn); ?> (<?php echo count($ary);?>)</option><?php 
  $totalNodes += count($ary);
}

?></select>
<?

echo "Total Nodes : $totalNodes";

$tor->close();
?>
</form>
<div id="nodes"></div>
<div id="controlport"></div>
</div>
