function indexfile() {
	// alert("!");
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
	
}

function init() {
	var button = $("input#buttonIndexfile");
	
	button.click(indexfile);
	button.removeAttr("disabled");
}

$(init);
 