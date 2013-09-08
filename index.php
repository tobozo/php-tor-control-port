<?php

require("./tor.class.php");

//session_start();

//$_SESSION['goback'] = $_SERVER['REQUEST_URI'];

//$tor = new Tor ;
//$tor->run();


?><!doctype html>
<html>
<head>
<title>Tor controlport</title>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript">


$(document).ready(function() {

  $('input[type=radio]').change(function() {
    $("#SharingResponse").hide();
    switch($(this).attr("name")) {

      case 'choice':
        if($(this).val()=='2') {
          $(".sharing_subdiv").hide();
          $("#sharing_subdiv2").show(500);
          return;
        }
        if($(this).val()=='3') {
          $(".sharing_subdiv").hide();
          $("#sharing_subdiv2").show(500);
          return;
        }
        if($(this).val()=='4') {
          $(".sharing_subdiv").hide();
          $("#sharing_subdiv2").show(500);
          return;
        }
      break;

    }; // end switch()


    $(".sharing_subdiv").hide();
  });


  $("#sharing").submit(function() {
    data = $("#sharing").serialize();
    $.get('setSharingChoice.php?'+data, function(ret) {
      $("#SharingResponse").html(ret).show();
      setTimeout(function() { $("#SharingResponse").fadeOut(1500); }, 5000);
    });
    return false;
  });

  $.getJSON('getSharingChoice.php', function(data) {
    $("#choice"+data.response.mode).click().trigger('change');
    $("#SharingResponse").html(data.response.msg).fadeIn(500);
    if(data.response.options.length) {
      options = data.response.options;
      for(i=0;i<options.length;i++) {
        if(options[i].value!='') {
          $("input[name="+options[i].name+"]").val(options[i].value);
        }
      }
    }

    if(data.response.error) {
     $("#SharingResponse").css({color:"orange"});
    } else {
     $("#SharingResponse").css({color:"darkgreen"});
    }
  });


  nonExitRelay = {
	Nickname:            {
		type:"text",
		name:"Nickname",
		value:"Unnamed"
	},
        ORPort:              {
		type:"text",
		name:"ORPort",
		value:"9001"
	},
        RelayBandwidthBurst: {
		type:"text",
		name:"RelayBandwidthBurst",
		value:"10485760"
	},
        RelayBandwidthRate:  {
		type:"text",
		name:"RelayBandwidthRate",
		value:"5242880"
	}
  }

/*
  for(thisinput in nonExitRelay) {
    $input = $("<input>");
    $input.attr("type", nonExitRelay[thisinput].type);
    $input.attr("name", nonExitRelay[thisinput].name);
    $input.attr("value", nonExitRelay[thisinput].value);
    $input.attr("id", nonExitRelay[thisinput].name);
    $input.appendTo("#dynfs");
    $("<label>").attr("for",  nonExitRelay[thisinput].name).html(" " + nonExitRelay[thisinput].name).appendTo("#dynfs");
    $("<br>").appendTo("#dynfs");
  };
*/

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

body {
    background-color: white;
    background-repeat:no-repeat;
    color:black;
    /* Safari 4-5, Chrome 1-9 */
    background: -webkit-gradient(radial, center center, 0%, center center, 100%, from(#fff), to(#222));
    /* Safari 5.1, Chrome 10+ */
    background: -webkit-radial-gradient(circle, #222, #fff);
    /* Firefox 3.6+ */
    background: -moz-radial-gradient(circle, #abc, #def);
    /* IE 10 */
    background: -ms-radial-gradient(circle, #222, #fff);
    /* Opera 11.10+ */
    background: -o-radial-gradient(circle, #222, #fff);
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#222222'); /* for IE */
background: -webkit-gradient(linear, center center, left top, from(#fff), to(#222)); /* for webkit browsers */
background: -moz-linear-gradient(center,  #fff,  #222); /* for firefox 3.6+ */ 
  }


</style>
</head>
<body>

<div id="underlay">
  <img src="/img/tator.png" width="100" />
</div>

<div class="mainContainer">

<form id="sharing" name="sharing" action="setSharingChoice.php" method="get" onsamereleClick="showSharingSubdiv();">

<fieldset><legend>Sharing</legend>
	<br />
	<input type="radio" name="choice" id="choice1" value="1" /><label for="choice1">Use Tor as client only</label><br />
	<input type="radio" name="choice" id="choice2" value="2" /><label for="choice2">Relay traffic inside the Tor network (non-exit relay)</label><br />
	<input type="radio" name="choice" id="choice3" value="3" /><label for="choice3">Exit node</label><br />
	<input type="radio" name="choice" id="choice4" value="4" /><label for="choice4">Help censored users to access Tor network</label><br />
</fieldset>

<span id="sharing_subshow">

	<fieldset id="sharing_subdiv2" class="sharing_subdiv" style="display:none">
                <legend>~Non-exit Relay</legend>
		Nickname: <input type="text" name="Nickname" value="Unnamed" /><br />
		ORPort: <input type="text" name="ORPort" value="9001" /><br />
		RelayBandwidthBurst: <input type="text" name="RelayBandwidthBurst" value="10485760" /><br />
		RelayBandwidthRate: <input type="text" name="RelayBandwidthRate" value="5242880" />
	</fieldset>


</span><br />

<input type="submit" value="Apply" />


<!-- <fieldset id="dynfs">

</fieldset> -->

</form>


<br />
<div class="SharingResponse" id="SharingResponse"></div>


</div>
</body>
</html>
