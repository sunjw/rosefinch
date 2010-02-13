var prefix = "#phpfmDoc";
var contents;
var browser;

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
	display(contents[0], false);
}

function initNav() {
	var nav = $(prefix + "Nav"); // 导航部分
	var navHTML = "";
	var doc = $(prefix); // 主体部分
	var docContents = doc.children();
	var length = docContents.length;
	for ( var i = 0; i < length; i++) {
		var docContent = $(docContents[i]);
		var href = $(docContent.children().get(0));
		var name = href.attr("name");
		var title = href.attr("title");
		contents.push(name);
		if (i > 0) {
			nav.append("&nbsp;|&nbsp;");
		}

		// IE hacks: href is different in old version of IE, so use title
		nav.append($("<a/>").attr("href", "#" + name).attr("title", name).text(
				title).click( function() {
			display($(this).attr("title"), true);
		}));
	}
}

function init() {
	contents = new Array();
	initNav();

	initContents();
}

$(init); // 运行准备函数
