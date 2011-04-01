/**
 * jQuery Folding
 * License: GPL 2.0
 * Author: Sun Junwen
 * Version: 1.3.1
 */
var jqFolding = {
	listSelector : ".foldingList", // 包含 trigger 和 container 块的选择器
	triggerSelector : ".trigger", // trigger 选择器
	containerSelector : ".foldingContainer", // container 选择器
	initState : "show", // 初始状态，show/hide
	
	classShown : "shown",
	classHidden : "hidden",
	classHeaderShown : "headerShown",
	classHeaderHidden : "headerHidden",
	
	/**
	 * 阻止事件浮升
	 */
	stopBubble : function (e) {
		var e = e ? e : window.event;
		if (window.event) { // IE
			e.cancelBubble = true;
		} else { // FF
			// e.preventDefault();
			e.stopPropagation();
		}
	},
	
	/**
	 * 设置参数
	 */
	setup : function (values) {
		jqFolding.listSelector = values.listSelector || jqFolding.listSelector;
		jqFolding.triggerSelector = values.triggerSelector || jqFolding.triggerSelector;
		jqFolding.containerSelector = values.containerSelector || jqFolding.containerSelector;
		jqFolding.initState = values.initState || jqFolding.initState;
	},
	
	/**
	 * 开关
	 */
	toggle : function () {
		//alert("!");
		var thisNode = $(this);
		var parent = $(this.parentNode);
		var list = parent.find(jqFolding.containerSelector);
		if (list.is(":visible")) {
			// 关闭
			//list.css("display", "none");
			list.slideUp("fast", function () {
					list.addClass(jqFolding.classHidden);
					list.removeClass(jqFolding.classShown);
					thisNode.addClass(jqFolding.classHeaderHidden);
					thisNode.removeClass(jqFolding.classHeaderShown);
				});
		} else {
			// 显示
			//list.css("display", "block");
			list.slideDown("fast", function () {
					list.addClass(jqFolding.classShown);
					list.removeClass(jqFolding.classHidden);
					thisNode.addClass(jqFolding.classHeaderShown);
					thisNode.removeClass(jqFolding.classHeaderHidden);
				});
		}
		
	},
	
	/**
	 * 初始化
	 */
	init : function () {
		var divListwHeaders = $(jqFolding.listSelector);
		var len = divListwHeaders.length;
		
		//alert(len);
		for (var i = 0; i < len; i++) {
			var divListwHeader = $(divListwHeaders[i]);
			divListwHeader.find(jqFolding.triggerSelector).click(jqFolding.toggle);
			// 阻止事件浮升
			var hrefs = divListwHeader.find(jqFolding.triggerSelector).find("a");
			var count = hrefs.length;
			for (var j = 0; j < count; j++)
				hrefs[j].onclick = function (e) {
					jqFolding.stopBubble(e);
				};
			
			if (jqFolding.initState == "hide") {
				divListwHeader.find(jqFolding.triggerSelector).addClass(jqFolding.classHeaderShown);
				divListwHeader.find(jqFolding.containerSelector).addClass(jqFolding.classHidden);
			} else {
				divListwHeader.find(jqFolding.triggerSelector).addClass(jqFolding.classHeaderHidden);
				divListwHeader.find(jqFolding.containerSelector).addClass(jqFolding.classShown);
			}
		}
	}
};

 