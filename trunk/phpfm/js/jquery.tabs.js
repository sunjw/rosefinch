/**
 * jQuery Tabs
 * License: GPL 2.0
 * Author: Sun Junwen
 * Version: 1.5
 */
var jqTabs = {
	prefix : "#jqTabs",
	splitor : "",
	navSelector : "Nav",
	navItemSelector : "Nav > a",
	linkPrefix : "#",
	linkAttr : "href",
	nameAttr : "name",
	titleAttr : "title",
	generateNav : false,
	
	navs : null,
	contents : null,
	browser : null,
	
	setup : function (values) {
		jqTabs.prefix = (values.prefix != undefined) ? values.prefix : jqTabs.prefix;
		jqTabs.splitor = (values.splitor != undefined) ? values.splitor : jqTabs.splitor;
		jqTabs.navSelector = (values.navSelector != undefined) ? values.navSelector : jqTabs.navSelector;
		jqTabs.navItemSelector = (values.navItemSelector != undefined) ? values.navItemSelector : jqTabs.navItemSelector;
		jqTabs.linkPrefix = (values.linkPrefix != undefined) ? values.linkPrefix : jqTabs.linkPrefix;
		jqTabs.linkAttr = (values.linkAttr != undefined) ? values.linkAttr : jqTabs.linkAttr;
		jqTabs.nameAttr = (values.nameAttr != undefined) ? values.nameAttr : jqTabs.nameAttr;
		jqTabs.titleAttr = (values.titleAttr != undefined) ? values.titleAttr : jqTabs.titleAttr;
		jqTabs.generateNav = (values.generateNav != undefined) ? values.generateNav : jqTabs.generateNav;
	},
	
	display : function (name, isAni) {
		var length = jqTabs.contents.length;
		var displayed = false;
		for (var i = 0; i < length; i++) {
			var id = jqTabs.prefix + jqTabs.contents[i];
			var jqObj = $(id);
			if (name != jqTabs.contents[i]) {
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
			for (navName in jqTabs.navs) {
				jqTabs.navs[navName].removeClass("selected");
			}
			
			jqTabs.navs[name].addClass("selected");
		} else {
			jqTabs.display(jqTabs.contents[0], isAni);
		}
	},
	
	initContents : function () {
		jqTabs.display(jqTabs.contents[0], false);
	},
	
	initNav : function () {
		var nav = $(jqTabs.prefix + jqTabs.navSelector); // 导航部分
		var navHTML = "";
		var doc = $(jqTabs.prefix); // 主体部分
		var docContents = doc.children();
		var length = docContents.length;
		for (var i = 0; i < length; i++) {
			var docContent = $(docContents[i]);
			var href = $(docContent.children().get(0));
			var name = href.attr(jqTabs.nameAttr);
			var title = href.attr(jqTabs.titleAttr);
			jqTabs.contents.push(name);
			if (jqTabs.generateNav) {
				if (i > 0) {
					nav.append(jqTabs.splitor);
				}
				
				// IE hacks: href is different in old version of IE, so use title
				var link = $("<a/>").attr(jqTabs.linkAttr, jqTabs.linkPrefix + name).attr(jqTabs.titleAttr, name).text(
					title);
				jqTabs.navs[name] = $(link);
				nav.append(link);
			}
		}
	},
	
	initClick : function () {
		var navs = $(jqTabs.prefix + jqTabs.navItemSelector);
		var length = navs.length;
		for (var i = 0; i < length; i++) {
			var nav = $(navs[i]);
			nav.click(function () {
					jqTabs.display($(this).attr(jqTabs.titleAttr), true);
				});
			jqTabs.navs[nav.attr(jqTabs.titleAttr)] = nav;
		}
	},
	
	init : function () {
		jqTabs.contents = new Array();
		jqTabs.navs = new Array();
		
		jqTabs.initNav();
		jqTabs.initClick();
		jqTabs.initContents();
		
		var url = window.location.href;
		var curTitle = url.split(jqTabs.linkPrefix)[1];
		
		jqTabs.display(curTitle, true);
	}
}
 