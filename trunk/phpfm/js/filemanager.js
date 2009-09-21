var ajaxBg;
var inputChecks;
var selectedItems;
var sortName;
var sortOrder;
var delayID = 0;

function doNothing() {
	void (0);
}

function detailViewItemOver(item) {
	var detailViewItem = $(item);
	detailViewItem.addClass("selected");
}

function detailViewItemOut(item) {
	var detailViewItem = $(item);
	var checkBox = $(detailViewItem.children().get(0)).children().get(0); // 比较笨的办法
	if (checkBox.checked != true) {
		detailViewItem.removeClass("selected");
	}
}

function largeiconViewItemOver(item) {
	var largeiconViewItem = $(item);
	largeiconViewItem.addClass("selected");
}

function largeiconViewItemOut(item) {
	var largeiconViewItem = $(item);
	var checkBox = $(largeiconViewItem.children().get(0)).children().get(0); // 比较笨的办法
	if (checkBox.checked != true) {
		largeiconViewItem.removeClass("selected");
	}
}

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

function getLeftMargin() {
	var viewWidth = document.documentElement.clientWidth;
	var leftMargin = (viewWidth - 400) / 2; // 居中
	return leftMargin;
}

function displayAjaxBg(canClose) {
	if (canClose) {
		ajaxBg.get(0).onclick = closeAjax;
	} else {
		ajaxBg.get(0).onclick = doNothing;
	}
	ajaxBg.css("height", document.documentElement.scrollHeight + "px");
	ajaxBg.css("display", "block");
}

function setOldname() {
	$("div#oldnameLine").css("display", "block");
	var oldnameInput = $("input#oldname");
	var newnameInput = $("input#newname");
	var path = selectedItems[0];
	var oldname = path.substring(path.lastIndexOf("/") + 1, path.length);
	oldnameInput.attr("value", oldname); // 显示原文件名
	newnameInput.attr("value", oldname);
}

function cleanOldname() {
	var oldnameInput = $("input#oldname");
	var newnameInput = $("input#newname");
	oldnameInput.attr("value", ""); // 显示原文件名
	newnameInput.attr("value", "");
}

function displayAjaxInput(action, title, isInput) {
	var ajaxInput = $("div#ajaxInput");
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

function clickRename() {
	displayAjaxBg(true);

	setOldname();

	displayAjaxInput("func/rename.func.php", "重命名", true);
}

function clickNewFolder() {
	// alert("newfolder");
	displayAjaxBg(true);

	displayAjaxInput("func/newfolder.func.php", "新建目录", true);
}

function clickCut() {
	// alert("cut");
	sendAjaxOper("cut");
}

function clickCopy() {
	sendAjaxOper("copy");
}

function sendAjaxOper(oper) {

	var itemsStr = selectedItems.join("|");

	// var subdir = $("input#subdir").val();

	$.post("func/clipboard.ajax.php", {
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

function clickPaste() {
	var subdir = $("input#subdir").attr("value");
	var returnURL = $("input#return").val();

	displayAjaxBg(false);

	var ajaxWait = $("div#ajaxWait");

	ajaxWait.css("left", getLeftMargin() + "px");
	ajaxWait.fadeIn();

	$.get("func/paste.ajax.php?subdir=" + subdir + "&return=" + returnURL,
			function(data) {
				// alert(data);
			window.location.reload();
		});
}

function clickDelete() {
	displayAjaxBg(true);

	var ajaxDelete = $("div#ajaxDelete");

	ajaxDelete.css("left", getLeftMargin() + "px");
	ajaxDelete.fadeIn();

}

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

	$.post("func/delete.ajax.php", {
		"items" :itemsStr
	}, function(data) {
		// alert(data);
			if (data == "ok") {
				window.location.reload();
			}
		});
}

function clickUpload() {
	displayAjaxBg(true);
	displayAjaxInput("func/upload.func.php", "上传", false);
}

function selectAll() {
	var count = inputChecks.length;

	for ( var i = 0; i < count; i++) {
		var checkBox = $(inputChecks.get(i));
		checkBox.attr("checked", "checked");
	}

	viewItemCheck();
}

function deselect() {
	var count = inputChecks.length;

	for ( var i = 0; i < count; i++) {
		var checkBox = $(inputChecks.get(i));
		checkBox.removeAttr("checked");
	}

	viewItemCheck();
}

function setSortArrow(name, order) {
	sortName = name;
	sortOrder = order;
	// var str = "#mainView > .header > span." + name + " > a";
	// var item = $(str);
	// item.addClass("sort" + order);
}

function getMessage() {
	$.get("func/getmessage.ajax.php", function(data) {
		if (data != "") {
			var phpfmMessage = $("#phpfmMessage");
			if (phpfmMessage.length == 1) {
				phpfmMessage.html(data);
				phpfmMessage.fadeIn();
			}

			clearTimeout(delayID);
			delayID = setTimeout("CloseMessage()", 5000);
		}
	});

}

function CloseMessage() {
	$("#phpfmMessage").fadeOut();
}

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

function initAjaxFunc() {
	ajaxBg = $("div#ajaxBg");

	var ajaxFuncClose = $("div.ajaxHeader > .ajaxFuncClose");
	var count = ajaxFuncClose.length;
	for ( var i = 0; i < count; i++) {
		ajaxFuncClose.get(i).onclick = closeAjax;
	}
}

function closeAjax() {
	var ajaxInput = $("div#ajaxInput");
	if (ajaxInput.css("display") == "block")
		ajaxInput.fadeOut();

	var ajaxDelete = $("div#ajaxDelete");
	if (ajaxDelete.css("display") == "block")
		ajaxDelete.fadeOut();

	$("div#oldnameLine").css("display", "none");
	cleanOldname();
	ajaxBg.css("display", "none");
}

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
						&& subMenu.css("display") == "block") {
					subMenu.prev(".arrow").removeClass("selected");
					// subMenu.css("display", "none");
					hideSubMenu(subMenu);
				}
			}

			if (thisSub.css("display") == "none") {
				$(this).addClass("selected");
				thisSub.fadeIn("fast");
			} else {
				$(this).removeClass("selected");
				// thisSub.css("display", "none");
				hideSubMenu(thisSub);
			}
		};
	}
}

function hideAllSubMenus() {
	var subMenus = $(".subMenu");
	for ( var i = 0; i < subMenus.length; i++) {
		var subMenu = $(subMenus[i]);
		subMenu.prev(".arrow").removeClass("selected");
		// subMenu.css("display", "none");
		hideSubMenu(subMenu);
	}
}

function hideSubMenu(subMenu) {
	// subMenu.css("display", "none");
	subMenu.fadeOut("fast");
}

function stopBubble(e) {
	var e = e ? e : window.event;
	if (window.event) { // IE
		e.cancelBubble = true;
	} else { // FF
		// e.preventDefault();
		e.stopPropagation();
	}
}

function init() {
	var str = "#mainView > .header > span." + sortName + " > a";
	var item = $(str);
	item.addClass("sort" + sortOrder);

	initFullPath();

	initMainView();

	initAjaxFunc();

	getMessage();

}

$(window).load(init); // 运行准备函数
