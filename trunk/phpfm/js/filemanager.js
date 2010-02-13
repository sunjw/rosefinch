var ajaxBg;
var inputChecks;
var selectedItems;
var sortName;
var sortOrder;
var delayID = 0;
var isIE;
var inlineShadow = "transparent url('images/shadow.png') no-repeat right bottom";

/*
 * 什么也不做
 */
function doNothing() {
	void (0);
}

/*
 * 详细信息模式时，当鼠标移到项目上的操作
 */
function detailViewItemOver(item) {
	var detailViewItem = $(item);
	detailViewItem.addClass("selected");
}

/*
 * 详细信息模式时，当鼠标移出项目的操作
 */
function detailViewItemOut(item) {
	var detailViewItem = $(item);
	var checkBox = $(detailViewItem.children().get(0)).children().get(0); // 比较笨的办法
	if (checkBox.checked != true) {
		detailViewItem.removeClass("selected");
	}
}

/*
 * 大图标模式时，当鼠标移到项目上的操作
 */
function largeiconViewItemOver(item) {
	var largeiconViewItem = $(item);
	largeiconViewItem.addClass("selected");
}

/*
 * 大图标模式时，当鼠标移出项目的操作
 */
function largeiconViewItemOut(item) {
	var largeiconViewItem = $(item);
	var checkBox = $(largeiconViewItem.children().get(0)).children().get(0); // 比较笨的办法
	if (checkBox.checked != true) {
		largeiconViewItem.removeClass("selected");
	}
}

/*
 * 大图标模式时，当鼠标点击项目的操作
 */
function largeiconViewItemClicked(item) {
	var largeiconViewItem = $(item);
	var checkBox = $(largeiconViewItem.children().get(0)).children().get(0); // 比较笨的办法
	if (checkBox.checked) {
		$(checkBox).removeAttr("checked");
	} else {
		$(checkBox).attr("checked", "checked");
		largeiconViewItem.addClass("selected");
	}
	viewItemCheck();
}

/*
 * 项目选择改变
 */
function viewItemCheck() {
	setButton("toolbarCut", "images/toolbar-cut-disable.gif", doNothing,
			"disable", "");
	setButton("toolbarCopy", "images/toolbar-copy-disable.gif", doNothing,
			"disable", "");
	setButton("toolbarRename", "images/toolbar-rename-disable.gif", doNothing,
			"disable", "");
	setButton("toolbarDelete", "images/toolbar-delete-disable.gif", doNothing,
			"disable", "");

	var count = inputChecks.length;
	var checkedItemsCount = 0;
	selectedItems = new Array();

	for ( var i = 0; i < count; i++) {
		var checkBox = inputChecks.get(i);
		var item = $(checkBox.parentNode.parentNode); // CheckBox 对应的项目
		if (checkBox.checked) {
			checkedItemsCount++;
			selectedItems.push(checkBox.name);
			item.addClass("selected");
		} else {
			item.removeClass("selected");
		}
	}

	if (checkedItemsCount > 0) {
		setButton("toolbarCut", "images/toolbar-cut.gif", clickCut, "",
				"disable");
		setButton("toolbarCopy", "images/toolbar-copy.gif", clickCopy, "",
				"disable");
		setButton("toolbarDelete", "images/toolbar-delete.gif", clickDelete,
				"", "disable");
		if (checkedItemsCount == 1) {
			setButton("toolbarRename", "images/toolbar-rename.gif",
					clickRename, "", "disable");
		}
	}
}

/*
 * 设置按钮
 */
function setButton(className, src, clickFunc, addClass, removeClass) {
	var buttons = $("div#leftToolbar > a");

	for ( var i = 0; i < buttons.length; i++) {
		var button = $(buttons.get(i));
		if (button.hasClass(className)) {
			button.get(0).onclick = clickFunc;
			if (addClass != "")
				button.addClass(addClass);
			if (removeClass != "")
				button.removeClass(removeClass);
			var img = button.children("img");
			img.attr("src", src);
		}
	}
}

/*
 * 点击了重命名的操作
 */
function clickRename() {
	displayAjaxBg(true);

	setOldname();

	displayAjaxInput("func/post.func.php", "rename", "重命名", true);
}

/*
 * 点击了新建目录的操作
 */
function clickNewFolder() {
	// alert("newfolder");
	displayAjaxBg(true);

	displayAjaxInput("func/post.func.php", "newfolder", "新建目录", true);
}

/*
 * 点击了剪切的操作
 */
function clickCut() {
	// alert("cut");
	sendAjaxOper("cut");
}

/*
 * 点击了复制的操作
 */
function clickCopy() {
	sendAjaxOper("copy");
}

/*
 * 点击了粘贴的操作
 */
function clickPaste() {
	var subdir = $("input#subdir").attr("value");
	var returnURL = $("input#return").val();

	displayAjaxBg(false);

	var ajaxWait = $("div#ajaxWait");

	ajaxWait.css("left", getLeftMargin() + "px");
	ajaxWait.fadeIn();

	$.post("func/post.func.php", {
		"oper" :"paste",
		"subdir" :subdir,
		"return" :returnURL
	}, function(data) {
		// alert(data);
			window.location.reload();
		});
	/*
	 * $.get("func/paste.ajax.php?subdir=" + subdir + "&return=" + returnURL,
	 * function(data) { // alert(data); window.location.reload(); });
	 */
}

/*
 * 点击了删除的操作
 */
function clickDelete() {
	displayAjaxBg(true);

	var ajaxDelete = $("div#ajaxDelete");

	ajaxDelete.css("left", getLeftMargin() + "px");
	ajaxDelete.fadeIn();

}

/*
 * 确认删除后的操作
 */
function doDelete() {
	// 准备界面
	var ajaxDelete = $("div#ajaxDelete");
	ajaxDelete.css("display", "none");

	ajaxBg.get(0).onclick = doNothing;

	var ajaxWait = $("div#ajaxWait");

	ajaxWait.css("left", getLeftMargin() + "px");
	ajaxWait.fadeIn();

	var itemsStr = selectedItems.join("|");

	// var subdir = $("input#subdir").val();

	$.post("func/post.func.php", {
		"oper" :"delete",
		"items" :itemsStr
	}, function(data) {
		// alert(data);
			if (data == "ok") {
				window.location.reload();
			}
		});
}

/*
 * 点击了上传的操作
 */
function clickUpload() {
	displayAjaxBg(true);
	displayAjaxInput("func/post.func.php", "upload", "上传", false);
}

/*
 * 点击了全选的操作
 */
function selectAll() {
	var count = inputChecks.length;

	for ( var i = 0; i < count; i++) {
		var checkBox = $(inputChecks.get(i));
		checkBox.attr("checked", "checked");
	}

	viewItemCheck();
}

/*
 * 点击了取消选择的操作
 */
function deselect() {
	var count = inputChecks.length;

	for ( var i = 0; i < count; i++) {
		var checkBox = $(inputChecks.get(i));
		checkBox.removeAttr("checked");
	}

	viewItemCheck();
}

/*
 * 设置显示排序的箭头
 */
function setSortArrow(name, order) {
	sortName = name;
	sortOrder = order;
}

/*
 * 获得消息
 */
function getMessage() {
	$.get("func/getmessage.ajax.php", function(data) {
		if (data != "") {
			var phpfmMessage = $("#phpfmMessage");
			if (phpfmMessage.length == 1) {
				var msg;
				var stat;

				data = data.split("|PHPFM|");
				msg = data[0];
				stat = data[1];

				phpfmMessage.html(msg);
				if (stat == 2) {
					// 错误消息
					phpfmMessage.addClass("wrong");
				} else {
					phpfmMessage.removeClass("wrong");
				}

				phpfmMessage.fadeIn();
			}

			clearTimeout(delayID);
			delayID = setTimeout("closeMessage()", 10000);
		}
	});

}

/*
 * 关闭消息
 */
function closeMessage() {
	$("#phpfmMessage").fadeOut();
}

/*
 * 获得左边距，使得输入部分居中
 */
function getLeftMargin() {
	var viewWidth = document.documentElement.clientWidth;
	var leftMargin = (viewWidth - 420) / 2; // 居中
	return leftMargin;
}

/*
 * 发送 ajax
 */
function sendAjaxOper(oper) {

	var itemsStr = selectedItems.join("|");

	// var subdir = $("input#subdir").val();

	$.post("func/post.func.php", {
		"oper" :oper,
		"items" :itemsStr
	}, function(data) {
		if (data == "ok") {
			setButton("toolbarPaste", "images/toolbar-paste.gif", clickPaste,
					"", "disable");
		} else {
			setButton("toolbarPaste", "images/toolbar-paste-disable.gif",
					doNothing, "disable", "");
		}
	});

	setTimeout("getMessage()", 500);
}

/*
 * 设置修改名称时的原名称
 */
function setOldname() {
	$("div#oldnameLine").css("display", "block");
	var oldnameInput = $("input#oldname");
	var newnameInput = $("input#newname");
	var path = selectedItems[0];
	var oldname = path.substring(path.lastIndexOf("/") + 1, path.length);
	oldnameInput.attr("value", oldname); // 显示原文件名
	newnameInput.attr("value", oldname);
}

/*
 * 清除修改名称输入框中的原名称
 */
function cleanOldname() {
	var oldnameInput = $("input#oldname");
	var newnameInput = $("input#newname");
	oldnameInput.attr("value", ""); // 显示原文件名
	newnameInput.attr("value", "");
}

/*
 * 初始化 ajax
 */
function initAjaxFunc() {
	ajaxBg = $("div#ajaxBg");

	var ajaxFuncClose = $("div.ajaxHeader > .ajaxFuncClose");
	var count = ajaxFuncClose.length;
	for ( var i = 0; i < count; i++) {
		ajaxFuncClose.get(i).onclick = closeAjax;
	}
}

/*
 * 显示 Ajax 输入的半透明背景
 */
function displayAjaxBg(canClose) {
	if (canClose) {
		ajaxBg.get(0).onclick = closeAjax;
	} else {
		ajaxBg.get(0).onclick = doNothing;
	}
	ajaxBg.css("height", document.documentElement.scrollHeight + "px");
	ajaxBg.css("display", "block");
}

/*
 * 显示 ajax 输入部分
 */
function displayAjaxInput(action, oper, title, isInput) {
	var ajaxInput = $("div#ajaxInput");
	var operInput = $("input#oper");
	operInput.val(oper);
	var form = ajaxInput.children("form");
	form.attr("action", action);
	var titleSpan = ajaxInput.children("div.ajaxHeader").children("span");
	titleSpan.html(title);
	if (isInput) {
		// form.removeAttr("enctype");
		$("div#divInput").removeClass("ajaxHidden");
		$("div#divUpload").addClass("ajaxHidden");

	} else {
		// form.attr("enctype", "multipart/form-data");
		$("div#divInput").addClass("ajaxHidden");
		$("div#divUpload").removeClass("ajaxHidden");
	}
	ajaxInput.css("left", getLeftMargin() + "px");
	ajaxInput.fadeIn();
	if (isInput) {
		$("input#newname").focus();
		$("input#newname").get(0).select();
	}
}

/*
 * 关闭 ajax 部分
 */
function closeAjax() {
	var ajaxInput = $("div#ajaxInput");
	if (ajaxInput.is(":visible"))
		ajaxInput.fadeOut();

	var ajaxDelete = $("div#ajaxDelete");
	if (ajaxDelete.is(":visible"))
		ajaxDelete.fadeOut();

	$("div#oldnameLine").css("display", "none");
	cleanOldname();
	ajaxBg.css("display", "none");
}

/*
 * 准备主视图
 */
function initMainView() {
	var detailViewItems = $("ul#detailView");
	var largeiconViewItems = $("div#largeiconView");
	if (detailViewItems.length > 0) {
		// 是详细视图
		var items = detailViewItems.children("li");
		var count = items.length;
		for ( var i = 0; i < count; i++) {
			var item = $(items.get(i));
			if (!item.hasClass("empty")) {
				var jsObj = item.get(0);
				jsObj.onmouseover = function() {
					detailViewItemOver(this);
				};
				jsObj.onmouseout = function() {
					detailViewItemOut(this);
				};
			}
		}
	} else if (largeiconViewItems.length > 0) {
		// 是大图标视图
		var items = largeiconViewItems.children("div.largeIconItem");
		var count = items.length;
		for ( var i = 0; i < count; i++) {
			var item = $(items.get(i));
			if (!item.hasClass("empty")) {
				var jsObj = item.get(0);
				jsObj.onmouseover = function() {
					largeiconViewItemOver(this);
				};
				jsObj.onmouseout = function() {
					largeiconViewItemOut(this);
				};
				jsObj.onclick = function() {
					largeiconViewItemClicked(this);
				};
				var as = jsObj.getElementsByTagName("a");
				for ( var j = 0; j < as.length; j++) {
					var a = as[j];
					a.onclick = function(e) {
						stopBubble(e);
					};
				}

			}
		}
	}

	inputChecks = $("input.inputCheck");
	var count = inputChecks.length;
	for ( var i = 0; i < count; i++) {
		var check = inputChecks.get(i);
		check.onclick = function(e) {
			viewItemCheck();
			stopBubble(e);
		};
	}
	viewItemCheck();
}

/*
 * 初始化完整路径
 */
function initFullPath() {
	var body = $("body").get(0);
	body.onclick = hideAllSubMenus;

	var divPathSlashes = $("div.pathSlash");
	var count = divPathSlashes.length;
	for ( var i = 0; i < count; i++) {
		var divPathSlash = $(divPathSlashes.get(i));
		divPathSlash.get(0).onclick = function(e) {
			stopBubble(e); // 取消事件浮升
		}
		var button = divPathSlash.children(".arrow");
		button.get(0).onclick = function() {
			// $(this).css("background-image",
			// "url('images/path-arrow-down.gif')");
			var thisSub = $(this.parentNode).children(".subMenu");
			var subMenus = $(".subMenu");
			for ( var i = 0; i < subMenus.length; i++) {
				var subMenu = $(subMenus[i]);
				if (thisSub.get(0) != subMenu.get(0)
						&& subMenu.is(":visible")) {
					subMenu.prev(".arrow").removeClass("selected");
					// subMenu.css("display", "none");
					hideSubMenu(subMenu);
				}
			}
			/*
			 * if(!isIE) { thisSub.css("background", "transparent
			 * url('images/shadow.png') no-repeat right bottom"); }
			 */
			if (!thisSub.is(":visible")) {
				$(this).addClass("selected");
				if (isIE) {
					thisSub.css("background", "none");
				}
				thisSub.fadeIn("fast", function() {
					if (isIE) {
						// IE-hack 去掉背景
						$(this).css("background", inlineShadow);
					}
				});
			} else {
				$(this).removeClass("selected");
				// thisSub.css("display", "none");
				hideSubMenu(thisSub);
			}
		};
	}
}

/*
 * 关闭所有子菜单
 */
function hideAllSubMenus() {
	var subMenus = $(".subMenu");
	for ( var i = 0; i < subMenus.length; i++) {
		var subMenu = $(subMenus[i]);
		subMenu.prev(".arrow").removeClass("selected");
		// subMenu.css("display", "none");
		hideSubMenu(subMenu);
	}
}

/*
 * 关闭子菜单
 */
function hideSubMenu(subMenu) {
	// subMenu.css("display", "none");
	if (isIE)
		subMenu.css("background", "none"); // IE-hack 去掉背景
	subMenu.fadeOut("fast");
}

/*
 * 阻止事件浮升
 */
function stopBubble(e) {
	var e = e ? e : window.event;
	if (window.event) { // IE
		e.cancelBubble = true;
	} else { // FF
		// e.preventDefault();
		e.stopPropagation();
	}
}

/*
 * 初始化
 */
function init() {
	isIE = $.browser.msie ? true : false;
	var str = "#mainView > .header > span." + sortName + " > a";
	var item = $(str);
	item.addClass("sort" + sortOrder);

	initFullPath();

	initMainView();

	initAjaxFunc();

	getMessage();

	$('a.lightboxImg').lightBox( {
		overlayOpacity :0.5,
		autoAdapt :true
	});

}

//$(window).load(init); // 运行准备函数
$(init);
