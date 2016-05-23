var currentX = 1, currentY = 1, ar = [], id;

$(document).ready(function(){
	ColorChange(!1);
	$("body").keydown(function(e){
		ColorChange(!0);
		switch (e.which){
			case 37: if(currentX - 1 > 0) currentX--; break;
			case 38: if(currentY - 1 > 0) currentY--; break;
			case 39: if(currentX + 1 < 10) currentX++; break;
			case 40: if(currentY + 1 < 10) currentY++; break;
			case 46: $(id).html(""); break;
			case 13: Step(); break;
		}
		ColorChange(!1);
		var str = e.which - 48; if (str>0&&str<10) $(id).html(str);
		
	});
	$("td").click(function(){
		ColorChange(!0);
		var id = this.id.toString();
		currentX = parseInt(id.charAt(1));
		currentY = parseInt(id.charAt(0));
		ColorChange(!1);
	});
});

function Clean(){ $("td").html("").attr('title', ""); }

function LoadTZ(){
	var ar = {11:7,12:5,16:9,19:4,22:1,26:8,27:9,33:6,35:4,39:1,41:8,42:4,44:2,46:7,64:4,66:1,68:2,69:8,71:4,75:9,77:6,83:5,84:1,88:3,91:1,94:8,98:4,99:5}
	Clean(); for(var key in ar) $("#"+key.toString()).html(ar[key]);
}

function Step(){//ar[this.id] = this.innerHTML;
	var m = ""; $("td").each(function() { m += this.id + "=" + this.innerHTML + "&" });
	$.ajax({
		type: 'POST',
		url: 'sudoku.php',
		data: m,
		error:function(e){ alert("Ошибка передачи данных!"); },
		success: function(e){
			e = $.parseJSON(e);
			switch(e["f"]){
				case 1: alert(e["e"]); break;
				case 0:
					$("td").attr('title', "");
					for(var key in e["ar"]) {
						$("#"+key).html(e["ar"][key]); 
						var m = e["arCandidate"][key], r = "";
						if(m){ for(var i in m) r += i+", "; $("#"+key).attr('title', r); }
					}
				break;
			}
		}
	});
}

function ColorChange(f){
	id = "#" + currentY.toString() + currentX.toString();
	if(f) var c="white"; else var c="#ccc";
	$(id).css("background-color",c);
}

