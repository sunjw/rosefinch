var Tabs = {
	prefix :"#phpfmDoc",
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
		
		if(displayed == false) {
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
			if (i > 0) {
				nav.append("&nbsp;|&nbsp;");
			}

			// IE hacks: href is different in old version of IE, so use title
			nav.append($("<a/>").attr("href", "#" + name).attr("title", name).text(
					title).click( function() {
				Tabs.display($(this).attr("title"), true);
			}));
		}
	},

init : function() {
		Tabs.contents = new Array();
		Tabs.initNav();

		Tabs.initContents();
		
		var url = window.location.href;
		var curTitle = url.split("#")[1];
		
		Tabs.display(curTitle, true);
	}
}

$(Tabs.init); // 运行准备函数
