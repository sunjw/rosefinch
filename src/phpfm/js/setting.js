var Setting = {
	working: false,
	users: null,
	oldTable: "",

	setStat: function (working) {
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
	getMessage: function () {
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
	 * 索引文件
	 */
	indexfile: function () {
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
	displayLogin: function () {
		var form = Dialog.initFuncDialog(Strings['User'], "login", true, false, true);
		var divLogin = $("<div/>");
		divLogin.attr("id", "divLogin");
		var table = $("<table/>");
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "username").html(Strings['Username:']))).append(
				$("<td/>").append(
					$("<input/>").attr({
						type: "text",
						name: "username",
						size: "40",
						maxlength: "128"
					}).val(""))));
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "password").html(Strings['Password:']))).append(
				$("<td/>").append(
					$("<input/>").attr({
						type: "password",
						name: "password",
						size: "40",
						maxlength: "128"
					}).val(""))));
		divLogin.append(table);
		divLogin.append(
			$("<div/>").append($("<a/>").attr("href", "../").html(Strings['Never mind...'])));
		form.append(divLogin);
		Dialog.displaySubmit();

		Dialog.displayFuncDialog();
		Dialog.setFocus($("input[name='username']"));
	},

	/**
	 * 显示登出对话框
	 */
	displayLogout: function () {
		if (Setting.working)
			return;

		var form = Dialog.initFuncDialog(Strings['User'], "logout", true, true, true);
		form.find("input#return").val("../");
		var divLogout = $("<div/>");
		divLogout.attr("id", "divLogout");
		divLogout.append($("<div/>").html(Strings['Are you sure to logout?']).addClass("center"));
		form.append(divLogout);
		Dialog.displaySubmit();

		Dialog.displayFuncDialog();
	},

	loadUsers: function (table) {
		Setting.setStat(true);
		$.post("../func/post.func.php", {
			oper: "userlist"
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
				row.append($("<td></td>").html(user.permission));
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

	addUser: function () {
		//alert("add");
		if (Setting.working)
			return;

		var form = Dialog.initFuncDialog(Strings['Add'], "adduser", true, true, true);
		var divAddUser = $("<div/>");
		divAddUser.attr("id", "divAddUser");
		var table = $("<table/>");
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "username").html(Strings['Username:']))).append(
				$("<td/>").append(
					$("<input/>").attr({
						type: "text",
						name: "username",
						size: "40",
						maxlength: "128"
					}).val(""))));
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "password").html(Strings['Password:']))).append(
				$("<td/>").append(
					$("<input/>").attr({
						type: "password",
						name: "password",
						size: "40",
						maxlength: "128"
					}).val(""))));
		var select = $("<select/>").attr("name", "permission");
		select.append(
			$("<option/>").val(25).html("User")).append(
			$("<option/>").val(75).html("Administrator")).append(
			$("<option/>").val(100).html("Root"));
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "permission").html(Strings['Permission:']))).append(
				$("<td/>").append(select)));
		divAddUser.append(table);
		form.append(divAddUser);
		Dialog.displaySubmit();

		Dialog.displayFuncDialog();
		Dialog.setFocus($("input[name='username']"));

		form.submit(function () {
			Dialog.closeFunc();
			Setting.setStat(true);
			$.post("../func/post.func.php", {
				oper: $("input[name='oper']").val(),
				noredirect: "noredirect",
				username: $("input[name='username']").val(),
				password: $("input[name='password']").val(),
				permission: $("select[name='permission']").val()
			}, function () {
				Setting.getMessage();
				Setting.loadUsers($("#tableUserMng"));
			});
			return false;
		});
	},

	modifyUser: function (id) {
		if (Setting.working)
			return;

		var user = Setting.users[id];
		var form = Dialog.initFuncDialog(Strings['Modify'], "modiuser", true, true, true);
		var divModifyUser = $("<div/>");
		divModifyUser.attr("id", "divModifyUser");
		var table = $("<table/>");
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "username").html(Strings['Username:']))).append(
				$("<td/>").append(
					$("<input/>").attr({
						type: "text",
						name: "username",
						size: "40",
						maxlength: "128"
					}).val(user.username))));
		var select = $("<select/>").attr("name", "permission");
		var option = $("<option/>").val(25).html("User");
		if (user.permission == "User")
			option.attr("selected", "selected");
		select.append(option);
		option = $("<option/>").val(75).html("Administrator");
		if (user.permission == "Administrator")
			option.attr("selected", "selected");
		select.append(option);
		option = $("<option/>").val(100).html("Root");
		if (user.permission == "Root")
			option.attr("selected", "selected");
		select.append(option);
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "permission").html(Strings['Permission:']))).append(
				$("<td/>").append(select)));

		divModifyUser.append(table);
		divModifyUser.append($("<input/>").attr({
				type: "hidden",
				name: "id"
			}).val(id));
		form.append(divModifyUser);
		Dialog.displaySubmit();

		Dialog.displayFuncDialog();
		Dialog.setFocus($("input[name='username']"));

		form.submit(function () {
			Dialog.closeFunc();
			Setting.setStat(true);
			$.post("../func/post.func.php", {
				oper: $("input[name='oper']").val(),
				noredirect: "noredirect",
				id: $("input[name='id']").val(),
				username: $("input[name='username']").val(),
				permission: $("select[name='permission']").val()
			}, function () {
				Setting.getMessage();
				Setting.loadUsers($("#tableUserMng"));
			});
			return false;
		});
	},

	deleteUser: function (id) {
		if (Setting.working)
			return;

		var form = Dialog.initFuncDialog(Strings['Delete'], "deluser", true, true, true);
		var divDelUser = $("<div/>");
		divDelUser.attr("id", "divDelUser");
		divDelUser.append($("<div/>").html(Strings['Are you sure to delete this user?'] + " \"" + Setting.users[id].username + "\"").addClass("center"));
		divDelUser.append($("<input/>").attr({
				type: "hidden",
				name: "id"
			}).val(id));
		form.append(divDelUser);
		Dialog.displaySubmit();

		Dialog.displayFuncDialog();

		form.submit(function () {
			Dialog.closeFunc();
			Setting.setStat(true);
			$.post("../func/post.func.php", {
				oper: $("input[name='oper']").val(),
				noredirect: "noredirect",
				id: $("input[name='id']").val()
			}, function () {
				Setting.getMessage();
				Setting.loadUsers($("#tableUserMng"));
			});
			return false;
		});
	},

	changePswd: function () {
		var form = Dialog.initFuncDialog(Strings['Change Password'], "changepswd", true, true, true);
		var divChangePswd = $("<div/>");
		divChangePswd.attr("id", "divChangePswd");
		var table = $("<table/>");
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "oldpswd").html(Strings['Old:']))).append(
				$("<td/>").append(
					$("<input/>").attr({
						type: "password",
						name: "oldpswd",
						size: "40",
						maxlength: "128"
					}))));
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "newpswd").html(Strings['New:']))).append(
				$("<td/>").append(
					$("<input/>").attr({
						type: "password",
						name: "newpswd",
						size: "40",
						maxlength: "128"
					}))));
		table.append(
			$("<tr/>").append(
				$("<td/>").append(
					$("<label/>").attr("for", "repeat").html(Strings['Repeat:']))).append(
				$("<td/>").append(
					$("<input/>").attr({
						type: "password",
						name: "repeat",
						size: "40",
						maxlength: "128"
					}))));
		divChangePswd.append(table);
		form.append(divChangePswd);
		Dialog.displaySubmit();

		Dialog.displayFuncDialog();
		Dialog.setFocus($("input[name='oldpswd']"));
	},

	initUserMng: function () {
		var tableUserMng = $("#tableUserMng");
		//alert(divUserMng.length);
		if (tableUserMng.length == 0)
			return;

		Setting.oldTable = tableUserMng.html();

		$("input#changePswd").click(Setting.changePswd);
		$("input#addUser").click(Setting.addUser);
		Setting.loadUsers(tableUserMng);
	},

	/**
	 * 初始化 Setting 部分的 js
	 */
	init: function () {
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
