<div id="phpfmHelp">
	<div id="phpfmHelpReadme">
	   	<a name="Readme"></a>
		<h4>Rosefinch (朱雀) 是一个用 php 编写的 Web 文件管理程序。</h4>
		<p>它提供了对于远程服务器或本地文件的管理能力，可以对文件或目录进行剪切、复制、粘贴、重命名和删除操作，还可以新建目录以及上传文件 (以上操作均需要对服务器端文件操作权限进行适当的设置)。当然，也可以下载文件，并且支持断点续传或客户端多线程下载。</p>
		<h4>Rosefinch (朱雀) 拥有跨平台、多语言的特性。</h4>
		<p>您可以用 Apache (推荐) 或 IIS (下载没有断点续传和多线程能力) 以及其他可以解析 php 的 web 服务程序来运行 Rosefinch (朱雀)，您的操作系统可以是 Linux、Windows 或其他操作系统。</p>
		<p>您运行 Rosefinch (朱雀) 的操作系统可以是任何字符能够转换成 UTF-8 编码的语言。对于操作系统使用的本地编码方式，只需简单的设置便可支持。</p>
		<p>Rosefinch (朱雀) 可以提供多种语言的界面，使用的是 gettext 的方案，你也可以自己本地化界面。</p>
		<p>Rosefinch (朱雀) 可以为图片提供 LightBox 支持。</p>
		<h4>Rosefinch (朱雀) 对于浏览器的要求</h4>
		<p>由于使用到一些 javascript 和 ajax 技术，所以需要浏览器支持 javascript。Rosefinch (朱雀) 通过 Cookie 记录下用户的一些设置 (如：排序方法)，所以需要浏览器能够记录 Cookie。如果希望功能保持完整并得到最佳的界面效果，使用 IE 7 及以上版本的 IE 浏览器，或者<strong>最好</strong>使用诸如 Firefox 之类的现代浏览器。</p>
	</div>
	<div id="phpfmHelpLicence">
		<a name="Licence"></a>
		<p>Rosefinch (朱雀) 使用 GPL 2.0 协议授权。</p>
		<p>协议文本: <a href="gpl-2.0.txt">GPL 2.0</a></p>
		<p>您可以修改代码，再分发以及其他用途，但必须遵守 GPL 2.0 协议。</p>
		<p>其他授权，请联系作者。</p>
	</div>
	<div id="phpfmHelpInstall">
	   	<a name="Install"></a>
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
		<p>5. 设置启用 LightBox</p>
		<p>修改“<em>define("LIGHTBOX", 1);</em>”，定义为 1 时，为图片启用 LightBox；定义为 0 时，不起用 LightBox。</p>
		<p><em>注意: Rosefinch (朱雀) 中的 LightBox 是修改过的，当图片宽度大于 1000 像素时，会被按比例缩放至 1000 像素。</em></p>
		<h4>已知问题</h4>
		<p>只有支持 $_SERVER['HTTP_RANGE'] 的 web 服务程序 (如: Apache) 才能提供断点续传。</p>
	</div>
	<div id="phpfmHelpAbout">
	   	<a name="About"></a>
	   	<h4>关于</h4>
		<?php printf("<p>Rosefinch - %s - PHPFM %s</p><p>%s</p>", _("Rosefinch"), VERSION, _("Using Tango icon library")); ?>
		<h4>作者</h4>
		<p>Sun Junwen</p>
	</div>
</div>