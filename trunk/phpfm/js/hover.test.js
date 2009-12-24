function init() {
	var lis = $("li.li-item");
	var liCount = lis.length;
	for ( var i = 0; i < liCount; i++) {
		var a = $(lis.get(i)).children("a");
		a.hover( function() {
			if (!$(this.parentNode).hasClass("current")) {
				$(this).animate( {
					'backgroundColor' :'rgb(64, 109, 168)'
				}, "fast");
			}
		}, function() {
			if (!$(this.parentNode).hasClass("current")) {
				$(this).animate( {
					'backgroundColor' :'black'
				}, "fast");
			}
		})
	}
}

$(window).load(init); // 运行准备函数
