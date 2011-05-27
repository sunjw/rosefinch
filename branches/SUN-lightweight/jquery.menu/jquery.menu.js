/**
 * jQuery Menu
 * License: GPL 2.0
 * Author: Sun Junwen
 * Version: 1.7.3
 */
var jqMenu = {
	menuItemsSelector : ".menu",
	menuButtonSelector : ".subToggle",
	subMenuSelector : ".submenu",
	inlineShadow : "transparent url('shadow.png') no-repeat right bottom",
	hoverOpen : false,
	
	isIE : true,
	opened : false,
	delay : 500,
	showTimer : 0,
	hideTimer : 0,
	activeButton : null,
	
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
	
	setup : function (values) {
		jqMenu.menuItemsSelector = values.menuItemsSelector || jqMenu.menuItemsSelector;
		jqMenu.menuButtonSelector = values.menuButtonSelector || jqMenu.menuButtonSelector;
		jqMenu.subMenuSelector = values.subMenuSelector || jqMenu.subMenuSelector;
		jqMenu.inlineShadow = values.inlineShadow || jqMenu.inlineShadow;
		jqMenu.hoverOpen = values.hoverOpen || jqMenu.hoverOpen;
	},
	
	/**
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
		jqMenu.opened = false;
	},
	
	/**
	 * 关闭子菜单
	 */
	hideSubMenu : function (subMenu) {
		// subMenu.css("display", "none");
		if (jqMenu.isIE)
			subMenu.css("background", "none"); // IE-hack 去掉背景
		subMenu.fadeOut("fast");
	},
	
	/**
	 * 打开菜单
	 */
	showSubMenu : function () {
		// $(this).css("background-image",
		// "url('images/path-arrow-down.gif')");
		var thisButton = jqMenu.activeButton;
		if (!jqMenu.hoverOpen)
			thisButton = this;
		if (!thisButton)
			return;
		var thisSub = $(thisButton.parentNode).children(jqMenu.subMenuSelector);
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
			$(thisButton).addClass("selected");
			if (jqMenu.isIE) {
				thisSub.css("background", "none");
			}
			thisSub.fadeIn("fast", function () {
					if (jqMenu.isIE) {
						// IE-hack 去掉背景
						$(this).css("background", jqMenu.inlineShadow);
					}
					jqMenu.opened = true;
				});
		} else if (!jqMenu.hoverOpen) {
			$(thisButton).removeClass("selected");
			// thisSub.css("display", "none");
			jqMenu.hideSubMenu(thisSub);
		}
	},
	
	delayShowSubMenu : function () {
		jqMenu.activeButton = this;
		if (jqMenu.opened) {
			// 有菜单打开时，不要 delay，直接打开新菜单
			jqMenu.showSubMenu();
		} else {
			// 没有菜单打开，第一个要 delay
			clearTimeout(jqMenu.showTimer);
			jqMenu.showTimer = setTimeout("jqMenu.showSubMenu()", jqMenu.delay * 0.75);
		}
	},
	
	delayHideMenu : function () {
		clearTimeout(jqMenu.showTimer);
		if (jqMenu.opened) {
			clearTimeout(jqMenu.hideTimer);
			jqMenu.hideTimer = setTimeout("jqMenu.hideAllSubMenus()", jqMenu.delay);
		}
	},
	
	/**
	 * 初始化菜单
	 */
	init : function () {
		jqMenu.isIE = $.browser.msie ? true : false;
		
		var body = $("body").get(0);
		var menuItems = $(jqMenu.menuItemsSelector);
		var count = menuItems.length;
		
		if (!jqMenu.hoverOpen) {
			// 点击开关
			for (var i = 0; i < count; i++) {
				var menuItem = $(menuItems.get(i));
				menuItem.get(0).onclick = function (e) {
					jqMenu.stopBubble(e); // 取消事件浮升
				}
				var button = menuItem.find(jqMenu.menuButtonSelector);
				button.get(0).onclick = jqMenu.showSubMenu;
			}
			
			body.onclick = jqMenu.hideAllSubMenus;
		} else {
			// hover 开关
			for (var i = 0; i < count; i++) {
				var menuItem = $(menuItems.get(i));
				menuItem.get(0).onmouseover = function (e) {
					jqMenu.stopBubble(e); // 取消事件浮升
					clearTimeout(jqMenu.hideTimer);
				}
				var button = menuItem.find(jqMenu.menuButtonSelector);
				button.get(0).onmouseover = jqMenu.delayShowSubMenu;
			}
			
			body.onmouseover = jqMenu.delayHideMenu;
		}
	}
}
