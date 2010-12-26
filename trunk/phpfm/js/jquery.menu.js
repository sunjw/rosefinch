var jqMenu = {
	menuItemsSeletor : ".menuContainer",
	menuButtonSeletor : ".menuButton",
	subMenuSeletor : ".subMenu",
	inlineShadow : "transparent url('images/shadow.png') no-repeat right bottom",
	isIE : true,
	
	/*
	 * 阻止事件浮升
	 */
	stopBubble : function(e) {
		var e = e ? e : window.event;
		if(window.event) { // IE
			e.cancelBubble = true;
		} else { // FF
			// e.preventDefault();
			e.stopPropagation();
		}
	},
	
	/*
	 * 关闭所有子菜单
	 */
	hideAllSubMenus : function() {
		var subMenus = $(jqMenu.subMenuSeletor);
		for(var i = 0; i < subMenus.length; i++) {
			var subMenu = $(subMenus[i]);
			subMenu.prev(jqMenu.menuButtonSeletor).removeClass("selected");
			// subMenu.css("display", "none");
			jqMenu.hideSubMenu(subMenu);
		}
	},
	
	/*
	 * 关闭子菜单
	 */
	hideSubMenu : function(subMenu) {
		// subMenu.css("display", "none");
		if(jqMenu.isIE) 
			subMenu.css("background", "none"); // IE-hack 去掉背景
		subMenu.fadeOut("fast");
	},
	
	/*
	 * 初始化菜单
	 */
	setup : function() {
		jqMenu.isIE = $.browser.msie ? true : false;
		
		var body = $("body").get(0);
		body.onclick = jqMenu.hideAllSubMenus;
		
		var menuItems = $(this.menuItemsSeletor);
		var count = menuItems.length;
		for(var i = 0; i < count; i++) {
			var menuItem = $(menuItems.get(i));
			menuItem.get(0).onclick = function(e) {
				jqMenu.stopBubble(e); // 取消事件浮升
			}
			var button = menuItem.children(this.menuButtonSeletor);
			button.get(0).onclick = function() {
				// $(this).css("background-image",
				// "url('images/path-arrow-down.gif')");
				var thisSub = $(this.parentNode) 
				.children(jqMenu.subMenuSeletor);
				if(!thisSub.length) 
					return;
				var subMenus = $(jqMenu.subMenuSeletor);
				for(var i = 0; i < subMenus.length; i++) {
					var subMenu = $(subMenus[i]);
					if(thisSub.get(0) != subMenu.get(0) 
						 && subMenu.is(":visible")) {
						subMenu.prev(jqMenu.menuButtonSeletor).removeClass(
							"selected");
						// subMenu.css("display", "none");
						jqMenu.hideSubMenu(subMenu);
					}
				}
				/*
				 * if(!isIE) { thisSub.css("background", "transparent
				 * url('images/shadow.png') no-repeat right bottom"); }
				 */
				if(!thisSub.is(":visible")) {
					$(this).addClass("selected");
					if(jqMenu.isIE) {
						thisSub.css("background", "none");
						if($.browser.version < 7) 
							thisSub.css("width", "160px");
					}
					thisSub.fadeIn("fast", function() {
							if(jqMenu.isIE) {
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

jqMenu.init = function() {
	jqMenu.setup();
};
 