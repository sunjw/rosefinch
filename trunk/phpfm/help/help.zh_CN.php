<div id="phpfmDoc">
	<div id="phpfmDocInstall">
	   	<a name="Install" title="<?php echo _("Install"); ?>"></a>
	   	<h4>安装</h4>
		<p>要将 Rosefinch (朱雀) 摆放到服务器的文件系统中，配置您的 web 服务程序，使其可以执行 Rosefinch (朱雀) 中的 php 代码，。</p>
		<h4>设置</h4>
		<p>目前没有专门的设置页面，只有通过修改 inc/defines.inc.php 文件中的常量定义进行设置，所以请<strong>小心</strong>。特别提醒，inc/defines.inc.php 使用 UTF-8 无 BOM 编码，<strong>不要使用</strong>记事本修改，会出现乱码字符，影响下载功能。</p>
		<p>1. 设置被管理目录的路径</p>
		<p>修改“<em>define("FILE_POSITION", "absolute");</em>”，当定义“<em>FILE_POSITION</em>”为 <em>absolute</em> 时，表示将使用绝对路径；当定义为 <em>relative</em> 时，表示将使用相对路径 (相对于 rosefinch 的根目录，index.php 所在的位置)。</p>
		<p>修改“<em>define("FILES_DIR", "");</em>”，双引号内为目录所在路径，相对或绝对在之前已经定义。</p>
		<p>2. 设置服务器的本地字符编码</p>
		<p>修改“<em>define("PLAT_CHARSET", "GB2312");</em>”，修改“<em>GB2312</em>”为服务器的本地字符编码。Windows 使用本地字符集，如中文的 GB2312，Linux 服务器常使用 UTF-8。</p>
		<p>3. 设置服务器使用的时区</p>
		<p>为了能够准确的显示文件的最后修改时间，修改“<em>date_default_timezone_set("Asia/Shanghai");</em>”，修改“<em>Asia/Shanghai</em>”为服务器时区。</p>
		<p>4. 设置界面语言</p>
		<p>修改“<em>define("LOCALE", "zh_CN");</em>”，修改“<em>zh_CN</em>”为您想使用的语言，如 en_US、ja_JP等 (目前只有中文、英文 可以显示)。</p>
		<p><em>注意: 如果您熟悉 php，可以随意修改代码；若不熟悉，请<strong>小心</strong>。</em></p>
		<p>5. 设置标题</p>
		<p>修改“<em>define("TITLENAME", "Rosefinch");</em>”，修改“<em>Rosefinch</em>”为您想使用的标题。</p>
		<p>6. 设置启用 LightBox</p>
		<p>修改“<em>define("LIGHTBOX", 1);</em>”，定义为 1 时，为图片启用 LightBox；定义为 0 时，不起用 LightBox。</p>
		<p><em>注意: Rosefinch (朱雀) 中的 LightBox 是修改过的，当图片宽度大于 1000 像素或高度大于500像素时，会按比例缩放。</em></p>
		<h4>已知问题</h4>
		<p>只有支持 $_SERVER['HTTP_RANGE'] 的 web 服务程序 (如: Apache) 才能提供断点续传。</p>
	</div>
	<div id="phpfmDocHelp">
	   	<a name="Help"title="<?php echo _("Help"); ?>"></a>
	   	<h4>帮助</h4>
		<p>帮助正文</p>
	</div>
</div>