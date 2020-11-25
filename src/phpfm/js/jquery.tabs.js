/**
 * jQuery Tabs
 * License: GPL 2.0
 * Author: Sun Junwen
 * Version: 1.5.3
 */
var jqTabs = {
    prefix: "#jqTabs", // jqTab class 名称的前缀
    splitor: "", // 分隔符
    navSelector: "Nav", // 导航栏选择器
    navItemSelector: "Nav > a", // 导航栏内项目选择器
    linkPrefix: "#", // 链接名称前缀
    linkAttr: "href", // 链接属性
    nameAttr: "name", // 名称属性
    titleAttr: "title", // 标题属性
    animation: false,
    generateNav: false, // 是否自动生成导航栏

    navs: null,
    contents: null,
    browser: null,

    /**
     * 设置参数
     */
    setup: function (values) {
        jqTabs.prefix = values.prefix || jqTabs.prefix;
        jqTabs.splitor = values.splitor || jqTabs.splitor;
        jqTabs.navSelector = values.navSelector || jqTabs.navSelector;
        jqTabs.navItemSelector = values.navItemSelector || jqTabs.navItemSelector;
        jqTabs.linkPrefix = values.linkPrefix || jqTabs.linkPrefix;
        jqTabs.linkAttr = values.linkAttr || jqTabs.linkAttr;
        jqTabs.nameAttr = values.nameAttr || jqTabs.nameAttr;
        jqTabs.titleAttr = values.titleAttr || jqTabs.titleAttr;
        jqTabs.generateNav = values.generateNav || jqTabs.generateNav;
        jqTabs.animation = values.animation || jqTabs.animation;
    },

    /**
     * 显示指定 tab
     */
    display: function (name, isAni) {
        var length = jqTabs.contents.length;
        var displayed = false;
        var toDisplay = null;
        var toDisplayName = "";
        var matched = false;
        for (var i = 0; i < length; i++) {
            var id = jqTabs.prefix + jqTabs.contents[i];
            var jqObj = $(id);
            if (name != jqTabs.contents[i]) {
                if (jqObj.is(":visible") && !matched) {
                    toDisplay = jqObj; // 还没有找到匹配前，就是原来那个
                    toDisplayName = jqTabs.contents[i];
                }
                jqObj.hide();
            } else {
                matched = true;
                toDisplay = jqObj;
                toDisplayName = name;
            }
        }

        if (toDisplay != null) {
            if (isAni && matched)
                toDisplay.fadeIn();
            else
                toDisplay.show();

            displayed = true;
        }

        if (displayed) {
            for (navName in jqTabs.navs) {
                jqTabs.navs[navName].removeClass("selected");
            }

            jqTabs.navs[toDisplayName].addClass("selected");
        } else {
            jqTabs.display(jqTabs.contents[0], isAni);
        }
    },

    /**
     * 初始化内容
     */
    initContents: function () {
        jqTabs.display(jqTabs.contents[0], false);
    },

    /**
     * 初始化导航栏
     */
    initNav: function () {
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

    /**
     * 初始化按钮
     */
    initClick: function () {
        var navs = $(jqTabs.prefix + jqTabs.navItemSelector);
        var length = navs.length;
        for (var i = 0; i < length; i++) {
            var nav = $(navs[i]);
            nav.click(function () {
                jqTabs.display($(this).attr(jqTabs.titleAttr), jqTabs.animation);
            });
            jqTabs.navs[nav.attr(jqTabs.titleAttr)] = nav;
        }
    },

    /**
     * 初始化
     */
    init: function () {
        jqTabs.contents = new Array();
        jqTabs.navs = new Array();

        jqTabs.initNav();
        jqTabs.initClick();
        jqTabs.initContents();

        var url = window.location.href;
        var curTitle = url.split(jqTabs.linkPrefix)[1];

        jqTabs.display(curTitle, jqTabs.animation);
    }
}
