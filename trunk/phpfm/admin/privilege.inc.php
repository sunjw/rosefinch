<div id="funcBg">
</div>
<div id="funcDialog">
	<div class="divHeader"><span><?php echo _("User"); ?></span></div>
	<div id="divInput" class="container">
		<form action="../func/post.func.php" method="post" enctype="multipart/form-data">
			<input type="hidden" id="oper" name="oper" value="login" /> 
			<input type="hidden" id="return" name="return" value="<?php echo rawurlencode(get_URI()); ?>" />
			<div id="divLogin">
				<div>
					<label for="username"><?php printf("%s&nbsp;", _("Username:")); ?></label>
					<input id="username" type="text" name="username" value="" size="40"
						maxlength="128" />
				</div>
				<div>
					<label for="password"><?php printf("%s&nbsp;", _("Password:")); ?></label>
					<input id="password" type="password" name="password" value="" size="40"
						maxlength="128" />
				</div>
				<div class="rightAlign">
					<input type="submit" value="<?php echo _("OK"); ?>"/>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
//<![CDATA[
	$(function(){
			$("#funcBg").css("height", document.documentElement.scrollHeight + "px");
			$("#funcBg").css("display", "block");
			$("#funcDialog").css("left", (document.documentElement.clientWidth - 420) / 2 + "px");
			$("#funcDialog").fadeIn("fast");
		});
//]]>
</script>
