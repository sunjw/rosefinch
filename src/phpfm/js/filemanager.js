var FileManager = {
	funcBg: null,
	multilanTitles: null,
	inputChecks: null,
	selectedItems: null,
	sortName: null,
	sortOrder: null,
	isSearch: null,
	delayID: 0,
	miniMainViewHeight: 120,
	isIE: null,
	isMobile: null,

	funcDialog: {
		body: null,
		header: null,
		divInput: null,
		divDelete: null,
		divAudio: null,
		divWaiting: null
	},

	/*
	 * 检测是否有指定名称的 Cookie
	 */
	hasCookie: function (c_name) {
		return (new RegExp("(?:^|;\\s*)" + escape(c_name).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
	},

	/*
	 * 得到指定名称的 Cookie 值
	 */
	getCookie: function (c_name) {
		if (!c_name || !FileManager.hasCookie(c_name)) {
			return null;
		}
		return unescape(document.cookie.replace(new RegExp("(?:^|.*;\\s*)" + escape(c_name).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*((?:[^;](?!;))*[^;]?).*"), "$1"));
	},

	/*
	 * 设置指定名称的 Cookie 值
	 */
	setCookie: function (c_name, value) {
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + 365);
		var c_value = escape(value) + "; expires=" + exdate.toUTCString();
		document.cookie = c_name + "=" + c_value;
	},

	/*
	 * 检测是否支持 html5 <audio> 标签
	 */
	supportHtml5Audio: function () {
		return !!document.createElement('audio').canPlayType;
	},

	/*
	 * 什么也不做
	 */
	doNothing: function () {
		return;
	},

	getItemCheckbox: function (item) {
		return $(item.children().get(0)).children().get(0); // 比较笨的办法
	},

	/*
	 * 详细信息模式时，当鼠标移到项目上的操作
	 */
	detailViewItemOver: function (item) {
		var detailViewItem = $(item);
		detailViewItem.addClass("selected");
	},

	/*
	 * 详细信息模式时，当鼠标移出项目的操作
	 */
	detailViewItemOut: function (item) {
		var detailViewItem = $(item);
		var checkBox = FileManager.getItemCheckbox(detailViewItem);
		if (checkBox.checked != true) {
			detailViewItem.removeClass("selected");
		}
	},

	/*
	 * 详细信息模式时，当鼠标点击项目的操作
	 */
	detailViewItemClicked: function (item) {
		var detailViewItem = $(item);
		var checkBox = FileManager.getItemCheckbox(detailViewItem);
		if (checkBox.checked) {
			$(checkBox).removeAttr("checked");
		} else {
			$(checkBox).attr("checked", "checked");
			detailViewItem.addClass("selected");
		}
		FileManager.viewItemCheck();
	},

	/*
	 * 项目选择改变
	 */
	viewItemCheck: function () {
		FileManager.setButton("toolbarCut", "images/toolbar-cut.png",
			FileManager.doNothing, "disable", "");
		FileManager.setButton("toolbarCopy", "images/toolbar-copy.png",
			FileManager.doNothing, "disable", "");
		FileManager.setButton("toolbarRename",
			"images/toolbar-rename.png", FileManager.doNothing,
			"disable", "");
		FileManager.setButton("toolbarDelete",
			"images/toolbar-delete.png", FileManager.doNothing,
			"disable", "");

		var count = FileManager.inputChecks.length;
		var checkedItemsCount = 0;
		FileManager.selectedItems = new Array();

		for (var i = 0; i < count; i++) {
			var checkBox = FileManager.inputChecks.get(i);
			var item = $(checkBox.parentNode.parentNode); // CheckBox 对应的项目
			if (checkBox.checked) {
				checkedItemsCount++;
				FileManager.selectedItems.push(checkBox.name);
				item.addClass("selected");
			} else {
				item.removeClass("selected");
			}
		}

		if (checkedItemsCount > 0) {
			FileManager.setButton("toolbarCut", "images/toolbar-cut.png",
				FileManager.clickCut, "", "disable");
			FileManager.setButton("toolbarCopy", "images/toolbar-copy.png",
				FileManager.clickCopy, "", "disable");
			FileManager.setButton("toolbarDelete", "images/toolbar-delete.png",
				FileManager.clickDelete, "", "disable");
			if (checkedItemsCount == 1) {
				FileManager.setButton("toolbarRename",
					"images/toolbar-rename.png", FileManager.clickRename,
					"", "disable");
			}
		}
	},

	/*
	 * 设置按钮
	 */
	setButton: function (className, src, clickFunc, addClass, removeClass) {
		var buttons = $("div#toolbar .toolbarButton");

		for (var i = 0; i < buttons.length; i++) {
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
	},

	/*
	 * 点击了重命名的操作
	 */
	clickRename: function () {
		FileManager.setOldname();
		FileManager.displayFuncDialog("func/post.func.php", "rename", "rename",
			null);
	},

	/*
	 * 点击了新建目录的操作
	 */
	clickNewFolder: function () {
		// alert("newfolder");
		FileManager.displayFuncDialog("func/post.func.php", "newfolder",
			"new folder", null);
	},

	/*
	 * 点击了剪切的操作
	 */
	clickCut: function () {
		// alert("cut");
		FileManager.sendAjaxOper("cut");
	},

	/*
	 * 点击了复制的操作
	 */
	clickCopy: function () {
		FileManager.sendAjaxOper("copy");
	},

	/*
	 * 点击了粘贴的操作
	 */
	clickPaste: function () {
		var subdir = $("input#subdir").attr("value");
		var returnURL = $("input#return").val();

		FileManager.displayFuncDialog("", "waiting",
			"waiting", null);

		$.post("func/post.func.php", {
			"oper": "paste",
			"subdir": subdir,
			"return": returnURL
		}, function () {
			// alert(data);
			window.location.reload();
		});
		/*
		 * $.get("func/paste.ajax.php?subdir=" + subdir + "&return=" +
		 * returnURL, function(data) { // alert(data); window.location.reload();
		 * });
		 */
	},

	/*
	 * 点击了删除的操作
	 */
	clickDelete: function () {
		FileManager.displayFuncDialog("", "delete",
			"delete", null);
	},

	/*
	 * 确认删除后的操作
	 */
	doDelete: function () {
		// 准备界面
		var funcDelete = $("div#funcDelete");
		funcDelete.css("display", "none");

		FileManager.funcBg.get(0).onclick = FileManager.doNothing;
		FileManager.displayFuncDialog("", "waiting",
			"waiting", null);

		var itemsStr = FileManager.selectedItems.join("|");

		// var subdir = $("input#subdir").val();

		$.post("func/post.func.php", {
			"oper": "delete",
			"items": itemsStr,
			"noredirect": "noredirect"
		}, function () {
			// alert(data);
			window.location.reload();
		});
	},

	/*
	 * 点击了上传的操作
	 */
	clickUpload: function () {
		FileManager.displayFuncDialog("func/post.func.php", "upload", "upload",
			null);
	},

	funcSubmit: function () {
		FileManager.displayWaiting();
	},

	/*
	 * 点击了全选的操作
	 */
	selectAll: function () {
		var count = FileManager.inputChecks.length;

		for (var i = 0; i < count; i++) {
			var checkBox = $(FileManager.inputChecks.get(i));
			checkBox.attr("checked", "checked");
		}

		FileManager.viewItemCheck();
	},

	/*
	 * 点击了取消选择的操作
	 */
	deselect: function () {
		var count = FileManager.inputChecks.length;

		for (var i = 0; i < count; i++) {
			var checkBox = $(FileManager.inputChecks.get(i));
			checkBox.removeAttr("checked");
		}

		FileManager.viewItemCheck();
	},

	/*
	 * 设置显示排序的箭头
	 */
	setSortArrow: function (name, order) {
		FileManager.sortName = name;
		FileManager.sortOrder = order;
	},

	/*
	 * 设置为搜索模式
	 */
	setSearchMode: function (isSearch) {
		FileManager.isSearch = isSearch;
	},

	/*
	 * 获得消息
	 */
	getMessage: function () {
		$.get("func/getmessage.ajax.php", function (data) {
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

					phpfmMessage.slideToggle();
				}

				phpfmMessage.click(FileManager.closeMessage);
				clearTimeout(FileManager.delayID);
				FileManager.delayID = setTimeout(function () {
						FileManager.closeMessage();
					}, 5000);
			}
		});

	},

	/*
	 * 关闭消息
	 */
	closeMessage: function () {
		$("#phpfmMessage").slideToggle();
		clearTimeout(FileManager.delayID);
	},

	/*
	 * 获得左边距，使得输入部分居中
	 */
	getLeftMargin: function () {
		var viewWidth = document.documentElement.clientWidth;
		var leftMargin = (viewWidth - 420) / 2; // 居中
		return leftMargin;
	},

	/*
	 * 发送 ajax
	 */
	sendAjaxOper: function (oper) {

		var itemsStr = FileManager.selectedItems.join("|");

		// var subdir = $("input#subdir").val();

		$.post("func/post.func.php", {
			"oper": oper,
			"items": itemsStr
		}, function (data) {
			if (data == "ok" && FileManager.isSearch == false) {
				FileManager.setButton("toolbarPaste",
					"images/toolbar-paste.png", FileManager.clickPaste, "",
					"disable");
			} else {
				FileManager.setButton("toolbarPaste",
					"images/toolbar-paste.png",
					FileManager.doNothing, "disable", "");
			}
		});

		setTimeout(function () {
			FileManager.getMessage();
		}, 500);
	},

	/*
	 * 设置修改名称时的原名称
	 */
	setOldname: function () {
		var oldPathInput = $("input#renamePath");
		var oldnameInput = $("input#oldname");
		var newnameInput = $("input#newname");
		var path = FileManager.selectedItems[0];
		oldPathInput.attr("value", path);
		var oldname = path.substring(path.lastIndexOf("/") + 1, path.length);
		oldnameInput.attr("value", oldname); // 显示原文件名
		newnameInput.attr("value", oldname);
	},

	/*
	 * 清除修改名称输入框中的原名称
	 */
	cleanOldname: function () {
		var oldnameInput = FileManager.funcDialog.body.find("input#oldname");
		var newnameInput = FileManager.funcDialog.body.find("input#newname");
		oldnameInput.attr("value", ""); // 显示原文件名
		newnameInput.attr("value", "");
	},

	/*
	 * 初始化 Func
	 */
	initFuncDialog: function () {
		FileManager.funcBg = $("div#funcBg");

		FileManager.funcDialog.body = $("div#funcDialog");
		FileManager.funcDialog.header = FileManager.funcDialog.body.children("div.divHeader");
		FileManager.funcDialog.divInput = FileManager.funcDialog.body.children("div#divInput");
		FileManager.funcDialog.divDelete = FileManager.funcDialog.body.children("div#divDelete");
		FileManager.funcDialog.divAudio = FileManager.funcDialog.body.children("div#divAudio");
		FileManager.funcDialog.divWaiting = FileManager.funcDialog.body.children("div#divWaiting");

		// 准备标题字符串
		var rawTitles = FileManager.funcDialog.header.children("span");
		rawTitles = $(rawTitles[0]);
		rawTitles = rawTitles.html();
		rawTitles = rawTitles.split("|");

		FileManager.multilanTitles = new Array();
		var count = rawTitles.length;
		var rawTitle,
		key,
		value;
		for (var i = 0; i < count; ++i) {
			rawTitle = rawTitles[i];
			rawTitle = rawTitle.split(":");
			key = rawTitle[0];
			value = rawTitle[1];
			FileManager.multilanTitles[key] = value;
		}

		var funcClose = FileManager.funcDialog.header.children(".funcClose");
		var count = funcClose.length;
		for (var i = 0; i < count; i++) {
			funcClose.get(i).onclick = FileManager.closeFunc;
		}
	},

	displayFuncPart: function (part) {
		FileManager.funcDialog.divInput.addClass("hidden");
		FileManager.funcDialog.divDelete.addClass("hidden");
		FileManager.funcDialog.divAudio.addClass("hidden");
		FileManager.funcDialog.divWaiting.addClass("hidden");

		part.removeClass("hidden");
	},

	displayInputPart: function (part) {
		FileManager.funcDialog.divInput.find("div#divReqInput").addClass("hidden");
		FileManager.funcDialog.divInput.find("div#divUpload").addClass("hidden");
		FileManager.funcDialog.divInput.find("div#divLogin").addClass("hidden");
		FileManager.funcDialog.divInput.find("div#divLogout").addClass("hidden");

		part.removeClass("hidden");
	},

	/*
	 * 显示 Func 输入的半透明背景
	 */
	displayFuncBg: function (canClose, closeableBkg) {
		if (canClose) {
			FileManager.funcBg.get(0).onclick = closeableBkg ? FileManager.closeFunc : FileManager.doNothing;
			FileManager.funcDialog.header.find(".funcClose").css("display", "block");
		} else {
			FileManager.funcBg.get(0).onclick = FileManager.doNothing;
			FileManager.funcDialog.header.find(".funcClose").css("display", "none");
		}
		FileManager.funcBg.css("height", document.documentElement.scrollHeight + "px");
		FileManager.funcBg.css("display", "block");
	},

	/*
	 * 显示 Func 输入部分
	 */
	displayFuncDialog: function (action, oper, title, data) {
		var funcDialog = FileManager.funcDialog.body;
		var divHeader = FileManager.funcDialog.header;
		var divInput = FileManager.funcDialog.divInput;
		var divDelete = FileManager.funcDialog.divDelete;
		var divAudio = FileManager.funcDialog.divAudio;
		var divWaiting = FileManager.funcDialog.divWaiting;

		var titleSpan = divHeader.find("span");
		titleSpan.html(FileManager.multilanTitles[title]);
		funcDialog.css("left", FileManager.getLeftMargin() + "px");

		switch (oper) {
		case "newfolder":
		case "rename":
		case "upload":
		case "login":
		case "logout":
			FileManager.displayFuncPart(divInput);

			var operInput = divInput.find("input#oper");
			operInput.val(oper);
			var form = divInput.find("form");
			form.attr("action", action);

			if (oper == "upload") {
				FileManager.displayInputPart(divInput.find("div#divUpload"));
			} else if (oper == "login") {
				FileManager.displayInputPart(divInput.find("div#divLogin"));
			} else if (oper == "logout") {
				FileManager.displayInputPart(divInput.find("div#divLogout"));
			} else {
				FileManager.displayInputPart(divInput.find("div#divReqInput"));
			}

			FileManager.displayFuncBg(true, true);
			funcDialog.fadeIn();

			if (oper != "upload" && oper != "login") {
				divInput.find("input#newname").focus();
				divInput.find("input#newname").get(0).select();
			} else if (oper == "login") {
				divInput.find("input#username").focus();
				divInput.find("input#username").get(0).select();
			}
			break;
		case "delete":
			FileManager.displayFuncPart(divDelete);

			FileManager.displayFuncBg(true, true);
			funcDialog.fadeIn();
			break;
		case "audio":
			FileManager.displayFuncPart(divAudio);

			var audioLink = data.link;

			var audioControl = divAudio.find("audio");
			audioControl.attr("src", audioLink);

			var divLink = divAudio.find("div#link");
			divLink.html("<a href=\"" + audioLink + "\">"
				 + data.title + "</a>");

			FileManager.displayFuncBg(true, false);
			funcDialog.fadeIn();
			break;
		case "waiting":
			FileManager.displayFuncPart(divWaiting);

			FileManager.displayFuncBg(false, false);
			funcDialog.fadeIn();
			break;
		}
	},

	/*
	 * 关闭 Func 部分
	 */
	closeFunc: function () {
		if (FileManager.funcDialog.body.is(":visible"))
			FileManager.funcDialog.body.fadeOut();

		FileManager.cleanOldname();
		FileManager.funcBg.css("display", "none");

		var divAudioPlayer = $("div#divAudioPlayer");
		if (divAudioPlayer.is(":visible")) {
			var audioControl = divAudioPlayer.find("audio");
			audioControl[0].pause();
		}
	},

	displayWaiting: function () {
		var funcInput = $("div#divInput");
		funcInput.css("display", "none");

		FileManager.funcBg.get(0).onclick = FileManager.doNothing;
		FileManager.displayFuncDialog("", "waiting",
			"waiting", null);
	},

	changeMainViewListHeight: function () {
		// 自适应 mainViewList 高度
		var mainViewList = $("div#mainViewList");
		var mainViewListOffset = mainViewList.offset();
		var footerHeight = $("div#footer").height();
		var windowHeight = $(window).height();
		var mainViewListHeight;

		if (FileManager.isIE && $.browser.version < 8) {
			return;
		} else {
			var mainViewListMarginBottom = 30;
			if (FileManager.isMobile) {
				mainViewListMarginBottom = 2;
			}
			mainViewListHeight = windowHeight - mainViewListOffset.top
				 - footerHeight - mainViewListMarginBottom;
			mainViewListHeight = mainViewListHeight > FileManager.miniMainViewHeight ? mainViewListHeight
				 : FileManager.miniMainViewHeight;
			mainViewList.css("height", mainViewListHeight + "px");
			mainViewList.css("overflow", "auto");
		}
	},

	toolbarButtonMouseIn: function () {
		if (!$(this).hasClass("disable")) {
			$(this).addClass("buttonHover");
		}
	},

	toolbarButtonMouseOut: function () {
		$(this).removeClass("buttonHover");
	},

	/*
	 * 准备工具栏
	 */
	initToolbar: function () {
		var buttons = $("div#toolbar .toolbarButton").add("div#toolbar .toolbarSmallButton");

		buttons.filter(".toolbarRefresh").click(function () {
			window.location.reload(); // 刷新
		});

		// buttons.filter(".toolbarSelectAll").click(FileManager.selectAll); //
		// 全选
		// buttons.filter(".toolbarDeselect").click(FileManager.deselect); //
		// 取消选择
		buttons.filter(".toolbarPaste").hasClass("disable") ? null :
		buttons.filter(".toolbarPaste").click(FileManager.clickPaste); // 粘贴

		if (FileManager.isSearch) {
			// 搜索模式
			buttons.filter(".toolbarUp").addClass("disable"); // 向上
			buttons.filter(".toolbarNewFolder").addClass("disable"); // 新建目录
			buttons.filter(".toolbarUpload").addClass("disable"); // 上传
		} else {
			// 浏览模式
			buttons.filter(".toolbarNewFolder").click(
				FileManager.clickNewFolder); // 新建目录
			buttons.filter(".toolbarUpload").click(FileManager.clickUpload); // 上传
		}

		buttons.hover(FileManager.toolbarButtonMouseIn,
			FileManager.toolbarButtonMouseOut); // 按钮 hover 时的效果

		$("#toolbar form#searchForm input[type='submit']").hover(
			FileManager.toolbarButtonMouseIn,
			FileManager.toolbarButtonMouseOut); // 按钮 hover 时的效果

		$("#mainView .header span #checkSelectAll").click(function () {
			if (this.checked)
				FileManager.selectAll();
			else
				FileManager.deselect();
		});

		// more 按钮
		var buttonMore = buttons.filter(".toolbarMore");
		if (buttonMore.hasClass("little")) {
			buttonMore.parent().find(".toolbarHiddenable").hide();
			buttonMore.find("img").attr("src", "images/toolbar-arrow-right.gif");
		}
		buttonMore.click(function () {
			var img = $(this).find("img");
			var part = $(this).parent().find(".toolbarHiddenable");
			if (part.is(":visible")) {
				part.fadeOut("fast");
				img.attr("src", "images/toolbar-arrow-right.gif");
				FileManager.setCookie("toolbar", "little");
			} else {
				part.fadeIn("fast");
				img.attr("src", "images/toolbar-arrow-left.gif");
				FileManager.setCookie("toolbar", "full");
			}
		});
	},

	/*
	 * 准备主视图
	 */
	initMainView: function () {
		FileManager.changeMainViewListHeight();
		$(window).resize(function () {
			FileManager.changeMainViewListHeight();
		});

		var detailViewItems = $("ul#detailView");
		if (detailViewItems.length > 0) {
			// 是详细视图
			var items = detailViewItems.children("li");
			var count = items.length;
			for (var i = 0; i < count; i++) {
				var item = $(items.get(i));
				if (!item.hasClass("empty")) {
					var jsObj = item.get(0);
					jsObj.onmouseover = function () {
						FileManager.detailViewItemOver(this);
					};
					jsObj.onmouseout = function () {
						FileManager.detailViewItemOut(this);
					};
					jsObj.onclick = function () {
						FileManager.detailViewItemClicked(this);
					};
				}
				item.children("a")[0].onclick = function (e) {
					jqCommon.stopBubble(e);
				};
			}

			detailViewItems.show();
		}

		FileManager.inputChecks = $("input.inputCheck");
		FileManager.inputChecks.onclick = function (e) {
			FileManager.viewItemCheck();
			jqCommon.stopBubble(e);
		};
		var count = FileManager.inputChecks.length;
		for (var i = 0; i < count; i++) {
			var check = FileManager.inputChecks.get(i);
			check.onclick = function (e) {
				FileManager.viewItemCheck();
				jqCommon.stopBubble(e);
			};
		}
		FileManager.viewItemCheck();
	},

	/*
	 * 准备 AudioPlayer
	 */
	initAudioPlayer: function () {
		$("a.audioPlayer").click(function () {
			var audioLink = $(this).attr("href");
			var audioTitle = $(this).attr("title");
			FileManager.displayFuncDialog("", "audio",
				"audio", {
				link: audioLink,
				title: audioTitle
			});
			return false;
		});
	},

	initMediaPreview: function () {
		// lightbox
		$('a.lightboxImg').lightBox({
			overlayOpacity: 0.5,
			autoAdapt: true
		});

		// AudioPlayer
		FileManager.initAudioPlayer();
	},

	initUploadify: function () {
		var playerVersion = swfobject.getFlashPlayerVersion(); // returns a JavaScript object
		if (playerVersion.major == 0) {
			return;
		}
		var sessionId = FileManager.getCookie('PHPSESSID');
		var subdir = encodeURIComponent($("input#subdir").attr("value"));
		var returnURL = encodeURIComponent($("input#return").val());
		var returnURLdecoded = decodeURIComponent($("input#return").val());
		$('#uploadFile').uploadify({
			'uploader': 'images/uploadify.swf',
			'script': 'func/post.func.php',
			'cancelImg': 'images/cancel.png',
			'auto': true,
			'multi': true,
			'fileDataName': 'uploadFile',
			'scriptData': {
				'session': sessionId,
				'oper': 'upload',
				'subdir': subdir,
				'return': returnURL,
				'ajax': 'ajax'
			},
			onAllComplete: function () {
				window.location.href = returnURLdecoded;
			}
		});
	},

	initUploadHtml5: function () {
		var infoText = "Drag and drop a file here to upload.";
		var uploadFileInput = $('#uploadFile');
		uploadFileInput.hide();

		var uploadFileInfo = $('#uploadFileInfo');
		uploadFileInfo.html(infoText);
		uploadFileInfo.addClass("dropUpload");

		uploadFileInput.change(function () {
			var filePath = uploadFileInput.val();
			var fileName = filePath.replace(/^.*[\\\/]/, '')
				uploadFileInfo.html(fileName);
		});

		var uploadFileInfoRaw = uploadFileInfo[0];
		uploadFileInfoRaw.ondragover = function () {
			uploadFileInfo.addClass("dropFile");
			return false;
		};
		uploadFileInfoRaw.ondragleave = function () {
			uploadFileInfo.removeClass("dropFile");
			return false;
		};
		uploadFileInfoRaw.ondragend = function () {
			uploadFileInfo.removeClass("dropFile");
			return false;
		};
		uploadFileInfoRaw.ondrop = function (e) {
			uploadFileInfo.removeClass("dropFile");
			e.preventDefault();
			var droppedFile = e.dataTransfer.files[0];

			FileManager.displayWaiting();

			var sessionId = FileManager.getCookie('PHPSESSID');
			var subdir = $("input#subdir").attr("value");
			var returnURL = $("input#return").val();
			var returnURLdecoded = decodeURIComponent($("input#return").val());

			var xhrUpload = new XMLHttpRequest();
			xhrUpload.open('POST', 'func/post.func.php');

			xhrUpload.onload = function () {
				window.location.href = returnURLdecoded;
			};

			var form = new FormData();
			form.append('session', sessionId);
			form.append('oper', 'upload');
			form.append('ajax', 'ajax');
			form.append('subdir', subdir);
			form.append('return', returnURL);
			form.append('uploadFile', droppedFile);

			xhrUpload.send(form);
		}

	},

	/*
	 * 点击了登录的操作
	 */
	clickLogin: function () {
		FileManager.displayFuncDialog("func/post.func.php", "login", "user",
			null);
	},

	/*
	 * 点击了登出的操作
	 */
	clickLogout: function () {
		FileManager.displayFuncDialog("func/post.func.php", "logout", "user",
			null);
	},

	initUserMng: function () {
		$("a#linkLogin").click(FileManager.clickLogin);
		$("a#linkLogout").click(FileManager.clickLogout);
	}

}

/*
 * 初始化
 */
FileManager.init = function () {
	// fix Chrome back issue
	$.ajaxSetup({
		cache: false
	});

	FileManager.isIE = $.browser.msie ? true : false;
	// alert($.browser.version);

	var browserVendor = (navigator.userAgent || navigator.vendor || window.opera);
	FileManager.isMobile = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(browserVendor) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(browserVendor.substr(0, 4));
	//alert(browserVendor + ", " + FileManager.isMobile);

	if (FileManager.isMobile) {
		$("body").addClass("mobile");
	}

	var str = "#mainView > .header > span." + FileManager.sortName + " > a";
	var item = $(str);
	item.addClass("sort" + FileManager.sortOrder);

	// FileManager.initFullPath();
	jqMenu.setup({
		menuItemsSelector: ".menuContainer",
		menuButtonSelector: ".menuButton",
		subMenuSelector: ".subMenu"
		//inlineShadow : "transparent url('images/shadow.png') no-repeat right bottom"
	});
	jqMenu.init();

	FileManager.initToolbar();
	FileManager.initMainView();
	FileManager.initFuncDialog();
	FileManager.initUserMng();
	FileManager.getMessage();
	FileManager.initMediaPreview();
	//FileManager.initUploadify();
	FileManager.initUploadHtml5();

	jqCommon.setPlaceholder("#searchForm", "#q", "搜索");
	jqCommon.setVerify("#searchForm", "#q", "empty", null, null);

};

// 运行准备函数
$(FileManager.init);
