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

function initClick() {
	var navs = $(prefix + "Nav > a");
	var length = navs.length;
	for ( var i = 0; i < length; i++) {
		var nav = $(navs[i]);
		nav.click( function() {
			display($(this).attr("href").substring(1), true);
		});
	}
}

function initNav() {
	var nav = $(prefix + "Nav"); // 导航部分
	var navHTML = "";
	var doc = $(prefix); // 主体部分
	var docContents = doc.children();
	var length = docContents.length;
	for ( var i = 0; i < length; i++) {
		var docContent = $(docContents[i]);
		var href = docContent.children().get(0);
		var name = href.name;
		var title = href.title;
		contents.push(name);
		if (i > 0) {
			navHTML += ("&nbsp;|&nbsp;");
		}
		navHTML += ("<a href=\"#" + name + "\">" + title + "</a>");
	}
	nav.html(navHTML);
	
	if(browser) {
		initClick();
	}
}

function init() {
	if($.browser.msie) {
		browser = ($.browser.version >= 8) ? true : false;
	} else {
		browser = true ;
	}
	contents = new Array();
	initNav();
	
	if(browser) {
		initContents();
	}

}

$(window).load(init); // 运行准备函数
