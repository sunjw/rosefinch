/**
 * jQuery Common
 * License: GPL 2.0
 * Author: Sun Junwen
 * Version: 1.0.9
 */
var jqCommon = {
	
	prefix : "jqComm",
	placeholderEmpty : "jqCommHolder",
	
	customRegexArray : new Array(),
	customRegexIndex : 0,
	
	/**
	 * 阻止事件浮升
	 */
	stopBubble : function (e) {
		var e = e ? e : window.event;
		if (window.event) { // IE
			e.cancelBubble = true;
		} else { // FF
			// e.preventDefault();
			e.stopPropagation();
		}
	},
	
	dummy : function () {
		return;
	},
	
	/**
	 * Dispatch
	 */
	checkValue : function (inputObj) {
		if (inputObj.hasClass(jqCommon.prefix + "empty")) {
			// check empty
			return jqCommon.checkEmpty(inputObj.val());
		} else if (inputObj.hasClass(jqCommon.prefix + "email")) {
			// check email
			return jqCommon.checkEmail(inputObj.val());
		} else if (inputObj.hasClass(jqCommon.prefix + "number")) {
			// check number
			return jqCommon.checkNumber(inputObj.val());
		} else if (inputObj.hasClass(jqCommon.prefix + "custom")) {
			// check custom regular express
			return jqCommon.checkRegex(jqCommon.customRegexArray[inputObj.attr("cusReg")], inputObj.val());
		}
		
		return true;
	},
	
	checkEmail : function (text) {
		var regExp = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/;
		return regExp.exec($.trim(text));
	},
	
	checkEmpty : function (text) {
		return ($.trim(text) != "");
	},
	
	checkNumber : function (text) {
		var regExp = /^[0-9]+$/;
		return regExp.exec($.trim(text));
	},
	
	checkRegex : function (regex, text) {
		return regex.exec($.trim(text));
	},
	
	getForm : function (formSelector) {
		if (formSelector == null)
			return $("body");
		return $("form" + formSelector);
	},
	
	getInput : function (formSelector, inputSelector) {
		return (jqCommon.getForm(formSelector)).find("input" + inputSelector);
	},
	
	/**
	 * 设置自定义表单提交 handler 函数
	 */
	setFormSubmitHandler : function (handler) {
		jqCommon.formSubmitHandler = handler;
	},
	
	/**
	 * 默认表单提交 handler 函数
	 */
	formSubmitHandler : function (formObj, result) {
		return result;
	},
	
	/**
	 * 表单提交时做验证
	 */
	formSubmit : function (formObj) {
		var inputs = formObj.find("input");
		var count = inputs.length;
		for (var i = 0; i < count; ++i) {
			var input = $(inputs[i]);
			if (input.hasClass(jqCommon.placeholderEmpty)) {
				input.val("");
			}
			
			if (!jqCommon.checkValue(input)) {
				return false;
			}
			
		}
		
		return true;
	},
	
	/**
	 * 检查 input 是否需要填入 placeholder 文字
	 */
	checkPlaceholder : function (input, text) {
		if (input.val() == "") {
			input.addClass(jqCommon.placeholderEmpty);
			if (!input.hasClass(jqCommon.prefix + "empty"))
				input.val(text);
		}
	},
	
	/**
	 * 需要时清空 placeholder 文字
	 */
	cleanPlaceholder : function (input) {
		if (input.hasClass(jqCommon.placeholderEmpty)) {
			input.removeClass(jqCommon.placeholderEmpty);
			input.val("");
		}
	},
	
	/**
	 * 设置指定表单的指定 input 的 placeholder 文字
	 */
	setPlaceholder : function (formSelector, inputSelector, text) {
		var form = jqCommon.getForm(formSelector);
		var input = jqCommon.getInput(formSelector, inputSelector);
		if (input.val() == "" || input.val() == text) {
			input.addClass(jqCommon.placeholderEmpty);
			input.val(text);
		}
		
		input.focus(function () {
				jqCommon.cleanPlaceholder($(this));
			});
		input.click(function () {
				jqCommon.cleanPlaceholder($(this));
			});
		input.blur(function () {
				jqCommon.checkPlaceholder($(this), text);
			});
		
		if (!form.hasClass(jqCommon.prefix + "Form")) {
			form.submit(function () {
					return jqCommon.formSubmitHandler(form, jqCommon.formSubmit($(this)));
				});
			form.addClass(jqCommon.prefix + "Form");
		}
	},
	
	setCustomRegex : function (formSelector, inputSelector, regex) {
		var input = jqCommon.getInput(formSelector, inputSelector);
		input.attr("cusReg", jqCommon.customRegexIndex);
		jqCommon.customRegexArray[jqCommon.customRegexIndex++] = regex;
	},
	
	/**
	 * 设置验证
	 */
	setVerify : function (formSelector, inputSelector, type, okHandler, errHandler) {
		var form = jqCommon.getForm(formSelector);
		var input = jqCommon.getInput(formSelector, inputSelector);
		var inputClass = jqCommon.prefix + type;
		input.addClass(inputClass);
		input.blur(function () {
				if (jqCommon.checkValue($(this))) {
					// ok
					okHandler != null ? okHandler() : jqCommon.dummy();
				} else {
					// error
					errHandler != null ? errHandler() : jqCommon.dummy();
				}
			});
		
		if (!form.hasClass(jqCommon.prefix + "Form")) {
			form.submit(function () {
					return jqCommon.formSubmitHandler(form, jqCommon.formSubmit($(this)));
				});
			form.addClass(jqCommon.prefix + "Form");
		}
	},
	
	/**
	 * 清除已设置的验证
	 */
	cleanVerify : function (formSelector, inputSelector) {
		var input = jqCommon.getInput(formSelector, inputSelector);
		input.removeClass(jqCommon.prefix + "empty");
		input.removeClass(jqCommon.prefix + "email");
		input.removeClass(jqCommon.prefix + "number");
		input.removeClass(jqCommon.prefix + "custom");
		input.removeAttr("cusReg");
		
	}
	
}
 