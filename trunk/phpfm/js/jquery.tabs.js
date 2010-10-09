/**
 * jQuery Tabs
 * License: GPL 2.0
 * Author: Sun Junwen
 * Version: 1.2
 */
var Tabs = {
	prefix :"#phpfmDoc",
	splitor :"&nbsp;|&nbsp;",
	generateNav :false,
	navs :null,
	contents :null,
	browser :null,

	display : function(name, isAni) {
		var length = this.contents.length;
		var displayed = false;
		for ( var i = 0; i < length; i++) {
			var id = this.prefix + this.contents[i];
			var jqObj = $(id);
			if (name != this.contents[i]) {
				jqObj.css("display", "none");
			} else {
				if (isAni)
					jqObj.fadeIn();
				else
					jqObj.css("display", "block");

				displayed = true;
			}
		}

		if (displayed) {
			for (navName in this.navs) {
				this.navs[navName].removeClass("selected");
			}

			this.navs[name].addClass("selected");
		} else {
			Tabs.display(this.contents[0], isAni);
		}
	},

	initContents : function() {
		Tabs.display(this.contents[0], false);
	},

	initNav : function() {
		var nav = $(this.prefix + "Nav"); // 导航部分
		var navHTML = "";
		var doc = $(this.prefix); // 主体部分
		var docContents = doc.children();
		var length = docContents.length;
		for ( var i = 0; i < length; i++) {
			var docContent = $(docContents[i]);
			var href = $(docContent.children().get(0));
			var name = href.attr("name");
			var title = href.attr("title");
			this.contents.push(name);
			if(Tabs.generateNav) {
				if (i > 0) {
					nav.append(this.splitor);
				}

				// IE hacks: href is different in old version of IE, so use title
				var link = $("<a/>").attr("href", "#" + name).attr("title", name).text(
						title);
				this.navs[name] = $(link);
				nav.append(link);
			}
		}
	},

	initClick : function() {
		var navs = $(this.prefix + "Nav > a");
		var length = navs.length;
		for ( var i = 0; i < length; i++) {
			var nav = $(navs[i]);
			nav.click( function() {
				Tabs.display($(this).attr("title"), true);
			});
			this.navs[nav.attr("title")] = nav;
		}
	},

	init : function() {
		Tabs.contents = new Array();
		Tabs.navs = new Array();
		
		Tabs.initNav();
		Tabs.initClick();
		Tabs.initContents();

		var url = window.location.href;
		var curTitle = url.split("#")[1];

		Tabs.display(curTitle, true);
	}
}

$(Tabs.init); // 运行准备函数
