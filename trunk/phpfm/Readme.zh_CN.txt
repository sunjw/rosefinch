1. 介绍
Rosefinch (朱雀) 是一个用 php 编写的 Web 文件管理程序。
    它提供了对于远程服务器或本地文件的管理能力，可以对文件或目录进行剪切、复制、粘贴、重命名和删除操作，还可以新建目录以及上传文件 (以上操作均需要对服务器端文件操作权限进行适当的设置)。当然，也可以下载文件，并且支持断点续传或客户端多线程下载。
Rosefinch (朱雀) 拥有跨平台、多语言的特性。
    您可以用 Apache (推荐) 或 IIS (下载没有断点续传和多线程能力) 以及其他可以解析 php 的 web 服务程序来运行 Rosefinch (朱雀)，您的操作系统可以是 Linux、Windows 或其他操作系统。
    您运行 Rosefinch (朱雀) 的操作系统可以是任何字符能够转换成 UTF-8 编码的语言。对于操作系统使用的本地编码方式，只需简单的设置便可支持。
    Rosefinch (朱雀) 可以提供多种语言的界面，使用的是 gettext 的方案，你也可以自己本地化界面。
Rosefinch (朱雀) 对于浏览器的要求
    由于使用到一些 javascript 和 ajax 技术，所以需要浏览器支持 javascript。Rosefinch (朱雀) 通过 Cookie 记录下用户的一些设置 (如：排序方法)，所以需要浏览器能够记录 Cookie。如果希望功能保持完整并得到最佳的界面效果，使用 IE 7 及以上版本的 IE 浏览器，或者最好使用诸如 Firefox 之类的现代浏览器。
	
2. 许可协议
    Rosefinch (朱雀) 使用 GPL 2.0 协议授权。
	协议文本参见 gpl-2.0.txt。
	您可以修改代码，再分发以及其他用途，但必须遵守 GPL 2.0 协议。
    其他授权，请联系作者。

3. 安装
    要将 Rosefinch (朱雀) 摆放到服务器的文件系统中，配置您的 web 服务程序，使其可以执行 Rosefinch (朱雀) 中的 php 代码，。
设置
目前没有专门的设置页面，只有通过修改 inc/defines.inc.php 文件中的常量定义进行设置，所以请小心。特别提醒，inc/defines.inc.php 使用 UTF-8 无 BOM 编码，不要使用记事本修改，会出现乱码字符，影响下载功能。
    1. 设置被管理目录的路径
修改“define("FILE_POSITION", "absolute");”，当定义“FILE_POSITION”为 absolute 时，表示将使用绝对路径；当定义为 relative 时，表示将使用相对路径 (相对于 rosefinch 的根目录，index.php 所在的位置)。
修改“define("FILES_DIR", "");”，双引号内为目录所在路径，相对或绝对在之前已经定义。
    2. 设置服务器的本地字符编码
修改“define("PLAT_CHARSET", "GB2312");”，修改“GB2312”为服务器的本地字符编码。Windows 使用本地字符集，如中文的 GB2312，Linux 服务器常使用 UTF-8。
    3. 设置服务器使用的时区
为了能够准确的显示文件的最后修改时间，修改“date_default_timezone_set("Asia/Shanghai");”，修改“Asia/Shanghai”为服务器时区。
    4. 设置界面语言
修改“define("LOCALE", "zh_CN");”，修改“zh_CN”为您想使用的语言，如 en_US、ja_JP等 (目前只有中文、英文 可以显示)。
    注意: 如果您熟悉 php，可以随意修改代码；若不熟悉，请小心。
已知问题
    只有支持 $_SERVER['HTTP_RANGE'] 的 web 服务程序 (如: Apache) 才能提供断点续传。

4. 关于
    Rosefinch - 朱雀 - PHPFM
    使用 Tango 图标库
作者
    Sun Junwen