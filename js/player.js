n=0;
nw=100;
imgpics=imgpics.split('|');
imglinks=imglinks.split('|');
imgtexts=imgtexts.split('|');

document.write(
'<style type="text/css">' +
	'#player{ margin: 14px auto 3px; width:200px ;height:auto}' +
	'#player img { width:200px ; height:150px;margin: 0 auto 0px;}' +
	'#player .gray{background-color:#666666;}' +
	'#player .gray a{color:white;text-decoration:none;}' +
	'#player .gray a:hover{color:white;text-decoration:none;}' +
	'#player .dblack{background-color:#000033;}' +
	'#player .dblack a{color:white;text-decoration:none;}' +
	'#player .dblack a:hover{color:white;text-decoration:none;}' +
'</style>'
);
document.write(
'<div id="player">' +
'<div id="player_img">' +
	'<a id="imglink" href="" target="_blank"><img id="imgpic" src="" border=0 style="display:none; margin:0 auto"></a>' +
'</div>' +
'<div class="c" style="height:10px"></div><div id="imgtext" style="display:none;text-align:center;"></div></div>'
);

var do_transition;
var tcount = 14;

var garTransitions = new Array();
garTransitions[0] = "progid:DXImageTransform.Microsoft.RandomDissolve()";
garTransitions[1] = "progid:DXImageTransform.Microsoft.Iris(irisStyle='star', motion='out')";
garTransitions[2] = "progid:DXImageTransform.Microsoft.Stretch(stretchStyle='push')";
garTransitions[3] = "progid:DXImageTransform.Microsoft.Stretch(stretchStyle='pop')";
garTransitions[4] = "progid:DXImageTransform.Microsoft.Fade(duration=2,overlap=0)";
garTransitions[5] = "progid:DXImageTransform.Microsoft.GradientWipe(duration=2,gradientSize=0.25,motion=forward )";
garTransitions[6] = "progid:DXImageTransform.Microsoft.Wheel(duration=2,spokes=16)";
garTransitions[7] = "progid:DXImageTransform.Microsoft.RadialWipe(duration=2,wipeStyle=CLOCK)";
garTransitions[8] = "progid:DXImageTransform.Microsoft.RandomBars(Duration=1,orientation=vertical)";
garTransitions[9] = "progid:DXImageTransform.Microsoft.Blinds(Duration=1,bands=20)";
garTransitions[10]= "progid:DXImageTransform.Microsoft.Checkerboard(Duration=1,squaresX=20,squaresY=20)";
garTransitions[11]= "progid:DXImageTransform.Microsoft.Strips(Duration=1,motion=rightdown)";
garTransitions[12]= "progid:DXImageTransform.Microsoft.Slide(Duration=1,slideStyle=push)";
garTransitions[13]= "progid:DXImageTransform.Microsoft.Spiral(Duration=1,gridSizeX=40,gridSizeY=40)";

function showimg(n){
	if(imgpics[n]){
		if (document.all){
			do_transition = Math.floor(Math.random() * tcount);
			document.all.player.style.filter=garTransitions[do_transition];
			document.all.player.filters[0].Apply();			
		}

		document.getElementById("imgpic").style.display='';
		document.getElementById("imgpic").src=imgpics[n];
		document.getElementById("imglink").href=imglinks[n];
		if(imgtexts[n]){
			document.getElementById("imgtext").innerHTML='<a href="' + imglinks[n] + '" target="_blank"  class="a3"><b>' + imgtexts[n] + '</b></a>';
			document.getElementById("imgtext").style.display = "";
		}else{
			document.getElementById("imgtext").style.display = "none";
		}
		if (document.all) {			
			document.all.player.filters[0].Play();		
		}
	}
}
function changeimg(n){
	if (n>=imgnum){
		n=0;
	}
	showimg(n);
	n++;
	setTimeout('changeimg('+n+')',3000);
}

setTimeout('changeimg('+n+')',0);