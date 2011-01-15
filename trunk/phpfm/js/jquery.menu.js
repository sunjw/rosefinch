/**
 * jQuery Menu
 * License: GPL 2.0
 * Author: Sun Junwen
 * Version: 1.6
 */
var jqMenu = {
	menuItemsSelector : ".menu",
	menuButtonSelector : ".subToggle",
	subMenuSelector : ".submenu",
	inlineShadow : "transparent url('shadow.png') no-repeat right bottom",
	isIE : true,
	
	/*
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
	
	setup : function (values) {
		jqMenu.menuItemsSelector = 
			(values.menuItemsSelector != undefined) ? values.menuItemsSelector : jqMenu.menuItemsSelector;
		jqMenu.menuButtonSelector = 
			(values.menuButtonSelector != undefined) ? values.menuButtonSelector : jqMenu.menuButtonSelector;
		jqMenu.subMenuSelector = 
			(values.subMenuSelector != undefined) ? values.subMenuSelector : jqMenu.subMenuSelector;
		jqMenu.inlineShadow = 
			(values.inlineShadow != undefined) ? values.inlineShadow : jqMenu.inlineShadow;
	},
	
	/*
	 * 关闭所有子菜单
	 */
	hideAllSubMenus : function () {
		var subMenus = $(jqMenu.subMenuSelector);
		for (var i = 0; i < subMenus.length; i++) {
			var subMenu = $(subMenus[i]);
			subMenu.prev(jqMenu.menuButtonSelector).removeClass("selected");
			// subMenu.css("display", "none");
			jqMenu.hideSubMenu(subMenu);
		}
	},
	
	/*
	 * 关闭子菜单
	 */
	hideSubMenu : function (subMenu) {
		// subMenu.css("display", "none");
		if (jqMenu.isIE) 
			subMenu.css("background", "none"); // IE-hack 去掉背景
		subMenu.fadeOut("fast");
	},
	
	/*
	 * 初始化菜单
	 */
	init : function () {
		jqMenu.isIE = $.browser.msie ? true : false;
		
		var body = $("body").get(0);
		body.onclick = jqMenu.hideAllSubMenus;
		
		var menuItems = $(jqMenu.menuItemsSelector);
		var count = menuItems.length;
		for (var i = 0; i < count; i++) {
			var menuItem = $(menuItems.get(i));
			menuItem.get(0).onclick = function (e) {
				jqMenu.stopBubble(e); // 取消事件浮升
			}
			var button = menuItem.children(jqMenu.menuButtonSelector);
			button.get(0).onclick = function () {
				// $(this).css("background-image",
				// "url('images/path-arrow-down.gif')");
				var thisSub = $(this.parentNode).children(jqMenu.subMenuSelector);
				var subMenus = $(jqMenu.subMenuSelector);
				for (var i = 0; i < subMenus.length; i++) {
					var subMenu = $(subMenus[i]);
					if (thisSub.get(0) != subMenu.get(0) 
						 && subMenu.is(":visible")) {
						subMenu.prev(jqMenu.menuButtonSelector).removeClass("selected");
						// subMenu.css("display", "none");
						jqMenu.hideSubMenu(subMenu);
					}
				}
				/*
				 * if(!isIE) { thisSub.css("background", "transparent
				 * url('images/shadow.png') no-repeat right bottom"); }
				 */
				if (!thisSub.is(":visible")) {
					$(this).addClass("selected");
					if (jqMenu.isIE) {
						thisSub.css("background", "none");
					}
					thisSub.fadeIn("fast", function () {
							if (jqMenu.isIE) {
								// IE-hack 去掉背景
								$(this).css("background", jqMenu.inlineShadow);
							}
						});
				} else {
					$(this).removeClass("selected");
					// thisSub.css("display", "none");
					jqMenu.hideSubMenu(thisSub);
				}
			};
		}
	}
}


 