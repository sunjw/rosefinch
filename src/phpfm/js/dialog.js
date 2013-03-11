var Dialog = {
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
	initFuncDialog : function (title, oper, redirect, closable, secondaryFolder) {
		if (Dialog.funcBg == null) {
			Dialog.funcBg = $("<div/>");
			Dialog.funcBg.attr("id", "funcBg");
			$("#content").append(Dialog.funcBg);
		}
		if (Dialog.funcDialog == null) {
			Dialog.funcDialog = $("<div/>");
			Dialog.funcDialog.attr("id", "funcDialog");
			var divHeader = $("<div/>");
			divHeader.addClass("divHeader");
			Dialog.funcDialog.append(divHeader);
			var divInput = $("<div/>");
			divInput.attr("id", "divInput");
			divInput.addClass("container");
			var form = $("<form/>");
			var actionUrl = secondaryFolder ? "../func/post.func.php" : "func/post.func.php";
			form.attr({
				action : actionUrl,
				method : "post",
				enctype : "multipart/form-data"
			});
			divInput.append(form);
			Dialog.funcDialog.append(divInput);
			$("#content").append(Dialog.funcDialog);
		}

		Dialog.funcDialog.find(".divHeader").html("").append($("<span/>").html(title));
		var imgSrc = secondaryFolder ? "../images/close.png" : "images/close.png";
		if (closable) {
			Dialog.funcDialog.find(".divHeader").append(
				$("<a/>").attr("href", "javascript:;").addClass("funcClose").append(
					$("<img/>").attr({
						alt : "Close",
						src : imgSrc,
						border : "0"
					})).click(Dialog.closeFunc));
			Dialog.funcDialog.addClass("closable");
			Dialog.funcBg.click(Dialog.closeFunc);
		} else {
			Dialog.funcDialog.removeClass("closable");
			Dialog.funcBg.click(Dialog.dummy);
		}

		var form = Dialog.funcDialog.find("form");
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

		form.unbind("submit");

		return form;
	},

	setFocus : function (o) {
		o.focus();
		o.get(0).select();
	},

	/**
	 * 显示对话框提交部分
	 */
	displaySubmit : function () {
		if (Dialog.funcDialog == null)
			return;
		var div = $("<div/>").addClass("funcBtnLine");
		div.append($("<input/>").attr("type", "submit").val(Strings['OK']));
		if (Dialog.funcDialog.hasClass("closable")) {
			var buttonCancel = $("<input/>").attr("type", "button").val(Strings['Cancel']);
			buttonCancel.click(Dialog.closeFunc);
			div.append(buttonCancel);
		}
		Dialog.funcDialog.find("form").append(div);
	},

	/**
	 * 显示功能对话框
	 */
	displayFuncDialog : function () {
		if (Dialog.funcBg == null || Dialog.funcDialog == null)
			return;

		Dialog.funcBg.css("height", document.documentElement.scrollHeight + "px");
		Dialog.funcBg.css("display", "block");
		Dialog.funcDialog.css("left", (document.documentElement.clientWidth - 420) / 2 + "px");
		Dialog.funcDialog.fadeIn("fast");
	},

	/**
	 * 关闭功能对话框
	 */
	closeFunc : function () {
		var funcAudioPlayer = Dialog.funcDialog.find("div#funcAudioPlayer");
		if (funcAudioPlayer.length && funcAudioPlayer.is(":visible")) {
			AudioPlayer.close("divAudioPlayer"); // IE 9 has a bug on this call
			//funcAudioPlayer.fadeOut();
		}

		if (Dialog.funcDialog.is(":visible"))
			Dialog.funcDialog.fadeOut();

		Dialog.funcBg.css("display", "none");
	}

};
