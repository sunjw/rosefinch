// css
require('../scss/bootstrap_phpfm.scss');
require('bootstrap/scss/bootstrap.scss');
require('bootstrap-icons/font/bootstrap-icons.css');
require('../css/main.css');

// js
window.$ = require('jquery');
require('bootstrap');
const qrcode = require('qrcode');
const utils = require('./utils');
const npmUtils = require('./npmUtils');
const jqueryUtils = require('./jqueryUtils');

class RosefinchDialog {

    constructor() {
        this.okText = 'OK';
        this.closeText = 'Close';
        this.loadingText = 'Working...';

        this.divModal = null;
        this.h5ModalTitle = null;
        this.divModalBody = null;
        this.divModalFooter = null;

        this.spanTips = null;
        this.buttonOk = null;
        this.spanOkText = null;
        this.spanOkLoadingSpinner = null;
        this.buttonClose = null;

        this.isShown = false;
        this.pendingClose = false;

        this.dataHandler = null;
        this.showHandler = null;
        this.closeHandler = null;
    }

    init(modalId, isStatic = false, needOkButton = false) {
        let that = this;
        this.divModal = $('<div/>').attr({
            'id': modalId,
            'tabindex': -1
        }).addClass('modal fade');
        if (isStatic) {
            this.divModal.attr('data-backdrop', 'static');
        }
        let divModalDialog = $('<div/>').addClass('modal-dialog modal-dialog-centered modal-dialog-scrollable');
        let divModalContent = $('<div/>').addClass('modal-content');

        let divModalHeader = $('<div/>').addClass('modal-header');
        this.h5ModalTitle = $('<h5/>').addClass('modal-title');
        divModalHeader.append(this.h5ModalTitle);
        divModalContent.append(divModalHeader);

        this.divModalBody = $('<div/>').addClass('modal-body');
        divModalContent.append(this.divModalBody);

        this.divModalFooter = $('<div/>').addClass('modal-footer');
        this.spanTips = $('<span/>').addClass('dialogTips flex-grow-1');
        this.divModalFooter.append(this.spanTips);
        if (needOkButton) {
            this.buttonOk = $('<button/>').attr({
                'type': 'button'
            }).addClass('btn btn-primary')
            this.spanOkText = $('<span/>').text(this.okText);
            this.spanOkLoadingSpinner = $('<span/>').attr({
                'role': 'status',
                'aria-hidden': true
            }).addClass('spinner-border spinner-border-sm');
            this.spanOkLoadingSpinner.hide();
            this.buttonOk.append(this.spanOkLoadingSpinner);
            this.buttonOk.append(this.spanOkText);
            this.divModalFooter.append(this.buttonOk);
        }
        this.buttonClose = $('<button/>').attr({
            'type': 'button',
            'data-dismiss': 'modal'
        }).addClass('btn btn-outline-secondary').text(this.closeText);
        this.divModalFooter.append(this.buttonClose);
        divModalContent.append(this.divModalFooter);

        divModalDialog.append(divModalContent);
        this.divModal.append(divModalDialog);

        this.divModal.on('shown.bs.modal', function () {
            that.isShown = true;
            if (that.showHandler) {
                that.showHandler();
            }
            if (that.pendingClose) {
                that.close();
            }
        });
        this.divModal.on('hidden.bs.modal', function () {
            that.isShown = false;
            that.onclose();
        });
    }

    onclose() {
        this.hideOkButtonLoading();
        if (this.closeHandler) {
            this.closeHandler();
        }
    }

    show() {
        this.divModal.modal('show');
    }

    close() {
        if (this.isShown) {
            this.divModal.modal('hide');
            this.pendingClose = false;
        } else {
            this.pendingClose = true;
        }
    }

    addClass(className) {
        this.divModal.addClass(className);
    }

    removeClass(className) {
        this.divModal.removeClass(className);
    }

    setTitle(titleText) {
        this.h5ModalTitle.text(titleText);
    }

    appendBody(childElement) {
        this.divModalBody.append(childElement);
    }

    setDataHandler(dataHandler) {
        this.dataHandler = dataHandler;
    }

    setData(data) {
        if (this.dataHandler) {
            this.dataHandler(data);
        }
    }

    hasShown() {
        return this.isShown;
    }

    handleUpdate() {
        this.divModal.modal('handleUpdate');
    }

    setShowHandler(showHandler) {
        this.showHandler = showHandler;
    }

    setCloseHandler(closeHandler) {
        this.closeHandler = closeHandler;
    }

    setTipsText(tipsText) {
        this.spanTips.text(tipsText);
    }

    setOkButtonHandler(okHandler) {
        if (this.buttonOk == null) {
            return;
        }
        this.buttonOk.on('click', function () {
            okHandler();
        });
    }

    showOkButtonLoading() {
        if (this.buttonOk == null) {
            return;
        }
        this.buttonOk.attr('disabled', 'disabled');
        this.buttonClose.attr('disabled', 'disabled');
        this.spanOkText.text(this.loadingText);
        this.spanOkText.addClass('loadingText');
        this.spanOkLoadingSpinner.show();
    }

    hideOkButtonLoading() {
        if (this.buttonOk == null) {
            return;
        }
        this.spanOkText.text(this.okText);
        this.spanOkText.removeClass('loadingText');
        this.spanOkLoadingSpinner.hide();
        this.buttonOk.removeAttr('disabled');
        this.buttonClose.removeAttr('disabled');
    }

    clickOkButton() {
        if (this.buttonOk == null) {
            return;
        }
        this.buttonOk.get(0).click();
    }

    setCloseButtonText(closeText) {
        this.closeText = closeText;
        this.buttonClose.text(this.closeText);
    }
}

class RosefinchPage {

    constructor(apiPrefix) {
        // const
        this.hashPrefix = '#!'
        this.productName = 'Rosefinch';

        this.apiBase = utils.isString(apiPrefix) ? apiPrefix : '';
        this.restApiEndpoint = 'func/rest.api.php';
        this.dlApiEndpoint = 'func/download.func.php';

        this.config = null;
        this.firstLoad = true;

        this.reqSortByKey = 's';
        this.reqSortOrderKey = 'o';
        this.reqDirKey = 'dir';
        this.reqFilePreviewKey = 'preview';

        this.sortByName = 'n';
        this.sortByType = 't';
        this.sortByMTime = 'm';
        this.sortByArray = [this.sortByName, this.sortByType, this.sortByMTime];
        this.sortOrderAsc = 'a';
        this.sortOrderDesc = 'd';
        this.sortOrderArray = [this.sortOrderAsc, this.sortOrderDesc];

        this.operCut = 'cut';
        this.operCopy = 'copy';

        this.dataPreview = 'data-preview';

        // elements
        this.body = $('body');
        this.divWrapper = $('#divWrapper');
        this.navToolbarWrapper = $('#navToolbarWrapper');
        this.divToolbarBrand = $('#divToolbarBrand');
        this.aBrand = null;
        this.divToolbarLeft = $('#divToolbarLeft');
        this.divToolbarRight = $('#divToolbarRight');
        this.navPathWrapper = $('#navPathWrapper');
        this.olPathWrapper = $('#olPathWrapper');
        this.divPathBtnWrapper = $('#divPathBtnWrapper');
        this.divMainWrapper = $('#divMainWrapper');
        this.divListWrapper = $('#divListWrapper');
        this.ulDetailView = null;
        this.divToastWrapper = null;

        // buttons
        this.buttonBack = null;
        this.buttonRefresh = null;
        this.buttonIconRefresh = null;
        this.spanLoadingSpinnerLeft = null;
        this.buttonUpload = null;
        this.buttonNewFolder = null;
        this.buttonCut = null;
        this.buttonCopy = null;
        this.buttonPaste = null;
        this.buttonRename = null;
        this.buttonDelete = null;
        this.buttonShare = null;

        this.buttonSetting = null;
        this.buttonAbout = null;
        this.buttonLoadingRight = null;

        this.buttonSortDropDown = null;
        this.buttonSortArray = {};

        // dialogs
        this.modalUpload = null;
        this.modalNewFolder = null;
        this.modalPaste = null;
        this.modalRename = null;
        this.modalDelete = null;
        this.modalShare = null;
        this.modalAbout = null;

        this.modalAudio = null;
        this.modalImage = null;

        this.modalSetting = null;
        this.modalInstall = null;

        // vars
        this.currentDir = [];

        this.sortBy = '';
        this.sortOrder = '';

        this.mainList = null;
        this.mainListSelectedList = null;
        this.mainListSelectedItemKey = 'item';
        this.mainListSelectedFilePathKey = 'filePath';

        this.clipboardCount = 0;

        this.currentDialog = null;
        this.dropFileEvent = null;

        this.currentInPreview = false;
    }

    initContent() {
        this.initLayout();
        this.initFunc();

        // init call
    }

    initLayout() {
        let that = this;

        // prepare layout
        let spanBrand = $('<span/>').attr('id', 'spanBrand').addClass('navbar-brand');
        this.aBrand = $('<a/>').attr('href', this.hashPrefix).addClass('noOutline').text(this.productName);
        spanBrand.append(this.aBrand);
        this.divToolbarBrand.append(spanBrand);

        this.initSortMenu();

        this.ulDetailView = $('<ul/>').attr('id', 'ulDetailView').addClass('list-group list-group-flush');
        this.divListWrapper.append(this.ulDetailView);

        this.divToastWrapper = $('<div/>').attr('id', 'divToastWrapper');
        this.divWrapper.append(this.divToastWrapper);

        // prepare event handler
        this.onLayoutResize();
        $(window).on('resize', function () {
            that.onLayoutResize();
        });
    }

    onLayoutResize() {
        let windowWidth = this.getWindowWidth();
        let windowHeight = this.getWindowHeight();
        utils.log('RosefinchPage.onWindowResize, windowWidth=%dpx, windowHeight=%dpx', windowWidth, windowHeight);

        let divListWrapperTop = this.divListWrapper.offset().top;
        let divListWrapperHeight = windowHeight - divListWrapperTop - 2;
        this.divListWrapper.css('height', divListWrapperHeight + 'px');

        if (this.isCurrentDialog(this.modalImage)) {
            this.modalImage.setData({
                'on': 'resize'
            });
        }
    }

    initFunc() {
        let that = this;

        // prepare
        this.initButtons();
        this.initDragDropUpload();

        // hash change
        $(window).on('hashchange', function () {
            that.onHashChange();
        });

        // begin with get config
        this.initConfig();
    }

    getWindowWidth() {
        return $(window).width();
    }

    getWindowHeight() {
        return $(window).height();
    }

    getCurrentDirStr() {
        let currentDirStr = this.currentDir.join('/');
        if (currentDirStr.length > 0) {
            currentDirStr = currentDirStr + '/';
        }
        return currentDirStr;
    }

    initConfig() {
        let that = this;

        let requestApi = this.generateRestApiUrl('api/v1/sys/config');
        utils.log('RosefinchPage.initConfig, requestApi=[%s]', requestApi);

        this.showMainListLoading();
        jqueryUtils.getRestRequest(requestApi, function (data) {
            that.hideMainListLoading();

            if (!that.checkRestRespData(data)) {
                utils.log('RosefinchPage.initConfig, response ERROR!');
                that.showToast(that.productName, 'Response error.', 'danger');
                return;
            }
            that.config = data.data;
            utils.log('RosefinchPage.initConfig, installed=[%s]', that.config['installed']);

            if (that.config['installed']) {
                let titleName = that.config['title_name'];
                that.aBrand.text(titleName);
                if (titleName != that.productName) {
                    document.title = that.productName + ' - ' + titleName;
                } else {
                    document.title = that.productName;
                }
                // begin
                that.onHashChange();
            } else {
                // install
                that.showInstallDialog();
            }
        });
    }

    onHashChange(isRetry = false) {
        let that = this;

        let locationHash = window.location.hash;
        utils.log('RosefinchPage.onHashChange, locationHash=[%s]', locationHash);

        if (locationHash.startsWith(this.hashPrefix)) {
            locationHash = locationHash.slice(this.hashPrefix.length);
        }

        let requestSortBy = utils.getUrlQueryVariable(locationHash, this.reqSortByKey);
        let requestSortOrder = utils.getUrlQueryVariable(locationHash, this.reqSortOrderKey);
        let requestDir = utils.getUrlQueryVariable(locationHash, this.reqDirKey);
        let requestFilePreview = utils.getUrlQueryVariable(locationHash, this.reqFilePreviewKey);

        if (!this.firstLoad) {
            // already loaded
            if (requestFilePreview != '') {
                // show preview
                utils.log('RosefinchPage.onHashChange, not firstLoad, requestFilePreview=[%s]',
                    requestFilePreview);
                this.showImagePreviewDialogByHash(requestFilePreview);
                return;
            }
            if (this.currentInPreview) {
                this.currentInPreview = false;
                if (this.isCurrentDialog(this.modalImage)) {
                    // close preview dialog
                    this.modalImage.close();
                }
                return;
            }
        }

        let requestApi = this.generateRestApiUrl('api/v1/fm/ls');
        requestApi += ('&' + this.reqSortByKey + '=' + requestSortBy);
        requestApi += ('&' + this.reqSortOrderKey + '=' + requestSortOrder);
        requestApi += ('&' + this.reqDirKey + '=' + requestDir);
        utils.log('RosefinchPage.onHashChange, requestApi=[%s]', requestApi);

        this.showMainListLoading();
        jqueryUtils.getRestRequest(requestApi, function (data) {
            if (!that.checkRestRespData(data)) {
                if (!isRetry) {
                    // let's retry
                    utils.log('RosefinchPage.onHashChange, response ERROR, retry.');
                    setTimeout(function () {
                        that.onHashChange(true);
                    }, 250);
                } else {
                    // still error!
                    utils.log('RosefinchPage.onHashChange, response ERROR!');
                    that.hideMainListLoading();
                    that.showToast(that.productName, 'Response error.', 'danger');
                }
                return;
            }

            that.hideMainListLoading();

            that.currentDir = data.data['current_path'];
            that.sortBy = data.data['sort']['by'];
            that.sortOrder = data.data['sort']['order'];
            that.mainList = data.data['main_list'];
            that.clipboardCount = data.data['clipboard']['count'];

            if (that.sortBy == '') {
                that.sortBy = that.sortByName;
                that.sortOrder = that.sortOrderAsc;
            }
            if (!Array.isArray(that.mainList)) {
                utils.log('RosefinchPage.onHashChange, mainList not an Array.');
                that.showToast(that.productName, 'Response error.', 'danger');
                return;
            }

            that.renderBreadcrumb();
            that.updateClipboard();
            that.updateSortMenu();
            that.onLayoutResize();
            that.renderMainList();
            that.onFileSelected();

            if (that.firstLoad) {
                utils.log('RosefinchPage.onHashChange, firstLoad.');
                if (requestFilePreview != '') {
                    utils.log('RosefinchPage.onHashChange, firstLoad, requestFilePreview=[%s]',
                        requestFilePreview);
                    // tweak history
                    let curHash = window.location.hash;
                    let dirHref = that.generateDirHref(that.currentDir);
                    utils.historyReplace(dirHref);
                    utils.historyPush(curHash);
                    // show preview
                    that.showImagePreviewDialogByHash(requestFilePreview);
                }
            }
            that.firstLoad = false;
        }, function () {
            utils.log('RosefinchPage.onHashChange, request ERROR!');
            that.showToast(that.productName, 'Request error.', 'danger');
            that.hideMainListLoading();
        });
    }

    generateToolbarButton(buttonId, iconName, title = null) {
        let button = $('<button/>').attr({
            'id': buttonId,
            'type': 'button'
        }).addClass('toolbarBtn btn btn-light');
        if (title) {
            button.attr('title', title);
        }
        let buttonIcon = $('<i/>').addClass('bi').addClass(iconName);
        button.append(buttonIcon);
        return button;
    }

    initButtons() {
        let that = this;

        // left
        this.buttonBack = this.generateToolbarButton('buttonBack', 'bi-chevron-left', 'Back');
        this.onButtonClick(this.buttonBack, function () {
            history.back();
        });
        this.buttonRefresh = this.generateToolbarButton('buttonRefresh', 'bi-arrow-clockwise', 'Refresh');
        this.buttonRefresh.addClass('toolbarBtnLoading');
        this.buttonIconRefresh = this.buttonRefresh.find('i.bi');
        this.spanLoadingSpinnerLeft = $('<span/>').attr({
            'role': 'status',
            'aria-hidden': true
        }).addClass('spinner-border');
        this.spanLoadingSpinnerLeft.hide();
        this.buttonRefresh.append(this.spanLoadingSpinnerLeft);
        this.onButtonClick(this.buttonRefresh, function () {
            that.onHashChange();
        });
        this.buttonUpload = this.generateToolbarButton('buttonUpload', 'bi-cloud-upload', 'Upload');
        this.onButtonClick(this.buttonUpload, function () {
            that.showUploadDialog();
        });
        this.buttonNewFolder = this.generateToolbarButton('buttonNewFolder', 'bi-folder-plus', 'New Folder');
        this.onButtonClick(this.buttonNewFolder, function () {
            that.showNewFolderDialog();
        });
        this.buttonCut = this.generateToolbarButton('buttonCut', 'bi-scissors', 'Cut');
        this.onButtonClick(this.buttonCut, function () {
            that.onCutCopy(that.operCut);
        });
        this.buttonCut.hide();
        this.buttonCopy = this.generateToolbarButton('buttonCopy', 'bi-files', 'Copy');
        this.onButtonClick(this.buttonCopy, function () {
            that.onCutCopy(that.operCopy);
        });
        this.buttonCopy.hide();
        this.buttonPaste = this.generateToolbarButton('buttonPaste', 'bi-clipboard', 'Paste');
        this.onButtonClick(this.buttonPaste, function () {
            that.showPasteDialog();
        });
        this.buttonPaste.hide();
        this.buttonRename = this.generateToolbarButton('buttonRename', 'bi-input-cursor-text', 'Rename');
        this.onButtonClick(this.buttonRename, function () {
            that.showRenameDialog();
        });
        this.buttonRename.hide();
        this.buttonDelete = this.generateToolbarButton('buttonDelete', 'bi-trash', 'Delete');
        this.onButtonClick(this.buttonDelete, function () {
            that.showDeleteDialog();
        });
        this.buttonDelete.hide();
        this.buttonShare = this.generateToolbarButton('buttonShare', 'bi-upc-scan', 'QR Code');
        this.onButtonClick(this.buttonShare, function () {
            that.showShareDialog();
        });

        this.divToolbarLeft.append(this.buttonBack);
        this.divToolbarLeft.append('\n'); // fix strange layout
        this.divToolbarLeft.append(this.buttonRefresh);
        this.divToolbarLeft.append('\n');
        this.divToolbarLeft.append(this.buttonUpload);
        this.divToolbarLeft.append('\n');
        this.divToolbarLeft.append(this.buttonNewFolder);
        this.divToolbarLeft.append('\n');
        this.divToolbarLeft.append(this.buttonCut);
        this.divToolbarLeft.append('\n');
        this.divToolbarLeft.append(this.buttonCopy);
        this.divToolbarLeft.append('\n');
        this.divToolbarLeft.append(this.buttonPaste);
        this.divToolbarLeft.append('\n');
        this.divToolbarLeft.append(this.buttonRename);
        this.divToolbarLeft.append('\n');
        this.divToolbarLeft.append(this.buttonDelete);
        this.divToolbarLeft.append('\n');
        this.divToolbarLeft.append(this.buttonShare);
        this.divToolbarLeft.append('\n');

        // right
        this.buttonSetting = this.generateToolbarButton('buttonSetting', 'bi-gear', 'Setting');
        this.onButtonClick(this.buttonSetting, function () {
            that.showSettingDialog();
        });
        this.buttonAbout = this.generateToolbarButton('buttonAbout', 'bi-info-circle', 'About');
        this.onButtonClick(this.buttonAbout, function () {
            that.showAboutDialog();
        });
        this.buttonLoadingRight = $('<button/>').attr({
            'id': 'buttonLoadingRight',
            'type': 'button',
            'disabled': 'disabled'
        }).addClass('toolbarBtn toolbarBtnLoading btn btn-light');
        let spanLoadingSpinnerRight = $('<span/>').attr({
            'role': 'status',
            'aria-hidden': true
        }).addClass('spinner-border');
        this.buttonLoadingRight.append(spanLoadingSpinnerRight);
        this.buttonLoadingRight.hide();

        this.divToolbarRight.append('\n');
        this.divToolbarRight.append(this.buttonSetting);
        this.divToolbarRight.append('\n');
        this.divToolbarRight.append(this.buttonAbout);
        this.divToolbarRight.append('\n');
        this.divToolbarRight.append(this.buttonLoadingRight);
        this.divToolbarRight.append('\n');
    }

    onButtonClick(button, handler) {
        let that = this;
        button.on('click', function () {
            if (that.needFixButtonFocus()) {
                button.addClass('focus');
            }
            handler();
            that.resetButtonStat(button);
        });
    }

    resetButtonStat(button) {
        let that = this;
        setTimeout(function () {
            if (that.needFixButtonFocus()) {
                button.removeClass('focus');
            }
            button.blur();
        }, 250);
    }

    needFixButtonFocus() {
        return (npmUtils.isiOS() || npmUtils.isMacOS());
    }

    initSortMenu() {
        let divBtnGroup = $('<div/>').addClass('btn-group');
        let spanButton = null;

        this.buttonSortDropDown = $('<button/>').attr({
            'type': 'button',
            'data-toggle': 'dropdown',
            'aria-haspopup': 'true',
            'aria-expanded': 'false'
        }).addClass('btn btn-sm btn-outline-secondary dropdown-toggle');
        spanButton = this.generateSortButton(this.sortByName, this.sortOrderAsc);
        this.buttonSortDropDown.append(spanButton);
        divBtnGroup.append(this.buttonSortDropDown);

        let divDropDownMenu = $('<div/>').addClass('dropdown-menu dropdown-menu-right');
        for (let i = 0; i < this.sortByArray.length; i++) {
            let itrSortBy = this.sortByArray[i];
            this.buttonSortArray[itrSortBy] = {};
            for (let j = 0; j < this.sortOrderArray.length; j++) {
                let itrSortOrder = this.sortOrderArray[j];
                let aDropDownItem = $('<a/>').attr('href', this.hashPrefix).addClass('dropdown-item');
                spanButton = this.generateSortButton(itrSortBy, itrSortOrder);
                aDropDownItem.append(spanButton);
                this.buttonSortArray[itrSortBy][itrSortOrder] = aDropDownItem;
                divDropDownMenu.append(aDropDownItem);
            }
        }
        divBtnGroup.append(divDropDownMenu);

        this.divPathBtnWrapper.append(divBtnGroup);
    }

    updateSortMenu() {
        this.buttonSortDropDown.empty();
        let spanButton = this.generateSortButton(this.sortBy, this.sortOrder);
        this.buttonSortDropDown.append(spanButton);

        for (let i = 0; i < this.sortByArray.length; i++) {
            let itrSortBy = this.sortByArray[i];
            for (let j = 0; j < this.sortOrderArray.length; j++) {
                let itrSortOrder = this.sortOrderArray[j];
                let aDropDownItem = this.buttonSortArray[itrSortBy][itrSortOrder];
                let sortHref = this.generateDirHrefEx(this.currentDir, itrSortBy, itrSortOrder);
                aDropDownItem.attr('href', sortHref);
                if (itrSortBy == this.sortBy) {
                    if (itrSortOrder == this.sortOrder) {
                        aDropDownItem.hide();
                    } else {
                        aDropDownItem.show();
                    }
                } else if (itrSortBy == this.sortByName || itrSortBy == this.sortByType) {
                    if (itrSortOrder == this.sortOrderAsc) {
                        aDropDownItem.show();
                    } else {
                        aDropDownItem.hide();
                    }
                } else if (itrSortBy == this.sortByMTime) {
                    if (itrSortOrder == this.sortOrderDesc) {
                        aDropDownItem.show();
                    } else {
                        aDropDownItem.hide();
                    }
                }
            }
        }
    }

    generateSortButton(sortBy, sortOrder) {
        let spanButton = $('<span/>');
        let iBi = $('<i/>').addClass('bi');
        let sortOrderClass = '';
        if (sortBy == this.sortByName || sortBy == this.sortByType) {
            if (sortOrder == this.sortOrderAsc) {
                sortOrderClass = 'bi-sort-alpha-down';
            } else if (sortOrder == this.sortOrderDesc) {
                sortOrderClass = 'bi-sort-alpha-down-alt';
            }
        } else if (sortBy == this.sortByMTime) {
            if (sortOrder == this.sortOrderAsc) {
                sortOrderClass = 'bi-sort-numeric-down';
            } else if (sortOrder == this.sortOrderDesc) {
                sortOrderClass = 'bi-sort-numeric-down-alt';
            }
        }
        iBi.addClass(sortOrderClass);
        spanButton.append(iBi);
        let sortByString = '';
        if (sortBy == this.sortByName) {
            sortByString = 'Name';
        } else if (sortBy == this.sortByType) {
            sortByString = 'Type';
        } else if (sortBy == this.sortByMTime) {
            sortByString = 'Modified time';
        }
        spanButton.append(sortByString);
        return spanButton;
    }

    updateClipboard() {
        if (this.clipboardCount > 0) {
            this.buttonPaste.show();
        } else {
            this.buttonPaste.hide();
        }
    }

    onCutCopy(oper) {
        let that = this;

        this.showMainListLoading();
        let requestApi = that.generateRestApiUrl('api/v1/fm/' + oper);
        utils.log('RosefinchPage.onCutCopy, requestApi=[%s]', requestApi);
        let reqObj = {};
        reqObj['items'] = that.getFileSelectedList();

        let toastTitle = 'Clipboard';
        if (oper == this.operCut) {
            toastTitle = 'Cut';
        } else if (oper == this.operCopy) {
            toastTitle = 'Copy';
        }
        jqueryUtils.postRestRequest(requestApi, reqObj, function (data) {
            that.hideMainListLoading();

            if (!that.checkRestRespData(data)) {
                utils.log('RosefinchPage.onCutCopy, response ERROR!');
                that.showToast(toastTitle, 'Response error.', 'danger');
            } else {
                let dataCode = data['code'];
                let dataMessage = data['message'];
                utils.log('RosefinchPage.onCutCopy, request OK, data[\'code\']=%d', dataCode);
                if (dataCode == 0) {
                    that.showToast(toastTitle, dataMessage, 'success');
                } else {
                    that.showToast(toastTitle, dataMessage, 'danger');
                }
            }

            that.clipboardCount = data.data['clipboard']['count'];
            utils.log('RosefinchPage.onCutCopy, request OK, clipboardCount=%d', that.clipboardCount);
            that.updateClipboard();
        }, function () {
            utils.log('RosefinchPage.onCutCopy, request ERROR!');
            that.hideMainListLoading();
            that.showToast(toastTitle, 'Request error.', 'danger');
        });
    }

    isImageType(type) {
        const imgTypes = ['jpg', 'jpeg', 'bmp', 'png', 'gif'];
        type = type.toLowerCase();
        return imgTypes.includes(type);
    }

    isAudioType(type) {
        const audioTypes = ['mp3'];
        type = type.toLowerCase();
        return audioTypes.includes(type);
    }

    initDragDropUpload() {
        let that = this;

        const dropFileClass = 'dropFile';
        let bodyElem = this.body.get(0);
        bodyElem.ondragover = function (e) {
            //utils.log('RosefinchPage.initDragDropUpload, body ondragover.');
            e.preventDefault();
            if (that.currentDialog == null) {
                that.showUploadDialog();
            } else if (that.isCurrentDialog(that.modalUpload)) {
                that.modalUpload.addClass(dropFileClass);
            }
            return false;
        };
        bodyElem.ondragleave = function (e) {
            utils.log('RosefinchPage.initDragDropUpload, body ondragleave.');
            e.preventDefault();
            if (that.isCurrentDialog(that.modalUpload)) {
                that.modalUpload.removeClass(dropFileClass);
            }
            return false;
        };
        bodyElem.ondragend = function (e) {
            utils.log('RosefinchPage.initDragDropUpload, body ondragend.');
            e.preventDefault();
            if (that.isCurrentDialog(that.modalUpload)) {
                that.modalUpload.removeClass(dropFileClass);
            }
            return false;
        };
        bodyElem.ondrop = function (e) {
            utils.log('RosefinchPage.initDragDropUpload, body ondrop.');
            e.preventDefault();
            if (that.isCurrentDialog(that.modalUpload)) {
                that.dropFileEvent = e;
                that.modalUpload.removeClass(dropFileClass);
                that.modalUpload.clickOkButton();
            }
            return false;
        }
    }

    checkRestRespData(data) {
        if (!utils.isObject(data) || !('code' in data)) {
            // Not return proper object.
            return false;
        }
        return true;
    }

    generateRestApiUrl(api) {
        return (this.apiBase + this.restApiEndpoint + '?api=' + api);
    }

    generateDlApiUrl() {
        return (this.apiBase + this.dlApiEndpoint);
    }

    generateDirHrefEx(dirArray, sortBy, sortOrder) {
        let paramDir = encodeURIComponent(dirArray.join('/'));
        let href = this.hashPrefix + this.reqSortByKey + '=' + sortBy +
            '&' + this.reqSortOrderKey + '=' + sortOrder +
            '&' + this.reqDirKey + '=' + paramDir;
        return href;
    }

    generateDirHref(dirArray) {
        return this.generateDirHrefEx(dirArray, this.sortBy, this.sortOrder);
    }

    generateFileHref(dirArray, file) {
        let paramFile = encodeURIComponent((dirArray.concat([file])).join('/'));
        let href = this.generateDlApiUrl() + '?file=' + paramFile;
        return href;
    }

    generateFilePreviewHref(dirArray, file) {
        let href = this.generateDirHref(dirArray);
        href = href + '&' + this.reqFilePreviewKey + '=' + file;
        return href;
    }

    onFileSelected() {
        const itemKey = this.mainListSelectedItemKey;
        const filePathKey = this.mainListSelectedFilePathKey;
        this.mainListSelectedList = [];
        let liDetailLines = $('li.detailLine');
        for (let i = 0; i < liDetailLines.length; i++) {
            let liDetailLine = $(liDetailLines.get(i));
            let inputCheckbox = liDetailLine.find('.fileCheck input:checkbox');
            let inputCheckboxElem = inputCheckbox.get(0);
            if (inputCheckboxElem.checked) {
                let filePath = inputCheckbox.attr('name');
                let selectedObject = {};
                selectedObject[itemKey] = liDetailLine;
                selectedObject[filePathKey] = filePath;
                this.mainListSelectedList.push(selectedObject);
            }
        }

        let fileSelectedCount = this.mainListSelectedList.length;
        utils.log('RosefinchPage.onFileSelected, fileSelectedCount=%d', fileSelectedCount);

        if (fileSelectedCount == 0) {
            this.buttonCut.hide();
            this.buttonCopy.hide();
            this.buttonRename.hide();
            this.buttonDelete.hide();
            this.buttonShare.show();
        } else if (fileSelectedCount == 1) {
            this.buttonCut.show();
            this.buttonCopy.show();
            this.buttonRename.show();
            this.buttonDelete.show();
            this.buttonShare.show();
        } else if (fileSelectedCount > 1) {
            this.buttonCut.show();
            this.buttonCopy.show();
            this.buttonRename.hide();
            this.buttonDelete.show();
            this.buttonShare.hide();
        }
    }

    getFileSelectedList() {
        const filePathKey = this.mainListSelectedFilePathKey;
        let fileSelectedList = [];
        for (let i = 0; i < this.mainListSelectedList.length; i++) {
            let filePath = this.mainListSelectedList[i][filePathKey];
            fileSelectedList.push(filePath);
        }
        return fileSelectedList;
    }

    showMainListLoading() {
        this.buttonIconRefresh.hide();
        this.spanLoadingSpinnerLeft.show();
        this.buttonAbout.hide();
        this.buttonLoadingRight.show();
    }

    hideMainListLoading() {
        let that = this;
        setTimeout(function () {
            that.spanLoadingSpinnerLeft.hide();
            that.buttonIconRefresh.show();
            that.buttonLoadingRight.hide();
            that.buttonAbout.show();
        }, 250);
    }

    showToast(title, message, type = 'info') {
        utils.log('RosefinchPage.showToast');

        let toastTypes = ['info', 'success', 'danger'];
        if (!toastTypes.includes(type)) {
            type = toastTypes[0];
        }

        let divToast = $('<div/>').attr({
            'role': 'alert',
            'aria-live': 'assertive',
            'aria-atomic': 'true',
            //'data-autohide': 'false',
            'data-delay': '5000'
        }).addClass('toast').addClass(type);

        let divToastHeader = $('<div/>').addClass('toast-header');
        let iToastIcon = $('<i/>').addClass('toastIcon bi bi-bell');
        divToastHeader.append(iToastIcon);
        let strongToastTitle = $('<strong/>').addClass('toastTitle mr-auto').text(title);
        divToastHeader.append(strongToastTitle);
        let buttonToastClose = $('<button/>').attr({
            'type': 'button',
            'data-dismiss': 'toast',
            'aria-label': 'Close'
        }).addClass('noOutline ml-2 mb-1 close');
        let spanClose = $('<span/>').attr('aria-hidden', 'true').html('&times;');
        buttonToastClose.append(spanClose);
        divToastHeader.append(buttonToastClose);
        divToast.append(divToastHeader);

        let divToastBody = $('<div/>').addClass('toast-body').html(message);
        divToast.append(divToastBody);

        this.divToastWrapper.append(divToast);

        divToast.on('hidden.bs.toast', function () {
            // remove self
            utils.log('RosefinchPage.showToast, clear.');
            divToast.remove();
        });

        divToast.toast('show');
    }

    isCurrentDialog(someDialog) {
        if (!someDialog) {
            return false;
        }
        return (this.currentDialog == someDialog);
    }

    showUploadDialog() {
        if (this.modalUpload == null) {
            utils.log('RosefinchPage.showUploadDialog, init modalUpload.');
            let that = this;

            const uploadFileInfoText = 'Click or drop files to upload.';

            this.modalUpload = new RosefinchDialog();
            this.modalUpload.init('divModalUpload', true, true);
            this.modalUpload.setTitle('Upload');
            this.modalUpload.setCloseButtonText('Cancel');

            let formBody = $('<form/>');
            jqueryUtils.formOnSubmit(formBody, function () {
                that.modalUpload.clickOkButton();
            });
            let divFormGroup = $('<div/>').addClass('form-group');

            let labelUploadFileInfo = $('<label/>').attr({
                'id': 'labelUploadFileInfo',
                'for': 'inputUploadFile'
            }).addClass('col-form-label text-truncate');
            labelUploadFileInfo.text(uploadFileInfoText);
            let inputUploadFile = $('<input/>').attr({
                'id': 'inputUploadFile',
                'type': 'file',
                'multiple': 'multiple'
            }).addClass('form-control');
            inputUploadFile.hide();
            inputUploadFile.on('change', function () {
                let fileList = inputUploadFile.prop('files');
                if (fileList.length > 0) {
                    let fileName = fileList[0].name;
                    if (fileList.length > 1) {
                        // multi files
                        fileName = fileName + ', ...';
                    }
                    fileName = utils.escapeHtml(fileName);
                    labelUploadFileInfo.html(fileName);
                    let tipsText = '';
                    if (fileList.length == 1) {
                        tipsText = '1 file';
                    } else if (fileList.length > 1) {
                        tipsText = fileList.length + ' files';
                    }
                    that.modalUpload.setTipsText(tipsText);
                }
            });

            divFormGroup.append(labelUploadFileInfo);
            divFormGroup.append(inputUploadFile);
            formBody.append(divFormGroup);
            this.modalUpload.appendBody(formBody);

            this.modalUpload.setCloseHandler(function () {
                utils.log('RosefinchPage.showUploadDialog, close.');
                inputUploadFile.val('');
                labelUploadFileInfo.text(uploadFileInfoText);
                that.modalUpload.setTipsText('');
                that.currentDialog = null;
            });

            this.modalUpload.setOkButtonHandler(function () {
                utils.log('RosefinchPage.showUploadDialog, ok.');

                that.modalUpload.showOkButtonLoading();

                let requestApi = that.generateRestApiUrl('api/v1/fm/upload');
                utils.log('RosefinchPage.showUploadDialog, requestApi=[%s]', requestApi);

                let toastTitle = 'Upload';
                let xhrUpload = new XMLHttpRequest();
                xhrUpload.open('POST', requestApi);
                xhrUpload.onload = function () {
                    let data = {};
                    try {
                        data = JSON.parse(this.responseText);
                    } catch (e) {
                        utils.log('RosefinchPage.showUploadDialog, JSON.parse ERROR!');
                    }

                    setTimeout(function () {
                        that.modalUpload.close();

                        if (!that.checkRestRespData(data)) {
                            utils.log('RosefinchPage.showUploadDialog, response ERROR!');
                            that.showToast(toastTitle, 'Response error.', 'danger');
                        } else {
                            let dataCode = data['code'];
                            let dataMessage = data['message'];
                            utils.log('RosefinchPage.showUploadDialog, request OK, data[\'code\']=%d', dataCode);
                            if (dataCode == 0) {
                                that.showToast(toastTitle, dataMessage, 'success');
                            } else {
                                that.showToast(toastTitle, dataMessage, 'danger');
                            }
                        }

                        that.onHashChange();
                    }, 500);
                };
                xhrUpload.onerror = function () {
                    utils.log('RosefinchPage.showUploadDialog, request ERROR!');
                    that.modalUpload.close();
                    that.showToast(toastTitle, 'Request error.', 'danger');
                };

                let form = new FormData();
                //form.append('session', sessionId);
                form.append('ajax', 'ajax');
                form.append('subdir', that.getCurrentDirStr());

                let uploadFiles = [];
                if (that.dropFileEvent) {
                    // drag and drop
                    uploadFiles = that.dropFileEvent.dataTransfer.files;
                    utils.log('RosefinchPage.showUploadDialog, dropFileEvent=%d', uploadFiles.length);
                    that.dropFileEvent = null;
                } else {
                    // click
                    uploadFiles = inputUploadFile.prop('files');
                    utils.log('RosefinchPage.showUploadDialog, inputUploadFile=%d', uploadFiles.length);
                }

                let filesCount = uploadFiles.length;
                for (let i = 0; i < filesCount; i++) {
                    form.append('uploadFile[]', uploadFiles[i]);
                }

                xhrUpload.send(form);
            });
        }

        utils.log('RosefinchPage.showUploadDialog');
        this.currentDialog = this.modalUpload;
        this.modalUpload.show();
    }

    showNewFolderDialog() {
        if (this.modalNewFolder == null) {
            utils.log('RosefinchPage.showNewFolderDialog, init modalNewFolder.');
            let that = this;

            this.modalNewFolder = new RosefinchDialog();
            this.modalNewFolder.init('divModalNewFoler', true, true);
            this.modalNewFolder.setTitle('New folder');
            this.modalNewFolder.setCloseButtonText('Cancel');

            let formBody = $('<form/>');
            jqueryUtils.formOnSubmit(formBody, function () {
                that.modalNewFolder.clickOkButton();
            });
            let divFormGroup = $('<div/>').addClass('form-group');
            let labelName = $('<label/>').attr('for', 'inputName').addClass('col-form-label').text('Name: ');
            let inputName = $('<input/>').attr({
                'id': 'inputName',
                'type': 'text'
            }).addClass('form-control');
            divFormGroup.append(labelName);
            divFormGroup.append(inputName);
            formBody.append(divFormGroup);
            this.modalNewFolder.appendBody(formBody);

            this.modalNewFolder.setShowHandler(function () {
                utils.log('RosefinchPage.showNewFolderDialog, show.');
                jqueryUtils.focusOnInput(inputName);
            });

            this.modalNewFolder.setCloseHandler(function () {
                utils.log('RosefinchPage.showNewFolderDialog, close.');
                inputName.val('');
                inputName.removeAttr('disabled');
                that.currentDialog = null;
            });

            this.modalNewFolder.setOkButtonHandler(function () {
                utils.log('RosefinchPage.showNewFolderDialog, ok.');

                inputName.attr('disabled', 'disabled');
                that.modalNewFolder.showOkButtonLoading();

                let requestApi = that.generateRestApiUrl('api/v1/fm/newfolder');
                utils.log('RosefinchPage.showNewFolderDialog, requestApi=[%s]', requestApi);
                let reqObj = {};
                reqObj['subdir'] = that.getCurrentDirStr();
                reqObj['newname'] = inputName.val().trim();

                let toastTitle = 'New folder';
                jqueryUtils.postRestRequest(requestApi, reqObj, function (data) {
                    that.modalNewFolder.close();

                    if (!that.checkRestRespData(data)) {
                        utils.log('RosefinchPage.showNewFolderDialog, response ERROR!');
                        that.showToast(toastTitle, 'Response error.', 'danger');
                    } else {
                        let dataCode = data['code'];
                        let dataMessage = data['message'];
                        utils.log('RosefinchPage.showNewFolderDialog, request OK, data[\'code\']=%d', dataCode);
                        if (dataCode == 0) {
                            that.showToast(toastTitle, dataMessage, 'success');
                        } else {
                            that.showToast(toastTitle, dataMessage, 'danger');
                        }
                    }

                    that.onHashChange();
                }, function () {
                    utils.log('RosefinchPage.showNewFolderDialog, request ERROR!');
                    that.modalNewFolder.close();
                    that.showToast(toastTitle, 'Request error.', 'danger');
                });
            });
        }

        utils.log('RosefinchPage.showNewFolderDialog');
        this.currentDialog = this.modalNewFolder;
        this.modalNewFolder.show();
    }

    showPasteDialog() {
        if (this.modalPaste == null) {
            utils.log('RosefinchPage.showPasteDialog, init modalPaste.');
            let that = this;

            this.modalPaste = new RosefinchDialog();
            this.modalPaste.init('divModalPaste', true, true);
            this.modalPaste.setTitle('Paste');
            this.modalPaste.setCloseButtonText('Cancel');

            let divMessage = $('<div/>');
            let pPasteMessage = $('<p/>');
            pPasteMessage.html('Are you sure to paste files/folders here?');
            divMessage.append(pPasteMessage);
            this.modalPaste.appendBody(divMessage);

            this.modalPaste.setCloseHandler(function () {
                utils.log('RosefinchPage.showPasteDialog, close.');
                that.currentDialog = null;
            });

            this.modalPaste.setOkButtonHandler(function () {
                utils.log('RosefinchPage.showPasteDialog, ok.');

                that.modalPaste.showOkButtonLoading();

                let requestApi = that.generateRestApiUrl('api/v1/fm/paste');
                utils.log('RosefinchPage.showPasteDialog, requestApi=[%s]', requestApi);
                let reqObj = {};
                reqObj['subdir'] = that.getCurrentDirStr();

                let toastTitle = 'Paste';
                jqueryUtils.postRestRequest(requestApi, reqObj, function (data) {
                    that.modalPaste.close();

                    if (!that.checkRestRespData(data)) {
                        utils.log('RosefinchPage.showPasteDialog, response ERROR!');
                        that.showToast(toastTitle, 'Response error.', 'danger');
                    } else {
                        let dataCode = data['code'];
                        let dataMessage = data['message'];
                        utils.log('RosefinchPage.showPasteDialog, request OK, data[\'code\']=%d', dataCode);
                        if (dataCode == 0) {
                            that.showToast(toastTitle, dataMessage, 'success');
                        } else {
                            that.showToast(toastTitle, dataMessage, 'danger');
                        }
                    }

                    that.onHashChange();
                }, function () {
                    utils.log('RosefinchPage.showPasteDialog, request ERROR!');
                    that.modalPaste.close();
                    that.showToast(toastTitle, 'Request error.', 'danger');
                });
            });
        }

        utils.log('RosefinchPage.showPasteDialog');
        this.currentDialog = this.modalPaste;
        this.modalPaste.show();
    }

    showRenameDialog() {
        if (this.modalRename == null) {
            utils.log('RosefinchPage.showRenameDialog, init modalRename.');
            let that = this;

            this.modalRename = new RosefinchDialog();
            this.modalRename.init('divModalRename', true, true);
            this.modalRename.setTitle('Rename');
            this.modalRename.setCloseButtonText('Cancel');

            let formBody = $('<form/>');
            jqueryUtils.formOnSubmit(formBody, function () {
                that.modalRename.clickOkButton();
            });
            let divFormGroup = $('<div/>').addClass('form-group');
            let labelName = $('<label/>').attr('for', 'inputName').addClass('col-form-label').text('New name: ');
            let inputName = $('<input/>').attr({
                'id': 'inputName',
                'type': 'text'
            }).addClass('form-control');
            divFormGroup.append(labelName);
            divFormGroup.append(inputName);
            formBody.append(divFormGroup);
            this.modalRename.appendBody(formBody);

            let renamePath = '';
            let oldname = '';
            this.modalRename.setDataHandler(function (data) {
                renamePath = data['renamePath'];
                oldname = utils.getFileName(renamePath);
                inputName.val(oldname);
            });

            this.modalRename.setShowHandler(function () {
                utils.log('RosefinchPage.showRenameDialog, show.');
                jqueryUtils.focusOnInput(inputName);
            });

            this.modalRename.setCloseHandler(function () {
                utils.log('RosefinchPage.showRenameDialog, close.');
                renamePath = '';
                oldname = '';
                inputName.val('');
                inputName.removeAttr('disabled');
                that.currentDialog = null;
            });

            this.modalRename.setOkButtonHandler(function () {
                utils.log('RosefinchPage.showRenameDialog, ok.');

                inputName.attr('disabled', 'disabled');
                that.modalRename.showOkButtonLoading();

                let requestApi = that.generateRestApiUrl('api/v1/fm/rename');
                utils.log('RosefinchPage.showRenameDialog, requestApi=[%s]', requestApi);
                let reqObj = {};
                reqObj['renamePath'] = renamePath;
                reqObj['oldname'] = oldname;
                reqObj['newname'] = inputName.val().trim();

                let toastTitle = 'Rename';
                jqueryUtils.postRestRequest(requestApi, reqObj, function (data) {
                    that.modalRename.close();

                    if (!that.checkRestRespData(data)) {
                        utils.log('RosefinchPage.showRenameDialog, response ERROR!');
                        that.showToast(toastTitle, 'Response error.', 'danger');
                    } else {
                        let dataCode = data['code'];
                        let dataMessage = data['message'];
                        utils.log('RosefinchPage.showRenameDialog, request OK, data[\'code\']=%d', dataCode);
                        if (dataCode == 0) {
                            that.showToast(toastTitle, dataMessage, 'success');
                        } else {
                            that.showToast(toastTitle, dataMessage, 'danger');
                        }
                    }

                    that.onHashChange();
                }, function () {
                    utils.log('RosefinchPage.showRenameDialog, request ERROR!');
                    that.modalRename.close();
                    that.showToast(toastTitle, 'Request error.', 'danger');
                });
            });
        }

        utils.log('RosefinchPage.showRenameDialog');
        this.currentDialog = this.modalRename;
        let fileSelectedList = this.getFileSelectedList();
        this.modalRename.setData({
            'renamePath': fileSelectedList[0]
        });
        this.modalRename.show();
    }

    showDeleteDialog() {
        if (this.modalDelete == null) {
            utils.log('RosefinchPage.showDeleteDialog, init modalDelete.');
            let that = this;

            this.modalDelete = new RosefinchDialog();
            this.modalDelete.init('divModalDelete', true, true);
            this.modalDelete.setTitle('Delete');
            this.modalDelete.setCloseButtonText('Cancel');

            let divMessage = $('<div/>');
            let pDeleteMessage = $('<p/>');
            pDeleteMessage.html('Are you sure to delete selected files/folders?');
            divMessage.append(pDeleteMessage);
            this.modalDelete.appendBody(divMessage);

            this.modalDelete.setCloseHandler(function () {
                utils.log('RosefinchPage.showDeleteDialog, close.');
                that.currentDialog = null;
            });

            this.modalDelete.setOkButtonHandler(function () {
                utils.log('RosefinchPage.showDeleteDialog, ok.');

                that.modalDelete.showOkButtonLoading();

                let requestApi = that.generateRestApiUrl('api/v1/fm/delete');
                utils.log('RosefinchPage.showDeleteDialog, requestApi=[%s]', requestApi);
                let reqObj = {};
                reqObj['items'] = that.getFileSelectedList();

                let toastTitle = 'Delete';
                jqueryUtils.postRestRequest(requestApi, reqObj, function (data) {
                    that.modalDelete.close();

                    if (!that.checkRestRespData(data)) {
                        utils.log('RosefinchPage.showDeleteDialog, response ERROR!');
                        that.showToast(toastTitle, 'Response error.', 'danger');
                    } else {
                        let dataCode = data['code'];
                        let dataMessage = data['message'];
                        utils.log('RosefinchPage.showDeleteDialog, request OK, data[\'code\']=%d', dataCode);
                        if (dataCode == 0) {
                            that.showToast(toastTitle, dataMessage, 'success');
                        } else {
                            that.showToast(toastTitle, dataMessage, 'danger');
                        }
                    }

                    that.onHashChange();
                }, function () {
                    utils.log('RosefinchPage.showDeleteDialog, request ERROR!');
                    that.modalDelete.close();
                    that.showToast(toastTitle, 'Request error.', 'danger');
                });
            });
        }

        utils.log('RosefinchPage.showDeleteDialog');
        this.currentDialog = this.modalDelete;
        this.modalDelete.show();
    }

    showShareDialog() {
        if (this.modalShare == null) {
            utils.log('RosefinchPage.showShareDialog, init modalShare.');
            let that = this;

            this.modalShare = new RosefinchDialog();
            this.modalShare.init('divModalShare');
            this.modalShare.setTitle('Share');

            let divShare = $('<div/>').addClass('text-center');
            let canvasQrImage = $('<canvas/>');
            divShare.append(canvasQrImage);
            this.modalShare.appendBody(divShare);

            this.modalShare.setDataHandler(function (data) {
                let dataLink = data['link'];
                qrcode.toCanvas(canvasQrImage.get(0), dataLink, {
                    width: 300
                });
            });

            this.modalShare.setCloseHandler(function () {
                utils.log('RosefinchPage.showShareDialog, close.');
                that.currentDialog = null;
            });
        }

        this.currentDialog = this.modalShare;

        // get share link
        let shareLink = '';
        if (this.mainListSelectedList.length == 0) {
            // current directory
            shareLink = window.location.href;
        } else {
            // selected one item
            const itemKey = this.mainListSelectedItemKey;
            let liSelectedItem = this.mainListSelectedList[0][itemKey];
            let aFileLink = liSelectedItem.find('a.fileLink');
            let aFileLinkHref = aFileLink.attr('href');
            let aPreviewLinkHref = aFileLink.attr(this.dataPreview);
            if (aPreviewLinkHref) {
                aFileLinkHref = aPreviewLinkHref;
            }
            let curUri = window.location.href.split(this.hashPrefix)[0];
            if (!aFileLinkHref.startsWith(this.hashPrefix) && !curUri.endsWith('/')) {
                // a file selected and using index.html, then get parent directory
                curUri = utils.getParentDir(curUri);
            }
            shareLink = curUri + aFileLinkHref;
        }
        utils.log('RosefinchPage.showShareDialog, shareLink=[%s]', shareLink);
        this.modalShare.setData({
            'link': shareLink
        });
        this.modalShare.show();
    }

    showAboutDialog() {
        if (this.modalAbout == null) {
            utils.log('RosefinchPage.showAboutDialog, init modalAbout.');
            let that = this;

            this.modalAbout = new RosefinchDialog();
            this.modalAbout.init('divModalAbout');
            this.modalAbout.setTitle(this.productName);

            let divMessage = $('<div/>');
            let pAboutMessage = $('<p/>');
            pAboutMessage.html('A web file manager with copy/paste, rename, delete and make new folder in browser.<br/>' +
                'Also, Rosefinch provides download, upload and other file manager features.<br/>' +
                'Rosefinch can be an alternative of Apache Directory Listing.');
            divMessage.append(pAboutMessage);
            this.modalAbout.appendBody(divMessage);

            this.modalAbout.setTipsText(this.config['version']);

            this.modalAbout.setCloseHandler(function () {
                utils.log('RosefinchPage.showAboutDialog, close.');
                that.currentDialog = null;
            });
        }

        utils.log('RosefinchPage.showAboutDialog');
        this.currentDialog = this.modalAbout;
        this.modalAbout.show();
    }

    showAudioPreviewDialog(audioTitle, audioLink) {
        if (this.modalAudio == null) {
            utils.log('RosefinchPage.showAudioPreviewDialog, init modalAudio.');
            let that = this;

            this.modalAudio = new RosefinchDialog();
            this.modalAudio.init('divModalAudio');
            this.modalAudio.setTitle('Preview');

            let divPreviewContent = $('<div/>').addClass('previewContent text-center');
            let audioControl = $('<audio controls/>');
            divPreviewContent.append(audioControl);
            this.modalAudio.appendBody(divPreviewContent);
            let divPreviewDownload = $('<div/>').addClass('previewDownload text-truncate');
            divPreviewDownload.html('Download:&nbsp;');
            let aDownload = $('<a/>');
            divPreviewDownload.append(aDownload);
            this.modalAudio.appendBody(divPreviewDownload);

            this.modalAudio.setDataHandler(function (data) {
                let dataTitle = data['title'];
                let dataLink = data['link'];
                audioControl.attr('src', dataLink);
                aDownload.attr('href', dataLink).html(utils.escapeHtml(dataTitle));
            });

            this.modalAudio.setCloseHandler(function () {
                utils.log('RosefinchPage.showAudioPreviewDialog, close.');
                audioControl.get(0).pause();
                audioControl.attr('src', '');
                aDownload.attr('href', '').html('');
                that.currentDialog = null;
            });
        }

        utils.log('RosefinchPage.showAudioPreviewDialog, audioTitle=[%s], audioLink=[%s]',
            audioTitle, audioLink);
        this.currentDialog = this.modalAudio;
        this.modalAudio.setData({
            'title': audioTitle,
            'link': audioLink
        });
        this.modalAudio.show();
    }

    showImagePreviewDialog(imageTitle, imageLink) {
        if (this.modalImage == null) {
            utils.log('RosefinchPage.showImagePreviewDialog, init modalImage.');
            const previewImageLoadedClass = 'previewImageLoaded';

            let that = this;

            this.modalImage = new RosefinchDialog();
            this.modalImage.init('divModalImage');
            this.modalImage.setTitle('Preview');

            let divPreviewContent = $('<div/>').addClass('previewContent text-center');

            let divLoading = $('<div/>').attr('role', 'status').addClass('spinner-border');
            let spanLoading = $('<span/>').addClass('sr-only');
            divLoading.append(spanLoading);
            divPreviewContent.append(divLoading);

            let imgPreview = $('<img/>').attr('src', '');
            imgPreview.hide();
            divPreviewContent.append(imgPreview);
            this.modalImage.appendBody(divPreviewContent);

            let divPreviewDownload = $('<div/>').addClass('previewDownload text-truncate');
            divPreviewDownload.html('Download:&nbsp;');
            let aDownload = $('<a/>');
            divPreviewDownload.append(aDownload);
            this.modalImage.appendBody(divPreviewDownload);

            let imgObj = null;
            let calcPreviewSize = function () {
                if (!imgObj) {
                    return;
                }

                let imgWidth = imgObj.width;
                let imgHeight = imgObj.height;
                let imgRatio = imgWidth / imgHeight;
                utils.log('RosefinchPage.showImagePreviewDialog, calcPreviewSize, imgWidth=%d, imgHeight=%d, imgRatio=%f',
                    imgWidth, imgHeight, imgRatio);

                let imgPreviewWidth = that.getWindowWidth();
                if (imgPreviewWidth < 576) {
                    imgPreviewWidth = imgPreviewWidth - 90;
                } else {
                    imgPreviewWidth = imgPreviewWidth - 120;
                }
                let imgPreviewHeight = that.getWindowHeight() - 300;
                let imgPreviewRadio = imgPreviewWidth / imgPreviewHeight;

                if (imgRatio >= imgPreviewRadio) {
                    imgPreviewHeight = imgPreviewWidth / imgRatio;
                } else {
                    imgPreviewWidth = imgPreviewHeight * imgRatio;
                }
                if (imgWidth < imgPreviewWidth || imgHeight < imgPreviewHeight) {
                    imgPreviewWidth = imgWidth;
                    imgPreviewHeight = imgHeight;
                }
                imgPreview.css({
                    'width': imgPreviewWidth + 'px',
                    'height': imgPreviewHeight + 'px'
                });

                let downloadMaxWidth = imgPreviewWidth;
                if (imgPreviewWidth < 470) {
                    downloadMaxWidth = 470;
                }
                divPreviewDownload.css('max-width', downloadMaxWidth + 'px');
            };

            this.modalImage.setDataHandler(function (data) {
                let onEvent = data['on'];
                let dataTitle = data['title'];
                let dataLink = data['link'];

                if (onEvent == 'resize') {
                    calcPreviewSize();
                    that.modalImage.handleUpdate();
                    return;
                }

                aDownload.attr('href', dataLink).html(utils.escapeHtml(dataTitle));
                imgObj = new Image();
                imgObj.onload = function () {
                    imgObj.onload = function () {
                    };

                    if (!that.isCurrentDialog(that.modalImage)) {
                        return;
                    }

                    // load finished
                    calcPreviewSize();

                    imgPreview.attr({
                        'src': dataLink,
                        'alt': dataTitle
                    });
                    imgPreview.on('click', function () {
                        window.location.href = dataLink;
                    });

                    divLoading.hide();
                    imgPreview.show();
                    that.modalImage.addClass(previewImageLoadedClass);
                    that.modalImage.handleUpdate();
                };

                imgObj.src = dataLink;
            });

            this.modalImage.setCloseHandler(function () {
                utils.log('RosefinchPage.showImagePreviewDialog, close.');
                divLoading.show();
                imgPreview.hide();
                imgPreview.attr({
                    'src': '',
                    'alt': ''
                }).width(0).height(0);
                if (imgObj) {
                    imgObj.onload = function () {
                    };
                }
                imgObj = null;
                aDownload.attr('href', '').html('');
                divPreviewDownload.css('max-width', 'none');
                that.modalImage.removeClass(previewImageLoadedClass);
                that.currentDialog = null;
                if (that.currentInPreview) {
                    utils.historyBack();
                }
            });
        }

        utils.log('RosefinchPage.showImagePreviewDialog, imageTitle=[%s], imageLink=[%s]',
            imageTitle, imageLink);
        this.currentDialog = this.modalImage;
        this.currentInPreview = true;
        this.modalImage.setData({
            'title': imageTitle,
            'link': imageLink
        });
        this.modalImage.show();
    }

    showSettingDialog() {
        if (this.modalSetting == null) {
            utils.log('RosefinchPage.showSettingDialog, init modalSetting.');
            let that = this;

            this.modalSetting = new RosefinchDialog();
            this.modalSetting.init('divModalSetting', false, true);
            this.modalSetting.setTitle('Setting');
            this.modalSetting.setCloseButtonText('Cancel');

            let divLoadingWrapper = $('<div/>').addClass('loadingWrapper text-center');
            let divLoading = $('<div/>').attr('role', 'status').addClass('spinner-border');
            let spanLoading = $('<span/>').addClass('sr-only');
            divLoading.append(spanLoading);
            divLoadingWrapper.append(divLoading);
            this.modalSetting.appendBody(divLoadingWrapper);

            let formBody = $('<form/>');
            jqueryUtils.formOnSubmit(formBody, function () {
                that.modalSetting.clickOkButton();
            });

            let divFormGroup = $('<div/>').addClass('form-group');
            let labelCharset = $('<label/>').attr('for', 'inputCharset').addClass('col-form-label').text('Charset: ');
            let inputCharset = $('<input/>').attr({
                'id': 'inputCharset',
                'type': 'text'
            }).addClass('form-control');
            jqueryUtils.inputOnEnter(inputCharset, function () {
                that.modalSetting.clickOkButton();
            });
            divFormGroup.append(labelCharset);
            divFormGroup.append(inputCharset);
            let labelTitle = $('<label/>').attr('for', 'inputTitle').addClass('col-form-label').text('Title: ');
            let inputTitle = $('<input/>').attr({
                'id': 'inputTitle',
                'type': 'text'
            }).addClass('form-control');
            jqueryUtils.inputOnEnter(inputTitle, function () {
                that.modalSetting.clickOkButton();
            });
            divFormGroup.append(labelTitle);
            divFormGroup.append(inputTitle);
            formBody.append(divFormGroup);
            formBody.hide();
            this.modalSetting.appendBody(formBody);

            let toastTitle = 'Setting';

            this.modalSetting.setShowHandler(function () {
                utils.log('RosefinchPage.showSettingDialog, show.');

                let requestApi = that.generateRestApiUrl('api/v1/sys/setting');
                utils.log('RosefinchPage.showSettingDialog, requestApi=[%s]', requestApi);
                jqueryUtils.getRestRequest(requestApi, function (data) {
                    if (!that.checkRestRespData(data)) {
                        utils.log('RosefinchPage.showSettingDialog, response ERROR!');
                        that.showToast(toastTitle, 'Response error.', 'danger');
                    } else {
                        let setting = data.data;
                        inputCharset.val(setting['charset']);
                        inputTitle.val(setting['title_name']);

                        divLoadingWrapper.hide();
                        formBody.show();
                    }
                });
            });

            this.modalSetting.setCloseHandler(function () {
                utils.log('RosefinchPage.showSettingDialog, close.');
                divLoadingWrapper.show();
                formBody.hide();
                inputCharset.val('');
                inputCharset.removeAttr('disabled');
                inputTitle.val('');
                inputTitle.removeAttr('disabled');
                that.currentDialog = null;
            });

            this.modalSetting.setOkButtonHandler(function () {
                utils.log('RosefinchPage.showSettingDialog, ok.');

                inputCharset.attr('disabled', 'disabled');
                inputTitle.attr('disabled', 'disabled');

                that.modalSetting.showOkButtonLoading();

                let requestApi = that.generateRestApiUrl('api/v1/sys/setting');
                utils.log('RosefinchPage.showSettingDialog, requestApi=[%s]', requestApi);
                let reqObj = {};
                reqObj['charset'] = inputCharset.val().trim();
                reqObj['titleName'] = inputTitle.val().trim();

                jqueryUtils.postRestRequest(requestApi, reqObj, function (data) {
                    that.modalSetting.close();

                    if (!that.checkRestRespData(data)) {
                        utils.log('RosefinchPage.showSettingDialog, response ERROR!');
                        that.showToast(toastTitle, 'Response error.', 'danger');
                    } else {
                        let dataCode = data['code'];
                        let dataMessage = data['message'];
                        utils.log('RosefinchPage.showSettingDialog, request OK, data[\'code\']=%d', dataCode);
                        if (dataCode == 0) {
                            that.showToast(toastTitle, dataMessage, 'success');
                            that.initConfig();
                        } else {
                            that.showToast(toastTitle, dataMessage, 'danger');
                        }
                    }
                }, function () {
                    utils.log('RosefinchPage.showSettingDialog, request ERROR!');
                    that.modalSetting.close();
                    that.showToast(toastTitle, 'Request error.', 'danger');
                });
            });
        }

        utils.log('RosefinchPage.showSettingDialog');
        this.currentDialog = this.modalSetting;
        this.modalSetting.show();
    }

    showInstallDialog() {
        if (this.modalInstall == null) {
            utils.log('RosefinchPage.showInstallDialog, init modalInstall.');
            let that = this;

            this.modalInstall = new RosefinchDialog();
            this.modalInstall.init('divModalInstall', true, true);
            this.modalInstall.setTitle('Install');
            this.modalInstall.setCloseButtonText('Cancel');

            let formBody = $('<form/>');
            jqueryUtils.formOnSubmit(formBody, function () {
                that.modalInstall.clickOkButton();
            });
            let divFormGroup = $('<div/>').addClass('form-group');
            let labelType = $('<label/>').attr('for', 'selectType').addClass('col-form-label').text('Path type: ');
            let selectType = $('<select/>').attr('id', 'selectType')
                .addClass('form-control')
                .append($('<option/>').attr('value', 'absolute').text('absolute'))
                .append($('<option/>').attr('value', 'relative').text('relative'));
            divFormGroup.append(labelType);
            divFormGroup.append(selectType);
            let labelPath = $('<label/>').attr('for', 'inputPath').addClass('col-form-label').text('Path: ');
            let inputPath = $('<input/>').attr({
                'id': 'inputPath',
                'type': 'text'
            }).addClass('form-control');
            divFormGroup.append(labelPath);
            divFormGroup.append(inputPath);
            formBody.append(divFormGroup);
            this.modalInstall.appendBody(formBody);

            this.modalInstall.setShowHandler(function () {
                utils.log('RosefinchPage.showInstallDialog, show.');
                jqueryUtils.focusOnInput(inputPath);
            });

            this.modalInstall.setCloseHandler(function () {
                utils.log('RosefinchPage.showInstallDialog, close.');
                that.currentDialog = null;
            });

            this.modalInstall.setOkButtonHandler(function () {
                utils.log('RosefinchPage.showInstallDialog, ok.');

                let inputPathVal = inputPath.val().trim();
                if (inputPathVal == '') {
                    jqueryUtils.focusOnInput(inputPath);
                    return;
                }

                selectType.attr('disabled', 'disabled');
                inputPath.attr('disabled', 'disabled');

                that.modalInstall.showOkButtonLoading();

                let requestApi = that.generateRestApiUrl('api/v1/sys/install');
                utils.log('RosefinchPage.showInstallDialog, requestApi=[%s]', requestApi);
                let reqObj = {};
                reqObj['rootType'] = selectType.val();
                reqObj['rootPath'] = inputPathVal;

                let toastTitle = 'Install';
                jqueryUtils.postRestRequest(requestApi, reqObj, function (data) {
                    that.modalInstall.close();

                    if (!that.checkRestRespData(data)) {
                        utils.log('RosefinchPage.showInstallDialog, response ERROR!');
                        that.showToast(toastTitle, 'Response error.', 'danger');
                    } else {
                        let dataCode = data['code'];
                        let dataMessage = data['message'];
                        utils.log('RosefinchPage.showInstallDialog, request OK, data[\'code\']=%d', dataCode);
                        if (dataCode == 0) {
                            that.showToast(toastTitle, dataMessage, 'success');
                            that.onHashChange();
                        } else {
                            that.showToast(toastTitle, dataMessage, 'danger');
                        }
                    }
                }, function () {
                    utils.log('RosefinchPage.showInstallDialog, request ERROR!');
                    that.modalInstall.close();
                    that.showToast(toastTitle, 'Request error.', 'danger');
                });
            });
        }

        utils.log('RosefinchPage.showInstallDialog');
        this.currentDialog = this.modalInstall;
        this.modalInstall.show();
    }

    renderBreadcrumb() {
        let that = this;
        // clear all
        this.olPathWrapper.empty();

        let dirs = [];
        for (let i = 0; i <= this.currentDir.length; i++) {
            let li = $('<li/>').addClass('breadcrumb-item');

            let aDir = $('<a/>').addClass('noOutline');
            let dirHref = this.generateDirHref(dirs);
            aDir.attr('href', dirHref);
            let dirName = '';
            if (i == 0) {
                dirName = 'Root';
            } else {
                dirName = this.currentDir[i - 1];
            }
            aDir.html(utils.escapeHtml(dirName));
            li.append(aDir);

            if (i == this.currentDir.length) {
                // last one
                aDir.on('click', function () {
                    that.onHashChange();
                    return false;
                });
                li.addClass('active');
            }

            this.olPathWrapper.append(li);

            dirs.push(this.currentDir[i]);
        }

        // an empty last element for "/" postfix
        let liPostfix = $('<li/>').addClass('breadcrumb-item');
        this.olPathWrapper.append(liPostfix);
    }

    renderMainList() {
        const previewImageClass = 'previewImage';
        const previewAudioClass = 'previewAudio';
        const folderTypes = ['folder'];

        let that = this;

        // clear all
        this.ulDetailView.empty();

        if (!Array.isArray(this.mainList)) {
            utils.log('RosefinchPage.renderMainList, this.mainList not an Array.');
            return;
        }

        utils.log('RosefinchPage.renderMainList, this.mainList.length=%d', this.mainList.length);
        for (let i = 0; i < this.mainList.length; i++) {
            let item = this.mainList[i];
            let itemName = item['name'];
            let itemType = item['type'];

            let itemIsFolder = false;
            let itemIsImage = false;
            let itemIsAudio = false;
            if (folderTypes.includes(itemType)) {
                itemIsFolder = true;
            } else if (this.isImageType(itemType)) {
                itemIsImage = true;
            } else if (this.isAudioType(itemType)) {
                itemIsAudio = true;
            }

            let liDetailLine = $('<li/>').addClass('detailLine list-group-item d-flex');
            if (itemIsFolder) {
                liDetailLine.addClass('detailLineFolder');
            }

            // left
            let divDetailLineLeft = $('<div/>').addClass('detailLineLeft d-flex flex-grow-1 overflow-hidden');

            let spanFileCheck = $('<span/>').addClass('fileCheck d-flex align-items-center flex-shrink-0');
            let inputCheckbox = $('<input/>').attr({
                'type': 'checkbox',
                'name': item['item_path']
            });
            spanFileCheck.append(inputCheckbox);
            divDetailLineLeft.append(spanFileCheck);

            let aFileLink = $('<a/>').addClass('fileLink noOutline flex-grow-1 d-flex align-items-center');
            if (itemIsImage) {
                aFileLink.addClass(previewImageClass);
            } else if (itemIsAudio) {
                aFileLink.addClass(previewAudioClass);
            }
            let aFileLinkHref = this.hashPrefix;
            if (itemIsFolder) {
                aFileLinkHref = this.generateDirHref(this.currentDir.concat([itemName]));
            } else {
                aFileLinkHref = this.generateFileHref(this.currentDir, itemName);
            }
            aFileLink.attr({
                'title': itemName,
                'href': aFileLinkHref
            });
            let iFileIcon = $('<i/>').addClass('fileIcon bi');
            if (itemIsFolder) {
                iFileIcon.addClass('bi-folder');
            } else if (itemIsImage) {
                iFileIcon.addClass('bi-image');
            } else if (itemIsAudio) {
                iFileIcon.addClass('bi-file-music');
            } else {
                iFileIcon.addClass('bi-file-text');
            }
            aFileLink.append(iFileIcon);
            let spanFileName = $('<span/>').addClass('fileName text-truncate').html(utils.escapeHtml(itemName));
            aFileLink.append(spanFileName);
            divDetailLineLeft.append(aFileLink);

            liDetailLine.append(divDetailLineLeft);

            // right
            let divDetailLineRight = $('<div/>').addClass('detailLineRight d-flex align-items-center');

            let spanFileType = $('<span/>').addClass('fileType').text(item['type_html']);
            divDetailLineRight.append(spanFileType);
            let spanFileSize = $('<span/>').addClass('fileSize')
            if (itemIsFolder) {
                spanFileSize.html('&nbsp;');
            } else {
                spanFileSize.text(npmUtils.formatSize(item['size']));
            }
            divDetailLineRight.append(spanFileSize);
            let spanFileTime = $('<span/>').addClass('fileTime').text(npmUtils.formatTimestamp(item['mtime']));
            divDetailLineRight.append(spanFileTime);

            liDetailLine.append(divDetailLineRight);

            // event
            let inputCheckboxElem = inputCheckbox.get(0);
            inputCheckboxElem.onclick = function (e) {
                jqueryUtils.stopBubble(e);
            };
            inputCheckbox.on('change', function () {
                if (inputCheckboxElem.checked) {
                    liDetailLine.addClass('selected');
                } else {
                    liDetailLine.removeClass('selected');
                }
                that.onFileSelected();
            });
            let aFileLinkElem = aFileLink.get(0);
            aFileLinkElem.onclick = function (e) {
                jqueryUtils.stopBubble(e);
            };

            liDetailLine.on('mouseover', function () {
                liDetailLine.addClass('hover');
            });
            liDetailLine.on('mouseout', function () {
                liDetailLine.removeClass('hover');
            });
            liDetailLine.on('click', function () {
                inputCheckboxElem.click();
            });

            // init preview
            if (aFileLink.hasClass(previewAudioClass)) {
                aFileLink.on('click', function () {
                    that.showAudioPreviewDialog(itemName, aFileLinkHref);
                    return false;
                });
            }

            if (aFileLink.hasClass(previewImageClass)) {
                let previewHref = this.generateFilePreviewHref(this.currentDir, itemName);
                aFileLink.attr(this.dataPreview, previewHref);
                aFileLink.on('click', function () {
                    utils.navToHash(previewHref);
                    // that.showImagePreviewDialog(itemName, aFileLinkHref);
                    return false;
                });
            }

            this.ulDetailView.append(liDetailLine);
        }

        this.divListWrapper.scrollTop(0);
    }

    findFileLinkByFileName(fileName) {
        let aFileLinkList = this.ulDetailView.find('a.fileLink');
        for (let i = 0; i < aFileLinkList.length; i++) {
            let aFileLink = $(aFileLinkList.get(i));
            let aFileLinkTitle = aFileLink.attr('title');
            if (aFileLinkTitle == fileName) {
                return aFileLink;
            }
        }
        return null;
    }

    showImagePreviewDialogByHash(previewFileName) {
        utils.log('RosefinchPage.showImagePreviewDialogByHash, previewFileName=[%s]',
            previewFileName);
        let previewFileLink = this.findFileLinkByFileName(previewFileName);
        if (previewFileLink) {
            let previewFileHref = previewFileLink.attr('href');
            this.showImagePreviewDialog(previewFileName, previewFileHref);
        }
    }
}

$(function () {
    utils.log('init, Rosefinch start...');

    let apiPrefix = '';

    if (PHPFM_CONFIG) {
        const api_prefix_key = 'api_prefix';
        if (api_prefix_key in PHPFM_CONFIG) {
            apiPrefix = PHPFM_CONFIG[api_prefix_key];
        }
    }

    utils.log('init, apiPrefix=[%s]', apiPrefix);

    let page = new RosefinchPage(apiPrefix);
    page.initContent();
});
