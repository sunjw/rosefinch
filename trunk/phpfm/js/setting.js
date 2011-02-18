var Setting = {
	funcBg : null, 
	funcDialog : null, 
	
	/**
	 * 无功能函数
	 */
	dummy : function () {
		return;
	}, 
	
	/**
	 * 准备功能对话框
	 */
	initFuncDialog : function (title, oper, redirect, closable) {
		if (Setting.funcBg == null) {
			Setting.funcBg = $("<div/>");
			Setting.funcBg.attr("id", "funcBg");
			$("#content").append(Setting.funcBg);
		}
		if (Setting.funcDialog == null) {
			Setting.funcDialog = $("<div/>");
			Setting.funcDialog.attr("id", "funcDialog");
			var divHeader = $("<div/>");
			divHeader.addClass("divHeader");
			Setting.funcDialog.append(divHeader);
			var divInput = $("<div/>");
			divInput.attr("id", "divInput");
			divInput.addClass("container");
			var form = $("<form/>");
			form.attr({
					action : "../func/post.func.php", 
					method : "post", 
					enctype : "multipart/form-data"
				});
			divInput.append(form);
			Setting.funcDialog.append(divInput);
			$("#content").append(Setting.funcDialog);
		}
		
		Setting.funcDialog.find(".divHeader").html("").append($("<span/>").html(title));
		if (closable) {
			Setting.funcDialog.find(".divHeader").append(
				$("<a/>").attr("href", "javascript:;").addClass("funcClose").append(
					$("<img/>").attr({
							alt : "Close", 
							src : "../images/close.gif", 
							border : "0"
						})).click(Setting.closeFunc));
			Setting.funcDialog.addClass("closable");
			Setting.funcBg.click(Setting.closeFunc);
		} else {
			Setting.funcDialog.removeClass("closable");
			Setting.funcBg.click(Setting.dummy);
		}
		
		var form = Setting.funcDialog.find("form");
		form.html("");
		form.append($("<input/>").attr({
					type : "hidden", 
					id : "oper", 
					name : "oper"
				}).val(oper));
		if (redirect) 
			form.append($("<input/>").attr({
						type : "hidden", 
						id : "return", 
						name : "return"
					}).val(Strings['return']));
		else 
			form.append($("<input/>").attr({
						type : "hidden", 
						name : "noredirect"
					}).val("noredirect"));
		
		return form;
	}, 
	
	/**
	 * 显示对话框提交部分
	 */
	displaySubmit : function () {
		if (Setting.funcDialog == null) 
			return;
		var div = $("<div/>").addClass("rightAlign");
		div.append($("<input/>").attr("type", "submit").val(Strings['OK']));
		if (Setting.funcDialog.hasClass("closable")) {
			var buttonCancel = $("<input/>").attr("type", "button").val(Strings['Cancel']);
			buttonCancel.click(Setting.closeFunc);
			div.append(buttonCancel);
		}
		Setting.funcDialog.find("form").append(div);
	}, 
	
	/**
	 * 显示功能对话框
	 */
	displayFuncDialog : function () {
		if (Setting.funcBg == null || Setting.funcDialog == null) 
			return;
		
		Setting.funcBg.css("height", document.documentElement.scrollHeight + "px");
		Setting.funcBg.css("display", "block");
		Setting.funcDialog.css("left", (document.documentElement.clientWidth - 420) / 2 + "px");
		Setting.funcDialog.fadeIn("fast");
	}, 
	
	/**
	 * 关闭功能对话框
	 */
	closeFunc : function () {
		if (Setting.funcDialog.is(":visible")) 
			Setting.funcDialog.fadeOut();
		
		Setting.funcBg.css("display", "none");
	}, 
	
	/**
	 * 索引文件
	 */
	indexfile : function () {
		var button = $("input#buttonIndexfile");
		var result = $("div#result");
		button.attr("disabled", "disabled");
		
		$.get("../func/indexfiles.func.php", function (data) {
				if (data == "ok") {
					result.html("OK");
				} else {
					result.html("Failed");
				}
				button.removeAttr("disabled");
			});
		
	}, 
	
	/**
	 * 显示登录对话框
	 */
	displayLogin : function () {
		var form = Setting.initFuncDialog(Strings['User'], "login", true, false);
		var divLogin = $("<div/>");
		divLogin.attr("id", "divLogin");
		divLogin.append(
			$("<div/>").append(
				$("<label/>").attr("for", "username").html(Strings['Username:'])).append(
				$("<input/>").attr({
						type : "text", 
						name : "username", 
						size : "40", 
						maxlength : "128"
					}).val("")));
		divLogin.append(
			$("<div/>").append(
				$("<label/>").attr("for", "password").html(Strings['Password:'])).append(
				$("<input/>").attr({
						type : "password", 
						name : "password", 
						size : "40", 
						maxlength : "128"
					}).val("")));
		divLogin.append(
			$("<div/>").append($("<a/>").attr("href", "../").html(Strings['Never mind...'])));
		form.append(divLogin);
		Setting.displaySubmit();
		
		Setting.displayFuncDialog();
	}, 
	
	/**
	 * 显示登出对话框
	 */
	displayLogout : function () {
		var form = Setting.initFuncDialog(Strings['User'], "logout", true, true);
		form.find("input#return").val("../");
		var divLogout = $("<div/>");
		divLogout.attr("id", "divLogout");
		divLogout.append($("<div/>").html(Strings['Are you sure to logout?']).addClass("center"));
		form.append(divLogout);
		Setting.displaySubmit();
		
		Setting.displayFuncDialog();
	}, 
	
	/**
	 * 初始化 Setting 部分的 js
	 */
	init : function () {
		var buttonIndex = $("input#buttonIndexfile");
		buttonIndex.click(Setting.indexfile);
		buttonIndex.removeAttr("disabled");
		
		var linkLogout = $("a#linkLogout");
		linkLogout.click(Setting.displayLogout);
	}
	
};

$(Setting.init);
 