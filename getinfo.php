<?php

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", "on");

require("./tor.class.php");

$tor = new Tor ;
$tor->run();

$tor->authenticate();

$confignames = $tor->getinfo("config/names");

if(isset($_GET['set'])) {
  if(isset($confignames[$_GET['set']])) {
    if(isset($_GET[$_GET['set']])) {
      // todo :  use more elaborate filters
      switch($confignames[$_GET['set']]) {
        case 'Boolean':
          if($_GET[$_GET['set']]!='1' && $_GET[$_GET['set']]!='0') {
            die(json_encode(array('error' => 'bad data for boolean type : '.$_GET[$_GET['set']])));
          }
        break;
        case 'Filename':
        case 'RouterList':
        case 'CommaList':
        case 'LineList':
        case 'String':
          if($_GET[$_GET['set']]!=trim($_GET[$_GET['set']])) {
            die(json_encode(array('error' => 'multiline given for commalist type')));
          }
        break;
        case 'DataSize':
        case 'TimeInterval':
        case 'Integer':
          if((int)$_GET[$_GET['set']]!=$_GET[$_GET['set']]) {
            die(json_encode(array('error' => 'bad value for integer type')));
          }
        break;
        case 'Float':
          if((float)$_GET[$_GET['set']]!=$_GET[$_GET['set']]) {
            die(json_encode(array('error' => 'bad value for float type')));
          }
        break;
        case 'Port':
          if((int)$_GET[$_GET['set']]!=$_GET[$_GET['set']]) {
            die(json_encode(array('error' => 'bad value for port type')));
          }
        break;
        case 'Dependant':

        break;
        case 'Virtual':

        break;

        defaut:

      }

      $tor->setconf($_GET['set'], $_GET[$_GET['set']]);
      die(json_encode(array('response' => 'success', 'datatype' => $confignames[$_GET['set']])));
    } else {
      die(json_encode(array('error' => 'name is set but no value was sent')));
    }
  } else {
    die(json_encode(array('error' => 'name is set but does not exist in current config')));
  }
}

?>
<html>
<head>
<title>Tor controlport</title>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript">

$(document).ready(function() {

  // make 'contains' case insensitive
  jQuery.expr[':'].contains = function(a, i, m) {
    return jQuery(a).text().toUpperCase()
        .indexOf(m[3].toUpperCase()) >= 0;
  };

  $(".setButton").click(function() {
    var setName = $(this).attr("data-name");
    url = '?set='+setName+"&"+setName+"="+$("input[name="+setName+"]").val();
    $.getJSON(url, function(data) {
      if(data.response) {
        $("#form_"+setName).css({backgroundColor:"#90EE90"}).delay(500).css({backgroundColor:"transparent"}, 1500);
      }
    });
    return false;
  });

  $(".helper").each(function() { 
    if($(this).text()=='') {
      $(this).remove();
    }
  });

  $("#filter").keyup(function() {
    if($(this).val()=='') {
      $("#cancelbutton").css({opacity:"0"});
      $(".tr_all").show();
      return;
    }
    $("#cancelbutton").css({opacity:1});
    $(".tr_all").hide()
    $(".tr_all:contains('"+$(this).val()+"')").show();
  });
  $("#cancelbutton").css({opacity:"0"}).click(function() {
    $("#filter").val("");
    $("#filter").trigger("keyup");
    $("#cancelbutton").css({opacity:"0"});
  });


});

</script>
<style type="text/css">

fieldset {
	border-color:transparent;
	width:500px;
	border-radius:10px;box-shadow:0px 5px 5px black, 0px 5px 5px black,0px 5px 5px black,0px 5px 5px black;
	margin-top:1.5em;
	background-color:#eeeeee;
}
legend {
	font-family:verdana;
	font-weight:bold;
	font-size:1.13em;
	color:black;
	text-shadow: 1px 1px 2px black;
	background:white;
	border-radius:0.5em;
	padding-right:10px;
	padding-left:10px;
        line-height:0px;
        margin-top:-10px;
}

.mainContainer {
   width:600px;
   height:auto;
   left:50%;
   margin-left:-300px;
   position:absolute;
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

#sharing {
/*  background:white;*/
}

.intheline {
  width:250px;
  margin-bottom:0;
}
.setButton {
  float:right;
}


.dataname, label {
  font-family:verdana;
  font-size:0.83em;
}

tr {
  opacity:0.8;
}

tr:hover {
  opacity:1;
}
tr:hover .helper {
  display:block;
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

#filterform { padding:0;margin:0 }
#filter { width:95px; font-size:0.61em; }
#cancelbutton {
    height: 14px;
    margin-left: -16px;
    margin-top: 2px;
    position: absolute;
    width: 14px;
    cursor:pointer;
}



.helper {
  position:absolute;
  font-family:arial;
  font-size:11px;
  background:yellow url(/img/help.gif) 185px 2px no-repeat;
  color:black;
  box-shadow:1px 1px 1px black;
  border:1ps solid black;
  border-radius:3px;
  width:180px;
  height:auto;
  padding-top:0.5em;
  padding-bottom:0.5em;
  padding-left:1em;
  padding-right:1em;
  margin-right:0;
  margin-top:-1.5em;
  text-align:left;
  min-height:50px;
  left:168px;
  z-index:1000;
  -webkit-transition:display 1s linear;
  -moz-transition:display 1s linear;
  -o-transition:display 1s linear;
  transition:display 1s linear;
  display:none;

}

.qmark {
  font-family:verdana;
  font-size:0.61em;
  background:black;
  color:white;
  border-radius:6px;
  margin:2px;
  padding:1px 3px;
}

</style>
</head>
<body>
<div id="underlay">
  <img src="/img/tator.png" width="100" />
</div>
<table style="width:650px;margin-top:1.5em;">
 <tr>
    <td align="right" style="width:360px"><form id="filterform"><input name="filter" id="filter" placeholder="filter" /><img id="cancelbutton" src="/img/cancel.png" /></form></td>
    <td style="width:17px"></td>
    <td style="width:260px"></td>
 </tr>
<?php

$response = "peau de balle";

$datatypes = array();

foreach($confignames as $dataname => $datatype) {
        $datatypes[$datatype] = true;
        //if(substr($dataname, 0, 1)=="_") continue;

        if(file_exists('docs/'.$dataname.'.txt')) {
          $helper = '<b>'.$dataname.'</b><br>'.file_get_contents('docs/'.$dataname.'.txt');
          $helpericon = '<span class="qmark">?</span>'; // '<img src="/img/help.gif" /> ';
        } else {
          $helper = '';
          $helpericon = '';
        }

	?>
	<tr class="tr_all" id="tr_<?php echo $dataname;?>"  data-name="<?php echo $dataname;?>">
           <?
            $conf = $tor->getConf($dataname);
            switch(strtolower(trim($datatype))) {

              case 'boolean':

                switch($conf) {
		  case '1':
		  case 1:
			$checkedtrue = 'checked="checked"';
			$checkedfalse = '';
		  break;
		  case '0':
 		  case 0:
                        $checkedfalse = 'checked="checked"';
                        $checkedtrue = '';
		  break;
                }; // end switch(conf)
                ?>
                <td align=right><span class="dataname" data-type="<?php echo $datatype;?>"><?php echo $dataname;?></span><?php echo $helpericon;?><span class="helper"><?php echo $helper;?></span></td>
                <td><img src="/img/<?php echo $datatype;?>.jpeg" title="type=<?php echo $datatype;?>" width=16 height=16 /></td>
                <td>
                        <form id="form_<?php echo $dataname ; ?>" class="intheline" action="./getinfo.php" method="post">
                               <input id="<?php echo $dataname ; ?>_true"  type="radio" name="<?php echo $dataname ; ?>" value="1" <?php echo $checkedfalse.$checkedtrue;?>/><label for="<?php echo $dataname ; ?>_true">True</label>
                               <input id="<?php echo $dataname ; ?>_false" type="radio" name="<?php echo $dataname ; ?>" value="0" /><label for="<?php echo $dataname ; ?>_false">False</label>
                <?
              break;

              default:
                ?>
                <td align=right><span class="dataname" data-type="<?php echo $datatype;?>"><label for="<?php echo $dataname;?>"><?php echo $dataname;?></label></span><?php echo $helpericon;?><span class="helper"><?php echo $helper;?></span></td>
                <td><img src="/img/<?php echo $datatype;?>.jpeg" title="type=<?php echo $datatype;?>" width=16 height=16 /></td>
                <td>
                        <form id="form_<?php echo $dataname ; ?>" class="intheline" action="./getinfo.php" method="post">
                               <input type="text" id="<?php echo $dataname;?>"  name="<?php echo $dataname ; ?>" value="<?php echo htmlentities($conf);?>" placeholder="not set" />
                <?
            }; // end switch(datatype)
            ?>

				<input type="submit" value="Set" class="setButton" data-name="<?php echo $dataname;?>" />
			</form>
		</td>
	</tr>
	<?php

}

$tor->close();
/*
String
Boolean
CommaList
LineList
DataSize
Integer
TimeInterval
Float
Port
Filename
RouterList
Dependant
Virtual
*/           
?>
</table>
</body>
</html>
