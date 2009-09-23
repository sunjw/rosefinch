var prefix = "#phpfmHelp";
var contents;

function display(name, isAni) {
	var length = contents.length;
	for ( var i = 0; i < length; i++) {
		var id = prefix + contents[i];
		var jqObj = $(id);
		if (name != contents[i]) {
			jqObj.css("display", "none");
		} else {
			if (isAni)
				jqObj.fadeIn();
			else
				jqObj.css("display", "block");
		}
	}
}

function initContents() {
	display("Readme", false);
}

function initNav() {
	var navs = $("#phpfmHelpNav > a");
	var length = navs.length;
	for ( var i = 0; i < length; i++) {
		var nav = navs[i];
		nav.onclick = function() {
			var navObj = $(this);
			var name = navObj.attr("href").substring(1);
			display(name, true);
		}
	}
}

function init() {
	contents = new Array("Readme", "Install", "About");
	initContents();
	initNav();
}

$(window).load(init); // 运行准备函数
