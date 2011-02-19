var Setting = {
	funcBg : null, 
	funcDialog : null, 
	
	users : null, 
	oldTable : "", 
	working : false, 
	
	/**
	 * 无功能函数
	 */
	dummy : function () {
		return;
	}, 
	
	setStat : function (working) {
		if (working) {
			$("span#statUserMng").html("&nbsp;|&nbsp;" + Strings['Working...']);
		} else {
			$("span#statUserMng").html("&nbsp;|&nbsp;" + Strings['Done']);
		}
		Setting.working = working;
	}, 
	
	/**
	 * 获得消息
	 */
	getMessage : function () {
		$.get("../func/getmessage.ajax.php", function (data) {
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
				}
			});
		
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
		
		form.unbind("submit");
		
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
		if (Setting.working) 
			return;
		
		var form = Setting.initFuncDialog(Strings['User'], "logout", true, true);
		form.find("input#return").val("../");
		var divLogout = $("<div/>");
		divLogout.attr("id", "divLogout");
		divLogout.append($("<div/>").html(Strings['Are you sure to logout?']).addClass("center"));
		form.append(divLogout);
		Setting.displaySubmit();
		
		Setting.displayFuncDialog();
	}, 
	
	loadUsers : function (table) {
		Setting.setStat(true);
		$.post("../func/post.func.php", {
				oper : "userlist"
			}, function (data) {
				table.html(Setting.oldTable);
				data = $.parseJSON(data);
				Setting.users = new Array();
				var length = data.length;
				for (var i = 0; i < length; ++i) {
					var user = data[i];
					Setting.users[user.id] = user;
					var row = $("<tr></tr>");
					if (i % 2) {
						row.addClass("odd");
					}
					row.append($("<td></td>").html(user.id));
					row.append($("<td></td>").html(user.username));
					row.append($("<td></td>").html(user.privilege));
					if (user.username == "root") 
						row.append($("<td></td>").html(""));
					else {
						var html = "<a href='javascript:Setting.modifyUser(" + user.id + ")'>" + Strings['Modify'] + "</a>";
						html += "&nbsp;|&nbsp;<a href='javascript:Setting.deleteUser(" + user.id + ")'>" + Strings['Delete'] + "</a>";
						row.append($("<td></td>").html(html));
					}
					
					table.append(row);
				}
				Setting.setStat(false);
			});
	}, 
	
	addUser : function () {
		//alert("add");
		if (Setting.working) 
			return;
		
		var form = Setting.initFuncDialog(Strings['Add'], "adduser", true, true);
		var divAddUser = $("<div/>");
		divAddUser.attr("id", "divAddUser");
		divAddUser.append(
			$("<div/>").append(
				$("<label/>").attr("for", "username").html(Strings['Username:'])).append(
				$("<input/>").attr({
						type : "text", 
						name : "username", 
						size : "40", 
						maxlength : "128"
					}).val("")));
		divAddUser.append(
			$("<div/>").append(
				$("<label/>").attr("for", "password").html(Strings['Password:'])).append(
				$("<input/>").attr({
						type : "password", 
						name : "password", 
						size : "40", 
						maxlength : "128"
					}).val("")));
		var select = $("<select/>").attr("name", "privilege");
		select.append(
			$("<option/>").val(25).html("User")).append(
			$("<option/>").val(75).html("Administrator")).append(
			$("<option/>").val(100).html("Root"));
		divAddUser.append(
			$("<div/>").append($("<label/>").attr("for", "privilege").html(Strings['Privilege:'])).append(select));
		form.append(divAddUser);
		Setting.displaySubmit();
		
		Setting.displayFuncDialog();
		
		form.submit(function () {
				Setting.closeFunc();
				Setting.setStat(true);
				$.post("../func/post.func.php", {
						oper : $("input[name='oper']").val(), 
						noredirect : "noredirect", 
						username : $("input[name='username']").val(), 
						password : $("input[name='password']").val(), 
						privilege : $("select[name='privilege']").val() 
					}, function () {
						Setting.getMessage();
						Setting.loadUsers($("#tableUserMng"));
					});
				return false;
			});
	}, 
	
	modifyUser : function (id) {
		if (Setting.working) 
			return;
		
		var user = Setting.users[id];
		var form = Setting.initFuncDialog(Strings['Modify'], "modiuser", true, true);
		var divModifyUser = $("<div/>");
		divModifyUser.attr("id", "divModifyUser");
		divModifyUser.append(
			$("<div/>").append(
				$("<label/>").attr("for", "username").html(Strings['Username:'])).append(
				$("<input/>").attr({
						type : "text", 
						name : "username", 
						size : "40", 
						maxlength : "128"
					}).val(user.username)));
		var select = $("<select/>").attr("name", "privilege");
		var option = $("<option/>").val(25).html("User");
		if (user.privilege == "User") 
			option.attr("selected", "selected");
		select.append(option);
		option = $("<option/>").val(75).html("Administrator");
		if (user.privilege == "Administrator") 
			option.attr("selected", "selected");
		select.append(option);
		option = $("<option/>").val(100).html("Root");
		if (user.privilege == "Root") 
			option.attr("selected", "selected");
		select.append(option);
		
		divModifyUser.append(
			$("<div/>").append($("<label/>").attr("for", "privilege").html(Strings['Privilege:'])).append(select));
		divModifyUser.append($("<input/>").attr({
					type : "hidden", 
					name : "id"
				}).val(id));
		form.append(divModifyUser);
		Setting.displaySubmit();
		
		Setting.displayFuncDialog();
		
		form.submit(function () {
				Setting.closeFunc();
				Setting.setStat(true);
				$.post("../func/post.func.php", {
						oper : $("input[name='oper']").val(), 
						noredirect : "noredirect", 
						id : $("input[name='id']").val(), 
						username : $("input[name='username']").val(), 
						privilege : $("select[name='privilege']").val() 
					}, function () {
						Setting.getMessage();
						Setting.loadUsers($("#tableUserMng"));
					});
				return false;
			});
	}, 
	
	deleteUser : function (id) {
		if (Setting.working) 
			return;
		
		var form = Setting.initFuncDialog(Strings['Delete'], "deluser", true, true);
		var divDelUser = $("<div/>");
		divDelUser.attr("id", "divDelUser");
		divDelUser.append($("<div/>").html(Strings['Are you sure to delete this user?'] + " \"" + Setting.users[id].username + "\"").addClass("center"));
		divDelUser.append($("<input/>").attr({
					type : "hidden", 
					name : "id"
				}).val(id));
		form.append(divDelUser);
		Setting.displaySubmit();
		
		Setting.displayFuncDialog();
		
		form.submit(function () {
				Setting.closeFunc();
				Setting.setStat(true);
				$.post("../func/post.func.php", {
						oper : $("input[name='oper']").val(), 
						noredirect : "noredirect", 
						id : $("input[name='id']").val() 
					}, function () {
						Setting.getMessage();
						Setting.loadUsers($("#tableUserMng"));
					});
				return false;
			});
	}, 
	
	initUserMng : function () {
		var tableUserMng = $("#tableUserMng");
		//alert(divUserMng.length);
		if (tableUserMng.length == 0) 
			return;
		
		Setting.oldTable = tableUserMng.html();
		$("input#addUser").click(Setting.addUser);
		Setting.loadUsers(tableUserMng);
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
		
		Setting.initUserMng();
		
		Setting.getMessage();
	}
	
};

$(Setting.init);
 