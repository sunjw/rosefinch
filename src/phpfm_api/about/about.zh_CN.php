<div id="phpfmDoc">
    <div id="phpfmDocReadme">
        <a name="Readme" title="<?php echo _('Readme'); ?>"></a>
        <h4>Rosefinch (朱雀) 是一个用 php 编写的 Web 文件管理程序。</h4>
        <p>它提供了对于远程服务器或本地文件的管理能力，可以对文件或目录进行剪切、复制、粘贴、重命名和删除操作，还可以新建目录以及上传文件 (以上操作均需要对服务器端文件操作权限进行适当的设置)。当然，也可以下载文件，并且支持断点续传或客户端多线程下载。它还可以在数据库的支持下提供搜索文件的功能。</p>
        <h4>Rosefinch (朱雀) 拥有跨平台、多语言的特性。</h4>
        <p>您可以用 Apache (推荐) 或 IIS (下载没有断点续传和多线程能力) 以及其他可以解析 php 的 web 服务程序来运行 Rosefinch (朱雀)，您的操作系统可以是 Linux、Windows 或其他操作系统。</p>
        <p>您运行 Rosefinch (朱雀) 的操作系统可以是任何字符能够转换成 UTF-8 编码的语言。对于操作系统使用的本地编码方式，只需简单的设置便可支持。</p>
        <p>Rosefinch (朱雀) 可以提供多种语言的界面，使用的是 gettext 的方案，你也可以自己本地化界面。</p>
        <p>Rosefinch (朱雀) 为图片和音乐提供预览功能。</p>
        <h4>Rosefinch (朱雀) 对于浏览器的要求</h4>
        <p>由于使用到 javascript 和 ajax 技术，所以需要浏览器支持 javascript。Rosefinch (朱雀) 通过 Cookie 记录下用户的一些设置 (如：排序方法)，所以需要浏览器能够记录 Cookie。如果希望功能保持完整并得到最佳的界面效果，使用 IE 7 及以上版本的 IE 浏览器，或者<strong>最好</strong>使用诸如 Firefox 之类的现代浏览器。</p>
    </div>
    <div id="phpfmDocLicence">
        <a name="Licence" title="<?php echo _('Licence'); ?>"></a>
        <p>Rosefinch (朱雀) 使用 GPL 2.0 协议授权。</p>
        <p>协议文本: <a href="gpl-2.0.txt">GPL 2.0</a></p>
        <p>您可以修改代码，再分发以及其他用途，但必须遵守 GPL 2.0 协议。</p>
        <p>其他授权，请联系作者。</p>
    </div>
    <div id="phpfmDocAbout">
        <a name="About" title="<?php echo _('About'); ?>"></a>
        <h4>关于</h4>
        <?php printf('<p>Rosefinch - %s - PHPFM %s</p><p>%s</p>', _('Rosefinch'), VERSION, _('Using Google Android icons')); ?>
        <h4>作者</h4>
        <p>Sun Junwen</p>
    </div>
</div>