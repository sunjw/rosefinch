var FileManager = {
    restApiUrl: 'func/rest.api.php',
    restApiPrefix: 'api/v1/',

    funcBg: null,
    multilanTitles: null,
    inputChecks: null,
    selectedItems: null,
    sortName: null,
    sortOrder: null,
    delayID: 0,
    miniMainViewHeight: 120,
    downloadText: null,
    imgPreviewLoading: 'images/lightbox-ico-loading.gif',
    isIE: null,
    isMobile: null,

    funcDialog: {
        body: null,
        header: null,
        divInput: null,
        divDelete: null,
        divPreview: null,
        divWaiting: null
    },

    /*
     * Check cookie.
     */
    hasCookie: function (c_name) {
        return (new RegExp('(?:^|;\\s*)' + escape(c_name).replace(/[\-\.\+\*]/g, '\\$&') + '\\s*\\=')).test(document.cookie);
    },

    /*
     * Get cookie value.
     */
    getCookie: function (c_name) {
        if (!c_name || !FileManager.hasCookie(c_name)) {
            return null;
        }
        return unescape(document.cookie.replace(new RegExp('(?:^|.*;\\s*)' + escape(c_name).replace(/[\-\.\+\*]/g, '\\$&') + '\\s*\\=\\s*((?:[^;](?!;))*[^;]?).*'), '$1'));
    },

    /*
     * Set cookie value.
     */
    setCookie: function (c_name, value) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate() + 365);
        var c_value = escape(value) + '; expires=' + exdate.toUTCString();
        document.cookie = c_name + '=' + c_value;
    },

    /*
     * Check support html5 <audio> tag.
     */
    supportHtml5Audio: function () {
        return !!document.createElement('audio').canPlayType;
    },

    /*
     * Do nothing.
     */
    dummy: function () {
        return;
    },

    refresh: function () {
        window.location.reload();
    },

    getItemCheckbox: function (item) {
        return $(item.children().get(0)).children().get(0); // Stupid way.
    },

    /*
     * Mouse over detail view item.
     */
    detailViewItemOver: function (item) {
        var detailViewItem = $(item);
        detailViewItem.addClass('selected');
    },

    /*
     * Mouse out detail view item.
     */
    detailViewItemOut: function (item) {
        var detailViewItem = $(item);
        var checkBox = FileManager.getItemCheckbox(detailViewItem);
        if (checkBox.checked != true) {
            detailViewItem.removeClass('selected');
        }
    },

    /*
     * Mouse click on detail view item.
     */
    detailViewItemClicked: function (item) {
        var detailViewItem = $(item);
        var checkBox = FileManager.getItemCheckbox(detailViewItem);
        if (checkBox.checked) {
            $(checkBox).removeAttr('checked');
        } else {
            $(checkBox).attr('checked', 'checked');
            detailViewItem.addClass('selected');
        }
        FileManager.viewItemCheck();
    },

    /*
     * Item selected.
     */
    viewItemCheck: function () {
        FileManager.setButton('toolbarCut', 'images/toolbar-cut.png',
            FileManager.dummy, 'disable', '');
        FileManager.setButton('toolbarCopy', 'images/toolbar-copy.png',
            FileManager.dummy, 'disable', '');
        FileManager.setButton('toolbarRename',
            'images/toolbar-rename.png', FileManager.dummy,
            'disable', '');
        FileManager.setButton('toolbarDelete',
            'images/toolbar-delete.png', FileManager.dummy,
            'disable', '');

        var count = FileManager.inputChecks.length;
        var checkedItemsCount = 0;
        FileManager.selectedItems = new Array();

        for (var i = 0; i < count; i++) {
            var checkBox = FileManager.inputChecks.get(i);
            var item = $(checkBox.parentNode.parentNode); // CheckBox 对应的项目
            if (checkBox.checked) {
                checkedItemsCount++;
                FileManager.selectedItems.push(checkBox.name);
                item.addClass('selected');
            } else {
                item.removeClass('selected');
            }
        }

        if (checkedItemsCount > 0) {
            FileManager.setButton('toolbarCut', 'images/toolbar-cut.png',
                FileManager.clickCut, '', 'disable');
            FileManager.setButton('toolbarCopy', 'images/toolbar-copy.png',
                FileManager.clickCopy, '', 'disable');
            FileManager.setButton('toolbarDelete', 'images/toolbar-delete.png',
                FileManager.clickDelete, '', 'disable');
            if (checkedItemsCount == 1) {
                FileManager.setButton('toolbarRename',
                    'images/toolbar-rename.png', FileManager.clickRename,
                    '', 'disable');
            }
        }
    },

    /*
     * Set button.
     */
    setButton: function (className, src, clickFunc, addClass, removeClass) {
        var buttons = $('div#toolbar .toolbarButton');

        for (var i = 0; i < buttons.length; i++) {
            var button = $(buttons.get(i));
            if (button.hasClass(className)) {
                button.get(0).onclick = clickFunc;
                if (addClass != '') {
                    button.addClass(addClass);
                }
                if (removeClass != '') {
                    button.removeClass(removeClass);
                }
                var img = button.children('img');
                img.attr('src', src);
            }
        }
    },

    /*
     * Rename.
     */
    clickRename: function () {
        FileManager.setOldname();
        FileManager.displayFuncDialog(FileManager.restApiUrl, 'rename', 'rename',
            null);
    },

    /*
     * New folder.
     */
    clickNewFolder: function () {
        // alert('newfolder');
        FileManager.displayFuncDialog(FileManager.restApiUrl, 'newfolder',
            'new folder', null);
    },

    /*
     * Cut.
     */
    clickCut: function () {
        // alert('cut');
        FileManager.sendCutCopyRestApi(FileManager.restApiPrefix + 'fm/cut');
    },

    /*
     * Copy.
     */
    clickCopy: function () {
        FileManager.sendCutCopyRestApi(FileManager.restApiPrefix + 'fm/copy');
    },

    /*
     * Paste.
     */
    clickPaste: function () {
        var subdir = $('input#subdir').attr('value');
        var returnUrl = $('input#return').val();
        var reqObj = {};
        reqObj.subdir = subdir;

        FileManager.displayWaiting();

        FileManager.sendPostRestApi(FileManager.restApiPrefix + 'fm/paste', reqObj, function (data) {
            if (!FileManager.checkRestRespData(data)) {
                FileManager.showMessage('Error.', true);
            } else {
                FileManager.showMessage(data.message, false);
            }
            setTimeout(function () {
                FileManager.refresh();
            }, 2000);
        });
    },

    /*
     * Delete.
     */
    clickDelete: function () {
        FileManager.displayFuncDialog('', 'delete',
            'delete', null);
    },

    /*
     * Delete confirmed.
     */
    doDelete: function () {
        // Prepare dialog.
        var funcDelete = $('div#funcDelete');
        funcDelete.css('display', 'none');

        var reqObj = {};
        reqObj.items = FileManager.selectedItems;

        FileManager.displayWaiting();

        FileManager.sendPostRestApi(FileManager.restApiPrefix + 'fm/delete', reqObj, function (data) {
            if (!FileManager.checkRestRespData(data)) {
                FileManager.showMessage('Error.', true);
            } else {
                FileManager.showMessage(data.message, false);
            }
            setTimeout(function () {
                FileManager.refresh();
            }, 2000);
        });
    },

    /*
     * Upload.
     */
    clickUpload: function () {
        FileManager.displayFuncDialog(FileManager.restApiUrl, 'upload', 'upload',
            null);
    },

    funcSubmit: function () {
        FileManager.displayWaiting();
    },

    /*
     * Select all.
     */
    selectAll: function () {
        var count = FileManager.inputChecks.length;

        for (var i = 0; i < count; i++) {
            var checkBox = $(FileManager.inputChecks.get(i));
            checkBox.attr('checked', 'checked');
        }

        FileManager.viewItemCheck();
    },

    /*
     * Deselect.
     */
    deselect: function () {
        var count = FileManager.inputChecks.length;

        for (var i = 0; i < count; i++) {
            var checkBox = $(FileManager.inputChecks.get(i));
            checkBox.removeAttr('checked');
        }

        FileManager.viewItemCheck();
    },

    /*
     * Set sort by and order.
     */
    setSortArrow: function (name, order) {
        FileManager.sortName = name;
        FileManager.sortOrder = order;
    },

    /*
     * Get message.
     */
    getMessage: function () {
        $.get('func/getmessage.ajax.php', function (data) {
            if (data != '') {
                var msg;
                var stat;
                data = data.split('|PHPFM|');
                msg = data[0];
                stat = data[1];

                FileManager.showMessage(msg, (stat == 2));
            }
        });

    },

    /*
     * Show message with auto close.
     */
    showMessage: function (msg, wrong) {
        var phpfmMessage = $('#phpfmMessage');
        if (phpfmMessage.length == 1) {
            phpfmMessage.html(msg);
            if (wrong) {
                phpfmMessage.addClass('wrong');
            } else {
                phpfmMessage.removeClass('wrong');
            }

            phpfmMessage.slideToggle();
        }

        phpfmMessage.click(FileManager.closeMessage);
        clearTimeout(FileManager.delayID);
        FileManager.delayID = setTimeout(function () {
            FileManager.closeMessage();
        }, 5000);
    },

    /*
     * Close message.
     */
    closeMessage: function () {
        $('#phpfmMessage').slideToggle();
        clearTimeout(FileManager.delayID);
    },

    /*
     * Get left margin.
     */
    getLeftMargin: function () {
        var viewWidth = document.documentElement.clientWidth;
        var leftMargin = (viewWidth - 420) / 2; // center
        return leftMargin;
    },

    /*
     * Send REST API request.
     */
    sendRestApi: function (method, api, reqObj, successCallback, errorCallback) {
        $.ajax({
            type: method,
            url: FileManager.restApiUrl + '?api=' + api,
            data: reqObj ? JSON.stringify(reqObj) : null,
            contentType: "application/json",
            success: function (data) {
                if (successCallback) {
                    successCallback(data);
                }
            },
            error: function () {
                if (errorCallback) {
                    errorCallback();
                }
            }
        });
    },

    /*
     * Send POST REST API request.
     */
    sendPostRestApi: function (api, reqObj, successCallback, errorCallback) {
        FileManager.sendRestApi('POST', api, reqObj, successCallback, errorCallback);
    },

    checkRestRespData: function (data) {
        if ((typeof data !== 'object' || data === null) ||
            !('code' in data)) {
            // Not return proper object.
            return false;
        }
        return true;
    },

    /*
     * Send cut/copy REST API request.
     */
    sendCutCopyRestApi: function (api) {
        var reqObj = {};
        reqObj.items = FileManager.selectedItems;

        FileManager.sendPostRestApi(api, reqObj, function (data) {
            if (!FileManager.checkRestRespData(data)) {
                FileManager.showMessage('Error.', true);
                return;
            }
            var wrong = false;
            if (data.code == 0) {
                FileManager.setButton('toolbarPaste',
                    'images/toolbar-paste.png', FileManager.clickPaste, '',
                    'disable');
            } else {
                // Error
                wrong = true;
                FileManager.setButton('toolbarPaste',
                    'images/toolbar-paste.png',
                    FileManager.dummy, 'disable', '');
            }
            if (data.message != '') {
                FileManager.showMessage(data.message, wrong);
            }
        }, function () {
            FileManager.setButton('toolbarPaste',
                'images/toolbar-paste.png',
                FileManager.dummy, 'disable', '');
            FileManager.showMessage('Request error.', true);
        });
    },

    /*
     * Set old name of rename.
     */
    setOldname: function () {
        var oldPathInput = $('input#renamePath');
        var oldnameInput = $('input#oldname');
        var newnameInput = $('input#newname');
        var path = FileManager.selectedItems[0];
        oldPathInput.attr('value', path);
        var oldname = path.substring(path.lastIndexOf('/') + 1, path.length);
        oldnameInput.attr('value', oldname); // old name
        newnameInput.attr('value', oldname);
    },

    /*
     * Clear old name of rename.
     */
    cleanOldname: function () {
        var oldnameInput = FileManager.funcDialog.body.find('input#oldname');
        var newnameInput = FileManager.funcDialog.body.find('input#newname');
        oldnameInput.attr('value', ''); // old name
        newnameInput.attr('value', '');
    },

    /*
     * Init func.
     */
    initFuncDialog: function () {
        FileManager.funcBg = $('div#funcBg');

        FileManager.funcDialog.body = $('div#funcDialog');
        FileManager.funcDialog.header = FileManager.funcDialog.body.children('div.divHeader');
        FileManager.funcDialog.divInput = FileManager.funcDialog.body.children('div#divInput');
        FileManager.funcDialog.divDelete = FileManager.funcDialog.body.children('div#divDelete');
        FileManager.funcDialog.divPreview = FileManager.funcDialog.body.children('div#divPreview');
        FileManager.funcDialog.divWaiting = FileManager.funcDialog.body.children('div#divWaiting');

        // Prepare title string.
        var rawTitles = FileManager.funcDialog.header.children('span');
        rawTitles = $(rawTitles[0]);
        rawTitles = rawTitles.html();
        rawTitles = rawTitles.split('|');

        FileManager.multilanTitles = {};
        var count = rawTitles.length;
        var rawTitle,
            key,
            value;
        for (var i = 0; i < count; ++i) {
            rawTitle = rawTitles[i];
            rawTitle = rawTitle.split(':');
            key = rawTitle[0];
            value = rawTitle[1];
            FileManager.multilanTitles[key] = value;
        }

        var funcClose = FileManager.funcDialog.header.children('.funcClose');
        var count = funcClose.length;
        for (var i = 0; i < count; i++) {
            funcClose.get(i).onclick = FileManager.closeFunc;
        }
    },

    displayFuncPart: function (part) {
        FileManager.funcDialog.divInput.addClass('hidden');
        FileManager.funcDialog.divDelete.addClass('hidden');
        FileManager.funcDialog.divPreview.addClass('hidden');
        FileManager.funcDialog.divWaiting.addClass('hidden');

        part.removeClass('hidden');
    },

    displayInputPart: function (part) {
        FileManager.funcDialog.divInput.find('div#divReqInput').addClass('hidden');
        FileManager.funcDialog.divInput.find('div#divUpload').addClass('hidden');
        FileManager.funcDialog.divInput.find('div#divLogin').addClass('hidden');
        FileManager.funcDialog.divInput.find('div#divLogout').addClass('hidden');

        part.removeClass('hidden');
    },

    /*
     * Display func background.
     */
    displayFuncBg: function (canClose, closeableBkg) {
        if (FileManager.isMobile) {
            return;
        }

        if (canClose) {
            FileManager.funcBg.get(0).onclick = closeableBkg ? FileManager.closeFunc : FileManager.dummy;
            FileManager.funcDialog.header.find('.funcClose').css('display', 'block');
        } else {
            FileManager.funcBg.get(0).onclick = FileManager.dummy;
            FileManager.funcDialog.header.find('.funcClose').css('display', 'none');
        }
        FileManager.funcBg.css('height', document.documentElement.scrollHeight + 'px');
        FileManager.funcBg.css('display', 'block');
    },

    /*
     * Display func input part.
     */
    displayFuncDialog: function (action, oper, title, data) {
        var funcDialog = FileManager.funcDialog.body;
        var divHeader = FileManager.funcDialog.header;
        var divInput = FileManager.funcDialog.divInput;
        var divDelete = FileManager.funcDialog.divDelete;
        var divPreview = FileManager.funcDialog.divPreview;
        var divWaiting = FileManager.funcDialog.divWaiting;

        var titleSpan = divHeader.find('span');
        var titleHtml = title;
        if (title in FileManager.multilanTitles) {
            titleHtml = FileManager.multilanTitles[title];
        }
        titleSpan.html(titleHtml);
        var funDialogLeft = FileManager.getLeftMargin();
        if (FileManager.isMobile) {
            funDialogLeft = 0;
        }
        funcDialog.css('left', funDialogLeft + 'px');

        switch (oper) {
            case 'newfolder':
            case 'rename':
            case 'upload':
            case 'login':
            case 'logout':
                FileManager.displayFuncPart(divInput);

                var apiInput = divInput.find('input#api');
                apiInput.val(oper);
                var form = divInput.find('form');
                form.attr('action', action);

                if (oper == 'upload') {
                    FileManager.displayInputPart(divInput.find('div#divUpload'));
                } else if (oper == 'login') {
                    FileManager.displayInputPart(divInput.find('div#divLogin'));
                } else if (oper == 'logout') {
                    FileManager.displayInputPart(divInput.find('div#divLogout'));
                } else {
                    FileManager.displayInputPart(divInput.find('div#divReqInput'));
                }

                FileManager.displayFuncBg(true, true);
                FileManager.displayFuncDialogInternal(funcDialog);

                if (oper != 'upload' && oper != 'login') {
                    divInput.find('input#newname').focus();
                    divInput.find('input#newname').get(0).select();
                } else if (oper == 'login') {
                    divInput.find('input#username').focus();
                    divInput.find('input#username').get(0).select();
                }
                break;
            case 'delete':
                FileManager.displayFuncPart(divDelete);

                FileManager.displayFuncBg(true, true);
                FileManager.displayFuncDialogInternal(funcDialog);
                break;
            case 'preview':
                var previewType = data.type;
                var previewLink = data.link;
                var previewTitle = data.title;

                var previewContent = divPreview.find('#divPreviewContent');
                FileManager.clearPreviewContent(previewContent);
                var previewContentInner = null;

                if (previewType == 'audio') {
                    previewContentInner = $('<audio controls />');
                    previewContent.addClass('previewAudio');
                    previewContent.append(previewContentInner);
                }
                if (previewType == 'img') {
                    var browserWidth = window.innerWidth || document.body.clientWidth;
                    var browserHeight = window.innerHeight || document.body.clientHeight;
                    var previewLoadingWidth = 400;
                    var previewLoadingHeight = 400;
                    if (!FileManager.isMobile) {
                        // Desktop css fix
                        funcDialog.css({
                            'top': 30 + 'px',
                            'left': ((browserWidth - previewLoadingWidth) / 2) + 'px',
                            'width': previewLoadingWidth + 'px',
                            'height': previewLoadingHeight + 'px'
                        });
                    }

                    previewContentInner = $('<img/>');
                    previewContent.addClass('previewImage');
                    previewContent.append(previewContentInner);

                    // Loading...
                    previewContentInner.attr('src', FileManager.imgPreviewLoading);
                    previewContentInner.css({
                        'width': '32px',
                        'height': '32px'
                    });
                }

                // Display main part and loading content.
                FileManager.displayFuncPart(divPreview);

                if (previewType == 'audio') {
                    previewContentInner.attr('src', previewLink);
                }
                if (previewType == 'img') {
                    var imgObj = new Image();
                    imgObj.onload = function () {
                        // Load finished
                        var imgWidth = imgObj.width;
                        var imgHeight = imgObj.height;
                        var imgRatio = imgWidth / imgHeight;

                        var imgPreviewWidth;
                        var imgPreviewHeight;
                        if (!FileManager.isMobile) {
                            imgPreviewWidth = 960; // 1000 - 40
                            imgPreviewHeight = browserHeight - 200;
                        } else {
                            imgPreviewWidth = funcDialog.width() - 20;
                            imgPreviewHeight = funcDialog.height() - 280;
                        }
                        var imgPreviewRadio = imgPreviewWidth / imgPreviewHeight;

                        if (imgRatio >= imgPreviewRadio) {
                            imgPreviewHeight = imgPreviewWidth / imgRatio;
                        } else {
                            imgPreviewWidth = imgPreviewHeight * imgRatio;
                        }

                        if (!FileManager.isMobile) {
                            // Desktop css fix again.
                            var previewWidth = imgPreviewWidth + 20;
                            var previewHeight = imgPreviewHeight + 90;
                            previewWidth = (previewWidth > previewLoadingWidth) ? previewWidth : previewLoadingWidth;
                            funcDialog.css({
                                'left': ((browserWidth - previewWidth) / 2) + 'px',
                                'width': previewWidth + 'px',
                                'height': previewHeight + 'px'
                            });
                        }
                        previewContentInner.attr('src', '');
                        previewContentInner.css({
                            'width': imgPreviewWidth + 'px',
                            'height': imgPreviewHeight + 'px'
                        });
                        previewContentInner.attr('src', previewLink);
                        previewContentInner.attr('alt', previewTitle);
                        previewContentInner.click(function () {
                            window.location.href = previewLink;
                        });

                        imgObj.onload = function () {
                        };
                    };
                    imgObj.src = previewLink;
                }

                var divLink = divPreview.find('div#link');
                if (FileManager.downloadText == null) {
                    FileManager.downloadText = divLink.html();
                }
                divLink.html(FileManager.downloadText + '<a href=\'' + previewLink + '\'>'
                    + previewTitle + '</a>');

                if (FileManager.isMobile || previewType == 'audio') {
                    FileManager.displayFuncBg(true, false);
                } else {
                    // Closeable
                    FileManager.displayFuncBg(true, true);
                }
                FileManager.displayFuncDialogInternal(funcDialog);

                FileManager.afterPreviewOpen(previewType, previewTitle);
                break;
            case 'waiting':
                FileManager.displayFuncPart(divWaiting);

                FileManager.displayFuncBg(false, false);
                FileManager.displayFuncDialogInternal(funcDialog);
                break;
        }
    },

    isPreviewContentVisible: function () {
        var divPreviewContent = $('#divPreviewContent');
        return divPreviewContent.is(':visible');
    },

    clearPreviewContent: function (previewContent) {
        previewContent.empty();
        previewContent.removeClass();
        previewContent.addClass('center');
    },

    /*
     * Close func.
     */
    closeFunc: function () {
        var divPreviewContent = $('#divPreviewContent');
        if (FileManager.isPreviewContentVisible()) {
            var audioControl = divPreviewContent.find('audio');
            if (audioControl.length > 0) {
                audioControl[0].pause();
            }
        }

        if (FileManager.funcDialog.body.is(':visible')) {
            FileManager.closeFuncDialogInternal(FileManager.funcDialog.body);
        }

        FileManager.cleanOldname();
        FileManager.funcBg.css('display', 'none');
        FileManager.clearPreviewContent(divPreviewContent);
    },

    displayFuncDialogInternal: function (funcDialog) {
        if (!FileManager.isMobile) {
            funcDialog.fadeIn();
        } else {
            funcDialog.show();
        }
    },

    closeFuncDialogInternal: function (funcDialogBody) {
        var isPreview = false;
        if (FileManager.isPreviewContentVisible()) {
            isPreview = true;
        }
        if (!FileManager.isMobile) {
            funcDialogBody.fadeOut(function () {
                funcDialogBody.css('top', '');
                funcDialogBody.css('left', '');
                funcDialogBody.css('width', '');
                funcDialogBody.css('height', '');
                if (isPreview) {
                    FileManager.afterPreviewClose();
                }
            });
        } else {
            funcDialogBody.hide();
            if (isPreview) {
                FileManager.afterPreviewClose();
            }
        }
    },

    displayWaiting: function () {
        var funcInput = $('div#divInput');
        funcInput.css('display', 'none');

        FileManager.funcBg.get(0).onclick = FileManager.dummy;
        FileManager.displayFuncDialog('', 'waiting',
            'waiting', null);
    },

    changeMainViewListHeight: function () {
        // Adapt mainViewList height.
        var mainViewList = $('div#mainViewList');
        var mainViewListOffset = mainViewList.offset();
        var footerHeight = $('div#footer').height();
        var windowHeight = $(window).height();
        var mainViewListHeight;

        if (FileManager.isIE && $.browser.version < 8) {
            return;
        } else {
            var mainViewListMarginBottom = 30;
            if (FileManager.isMobile) {
                mainViewListMarginBottom = 2;
            }
            mainViewListHeight = windowHeight - mainViewListOffset.top
                - footerHeight - mainViewListMarginBottom;
            mainViewListHeight = mainViewListHeight > FileManager.miniMainViewHeight ? mainViewListHeight
                : FileManager.miniMainViewHeight;
            mainViewList.css('height', mainViewListHeight + 'px');
            mainViewList.css('overflow', 'auto');
        }
    },

    toolbarButtonMouseIn: function () {
        if (!$(this).hasClass('disable')) {
            $(this).addClass('buttonHover');
        }
    },

    toolbarButtonMouseOut: function () {
        $(this).removeClass('buttonHover');
    },

    /*
     * Prepare toolbar.
     */
    initToolbar: function () {
        var buttons = $('div#toolbar .toolbarButton').add('div#toolbar .toolbarSmallButton');

        buttons.filter('.toolbarRefresh').click(function () {
            window.location.reload(); // refresh
        });

        // buttons.filter('.toolbarSelectAll').click(FileManager.selectAll); //
        // select all
        // buttons.filter('.toolbarDeselect').click(FileManager.deselect); //
        // deselect
        buttons.filter('.toolbarPaste').hasClass('disable') ? null :
            buttons.filter('.toolbarPaste').click(FileManager.clickPaste); // paste

        // view mode
        buttons.filter('.toolbarNewFolder').click(
            FileManager.clickNewFolder); // new folder
        buttons.filter('.toolbarUpload').click(FileManager.clickUpload); // upload

        buttons.hover(FileManager.toolbarButtonMouseIn,
            FileManager.toolbarButtonMouseOut); // button hover

        $('#toolbar form#searchForm input[type="submit"]').hover(
            FileManager.toolbarButtonMouseIn,
            FileManager.toolbarButtonMouseOut); // button hover

        $('#mainView .header span #checkSelectAll').click(function () {
            if (this.checked) {
                FileManager.selectAll();
            } else {
                FileManager.deselect();
            }
        });

        // more...
        var buttonMore = buttons.filter('.toolbarMore');
        if (buttonMore.hasClass('little')) {
            buttonMore.parent().find('.toolbarHiddenable').hide();
            buttonMore.find('img').attr('src', 'images/toolbar-arrow-right.gif');
        }
        buttonMore.click(function () {
            var img = $(this).find('img');
            var part = $(this).parent().find('.toolbarHiddenable');
            if (part.is(':visible')) {
                part.fadeOut('fast');
                img.attr('src', 'images/toolbar-arrow-right.gif');
                FileManager.setCookie('toolbar', 'little');
            } else {
                part.fadeIn('fast');
                img.attr('src', 'images/toolbar-arrow-left.gif');
                FileManager.setCookie('toolbar', 'full');
            }
        });
    },

    /*
     * Init main view.
     */
    initMainView: function () {
        FileManager.changeMainViewListHeight();
        $(window).resize(function () {
            FileManager.changeMainViewListHeight();
        });

        var detailViewItems = $('ul#detailView');
        if (detailViewItems.length > 0) {
            // detail view
            var items = detailViewItems.children('li');
            var count = items.length;
            for (var i = 0; i < count; i++) {
                var item = $(items.get(i));
                if (!item.hasClass('empty')) {
                    var jsObj = item.get(0);
                    jsObj.onmouseover = function () {
                        FileManager.detailViewItemOver(this);
                    };
                    jsObj.onmouseout = function () {
                        FileManager.detailViewItemOut(this);
                    };
                    jsObj.onclick = function () {
                        FileManager.detailViewItemClicked(this);
                    };
                }
                item.children('a')[0].onclick = function (e) {
                    jqCommon.stopBubble(e);
                };
            }

            detailViewItems.show();
        }

        FileManager.inputChecks = $('input.inputCheck');
        FileManager.inputChecks.onclick = function (e) {
            FileManager.viewItemCheck();
            jqCommon.stopBubble(e);
        };
        var count = FileManager.inputChecks.length;
        for (var i = 0; i < count; i++) {
            var check = FileManager.inputChecks.get(i);
            check.onclick = function (e) {
                FileManager.viewItemCheck();
                jqCommon.stopBubble(e);
            };
        }
        FileManager.viewItemCheck();
    },

    initImgPreview: function () {
        $('a.lightboxImg').click(function () {
            var imgLink = $(this).attr('href');
            var imgTitle = $(this).attr('title');
            FileManager.displayFuncDialog('', 'preview',
                'preview', {
                    type: 'img',
                    link: imgLink,
                    title: imgTitle
                });
            return false;
        });
    },

    /*
     * Init AudioPlayer.
     */
    initAudioPlayer: function () {
        $('a.audioPlayer').click(function () {
            var audioLink = $(this).attr('href');
            var audioTitle = $(this).attr('title');
            FileManager.displayFuncDialog('', 'preview',
                'preview', {
                    type: 'audio',
                    link: audioLink,
                    title: audioTitle
                });
            return false;
        });
    },

    initMediaPreview: function () {
        FileManager.initImgPreview();
        FileManager.initAudioPlayer();
        FileManager.afterPreviewOpen = function (type, title) {
            // Change url hash.
            window.location.hash = 'preview_' + type + '_' + encodeURIComponent(title);
        };
        FileManager.afterPreviewClose = function () {
            var curHash = window.location.hash;
            if (curHash &&
                (curHash.startsWith('#preview_') || curHash.startsWith('preview_'))) {
                // Browser push a history with hash changed when preview open.
                history.back();
            }
        };
        window.onpopstate = function (event) {
            var curHash = window.location.hash;
            if (FileManager.isPreviewContentVisible()) {
                if (curHash == '' || curHash == '#') {
                    // Still in preview.
                    FileManager.closeFunc();
                }
            } else {
                if (curHash &&
                    (curHash.startsWith('#preview_') || curHash.startsWith('preview_'))) {
                    // Still preview url?
                    history.replaceState('', document.title, window.location.pathname + window.location.search);
                }
            }
        };
        var curHash = window.location.hash;
        if (curHash &&
            (curHash.startsWith('#preview_') || curHash.startsWith('preview_'))) {
            // Fix hash on load with preview url.
            history.replaceState('', document.title, window.location.pathname + window.location.search);
        }
    },

    initUploadHtml5: function () {
        var body = $('body');
        body = body[0];

        body.ondragover = function () {
            if (!FileManager.funcDialog.body.is(':visible')) {
                FileManager.clickUpload();
            }
        };

        var uploadFileInfo = $('#uploadFileInfo');
        uploadFileInfo.addClass('dropUpload');

        var uploadFileInput = $('#uploadFile');
        uploadFileInput.hide();

        uploadFileInput.change(function () {
            var fileList = uploadFileInput.prop('files');
            if (fileList.length > 0) {
                var fileName = fileList[0].name;
                if (fileList.length > 1) {
                    // multi files
                    fileName = fileName + ', ... ' + fileList.length + ' files';
                }
                uploadFileInfo.html(fileName);
            }
        });

        var uploadFileInfoRaw = uploadFileInfo[0];
        uploadFileInfoRaw.ondragover = function () {
            uploadFileInfo.addClass('dropFile');
            return false;
        };
        uploadFileInfoRaw.ondragleave = function () {
            uploadFileInfo.removeClass('dropFile');
            return false;
        };
        uploadFileInfoRaw.ondragend = function () {
            uploadFileInfo.removeClass('dropFile');
            return false;
        };
        uploadFileInfoRaw.ondrop = function (e) {
            uploadFileInfo.removeClass('dropFile');
            e.preventDefault();
            FileManager.displayWaiting();
            FileManager.uploadHtml5Files(e);
        };

        var divUpload = FileManager.funcDialog.divInput.find('div#divUpload');
        var funcBgRaw = FileManager.funcBg.get(0);
        funcBgRaw.ondragover = function (e) {
            if (!divUpload.is(':visible')) {
                return;
            }
            uploadFileInfo.addClass('dropFile');
            return false;
        };
        funcBgRaw.ondragleave = function (e) {
            uploadFileInfo.removeClass('dropFile');
            return false;
        };
        funcBgRaw.ondragend = function (e) {
            uploadFileInfo.removeClass('dropFile');
            return false;
        };
        funcBgRaw.ondrop = function (e) {
            if (!divUpload.is(':visible')) {
                return;
            }
            uploadFileInfo.removeClass('dropFile');
            e.preventDefault();
            FileManager.displayWaiting();
            FileManager.uploadHtml5Files(e);
        };

    },

    uploadHtml5Files: function (dropEvent) {
        var sessionId = FileManager.getCookie('PHPSESSID');
        var subdir = $('input#subdir').attr('value');
        var returnURL = $('input#return').val();
        var returnURLdecoded = decodeURIComponent($('input#return').val());

        var xhrUpload = new XMLHttpRequest();
        xhrUpload.open('POST', FileManager.restApiUrl);

        xhrUpload.onload = function () {
            window.location.href = returnURLdecoded;
        };

        var form = new FormData();
        form.append('session', sessionId);
        form.append('api', 'upload');
        form.append('ajax', 'ajax');
        form.append('subdir', subdir);
        form.append('return', returnURL);

        // multi files
        var dropFiles = dropEvent.dataTransfer.files;
        filesCount = dropFiles.length;
        for (var i = 0; i < filesCount; ++i) {
            form.append('uploadFile[]', dropFiles[i]);
        }

        xhrUpload.send(form);
    },

    /*
     * Login.
     */
    clickLogin: function () {
        FileManager.displayFuncDialog(FileManager.restApiUrl, 'login', 'user',
            null);
    },

    /*
     * Logout.
     */
    clickLogout: function () {
        FileManager.displayFuncDialog(FileManager.restApiUrl, 'logout', 'user',
            null);
    },

    initUserMng: function () {
        $('a#linkLogin').click(FileManager.clickLogin);
        $('a#linkLogout').click(FileManager.clickLogout);
    }

}

/*
 * Init.
 */
FileManager.init = function () {
    // pre-load some images
    var imgPreload = new Image();
    imgPreload.src = FileManager.imgPreviewLoading;

    // fix Chrome back issue
    $.ajaxSetup({
        cache: false
    });

    FileManager.isIE = $.browser.msie ? true : false;
    // alert($.browser.version);

    var browserVendor = (navigator.userAgent || navigator.vendor || window.opera);
    FileManager.isMobile = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(browserVendor) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(browserVendor.substr(0, 4));
    //alert(browserVendor + ', ' + FileManager.isMobile);

    if (FileManager.isMobile) {
        $('body').addClass('mobile');
    }

    var str = '#mainView > .header > span.' + FileManager.sortName + ' > a';
    var item = $(str);
    item.addClass('sort' + FileManager.sortOrder);

    // FileManager.initFullPath();
    jqMenu.setup({
        menuItemsSelector: '.menuContainer',
        menuButtonSelector: '.menuButton',
        subMenuSelector: '.subMenu'
        //inlineShadow : 'transparent url('images/shadow.png') no-repeat right bottom'
    });
    jqMenu.init();

    FileManager.initToolbar();
    FileManager.initMainView();
    FileManager.initFuncDialog();
    FileManager.initUserMng();
    FileManager.getMessage();
    FileManager.initMediaPreview();
    FileManager.initUploadHtml5();

    jqCommon.setPlaceholder('#searchForm', '#q', 'Search');
    jqCommon.setVerify('#searchForm', '#q', 'empty', null, null);

};

$(FileManager.init);
