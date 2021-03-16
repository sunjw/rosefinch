// css
require('../scss/bootstrap_phpfm.scss');
require('bootstrap/scss/bootstrap.scss');
require('bootstrap-icons/font/bootstrap-icons.css');
require('../css/main.css');

// js
window.$ = require('jquery');
require('bootstrap');
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
        // consts
        this.titleName = 'Rosefinch';
        this.apiBase = utils.isString(apiPrefix) ? apiPrefix : '';
        this.restApiEndpoint = 'func/rest.api.php';
        this.dlApiEndpoint = 'func/download.func.php';

        // elements
        this.body = $('body');
        this.divWrapper = $('#divWrapper');
        this.navToolbarWrapper = $('#navToolbarWrapper');
        this.divToolbarBrand = $('#divToolbarBrand');
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

        this.buttonDebug = null;
        this.buttonSetting = null;
        this.buttonAbout = null;
        this.buttonLoadingRight = null;

        // dialogs
        this.modalUpload = null;
        this.modalNewFolder = null;
        this.modalDelete = null;
        this.modalAbout = null;

        this.modalAudio = null;
        this.modalImage = null;

        // vars
        this.currentDir = [];
        this.sort = '';
        this.sortOrder = '';

        this.mainList = null;
        this.fileSelectedList = null;

        this.currentDialog = null;
        this.dropFileEvent = null;
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
        let aBrand = $('<a/>').attr('href', '#').addClass('noOutline').text(this.titleName);
        spanBrand.append(aBrand);
        this.divToolbarBrand.append(spanBrand);

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
        this.onHashChange();
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

    onHashChange(isRetry = false) {
        let that = this;

        let locationHash = window.location.hash;
        utils.log('RosefinchPage.onHashChange, locationHash=[%s]', locationHash);

        if (locationHash.startsWith('#')) {
            locationHash = locationHash.slice(1);
        }

        let requestSort = utils.getUrlQueryVariable(locationHash, 's');
        let requestOrder = utils.getUrlQueryVariable(locationHash, 'o');
        let requestDir = utils.getUrlQueryVariable(locationHash, 'dir');

        let requestApi = this.generateRestApiUrl('api/v1/fm/ls');
        requestApi += ('&s=' + requestSort);
        requestApi += ('&o=' + requestOrder);
        requestApi += ('&dir=' + requestDir);
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
                    that.showToast(that.titleName, 'Response error.', 'danger');
                }
                return;
            }

            that.hideMainListLoading();

            that.currentDir = data.data['current_path'];
            that.sort = data.data['sort']['type'];
            that.sortOrder = data.data['sort']['order'];
            that.mainList = data.data['main_list'];
            if (!Array.isArray(that.mainList)) {
                utils.log('RosefinchPage.onHashChange, mainList not an Array.');
                that.showToast(that.titleName, 'Response error.', 'danger');
                return;
            }

            that.renderBreadcrumb();
            that.onLayoutResize();
            that.renderMainList();
            that.onFileSelected();
        }, function () {
            utils.log('RosefinchPage.onHashChange, request ERROR!');
            that.showToast(that.titleName, 'Request error.', 'danger');
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
        this.buttonIconRefresh = this.buttonRefresh.children('i.bi');
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
        this.buttonCut.hide();
        this.buttonCopy = this.generateToolbarButton('buttonCopy', 'bi-files', 'Copy');
        this.buttonCopy.hide();
        this.buttonPaste = this.generateToolbarButton('buttonPaste', 'bi-clipboard', 'Paste');
        this.buttonPaste.hide();
        this.buttonRename = this.generateToolbarButton('buttonRename', 'bi-input-cursor-text', 'Rename');
        this.buttonRename.hide();
        this.buttonDelete = this.generateToolbarButton('buttonDelete', 'bi-trash', 'Delete');
        this.onButtonClick(this.buttonDelete, function () {
            that.showDeleteDialog();
        });
        this.buttonDelete.hide();
        this.buttonShare = this.generateToolbarButton('buttonShare', 'bi-upc-scan', 'QR Code');

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
        this.buttonDebug = $('<a/>').attr({
            'id': 'buttonDebug',
            'href': 'index.php',
            'role': 'button',
            'title': 'Debug'
        }).addClass('toolbarBtn btn btn-light');
        let debugIcon = $('<i/>').addClass('bi').addClass('bi-bug');
        this.buttonDebug.append(debugIcon);

        this.buttonSetting = this.generateToolbarButton('buttonSetting', 'bi-gear', 'Setting');
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

        this.divToolbarRight.append(this.buttonDebug);
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
            } else if (that.currentDialog == that.modalUpload) {
                that.modalUpload.addClass(dropFileClass);
            }
            return false;
        };
        bodyElem.ondragleave = function (e) {
            utils.log('RosefinchPage.initDragDropUpload, body ondragleave.');
            e.preventDefault();
            if (that.currentDialog == that.modalUpload) {
                that.modalUpload.removeClass(dropFileClass);
            }
            return false;
        };
        bodyElem.ondragend = function (e) {
            utils.log('RosefinchPage.initDragDropUpload, body ondragend.');
            e.preventDefault();
            if (that.currentDialog == that.modalUpload) {
                that.modalUpload.removeClass(dropFileClass);
            }
            return false;
        };
        bodyElem.ondrop = function (e) {
            utils.log('RosefinchPage.initDragDropUpload, body ondrop.');
            e.preventDefault();
            if (that.currentDialog == that.modalUpload) {
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

    generateDirHrefEx(dirArray, sort, sortOrder) {
        let paramDir = encodeURIComponent(dirArray.join('/'));
        let href = '#s=' + sort + '&o=' + sortOrder + '&dir=' + paramDir;
        return href;
    }

    generateDirHref(dirArray) {
        return this.generateDirHrefEx(dirArray, this.sort, this.sortOrder);
    }

    generateFileHref(dirArray, file) {
        let paramFile = encodeURIComponent((dirArray.concat([file])).join('/'));
        let href = this.generateDlApiUrl() + '?file=' + paramFile;
        return href;
    }

    onFileSelected() {
        this.fileSelectedList = [];
        let fileListCheckboxes = $('li.detailLine .fileCheck input:checkbox');
        for (let i = 0; i < fileListCheckboxes.length; i++) {
            let fileListCheckboxElem = fileListCheckboxes.get(i);
            if (fileListCheckboxElem.checked) {
                let fileListCheckbox = $(fileListCheckboxElem);
                let filePath = fileListCheckbox.attr('name');
                this.fileSelectedList.push(filePath);
            }
        }

        let fileSelectedCount = this.fileSelectedList.length;
        utils.log('RosefinchPage.onFileSelected, fileSelectedCount=%d', fileSelectedCount);

        if (fileSelectedCount == 0) {
            this.buttonCut.hide();
            this.buttonCopy.hide();
            this.buttonPaste.hide();
            this.buttonRename.hide();
            this.buttonDelete.hide();
            this.buttonShare.show();
        } else if (fileSelectedCount == 1) {
            this.buttonCut.show();
            this.buttonCopy.show();
            this.buttonPaste.show();
            this.buttonRename.show();
            this.buttonDelete.show();
            this.buttonShare.show();
        } else if (fileSelectedCount > 1) {
            this.buttonCut.show();
            this.buttonCopy.show();
            this.buttonPaste.show();
            this.buttonRename.hide();
            this.buttonDelete.show();
            this.buttonShare.hide();
        }
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
            formBody.on('submit', function (e) {
                utils.log('RosefinchPage.showUploadDialog, formBody.submit');
                e.preventDefault();
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
                    fileName = utils.escapeHtmlPath(fileName);
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
            formBody.on('submit', function (e) {
                utils.log('RosefinchPage.showNewFolderDialog, formBody.submit');
                e.preventDefault();
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
                reqObj['newname'] = inputName.val();

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
                reqObj['items'] = that.fileSelectedList;

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

    showAboutDialog() {
        if (this.modalAbout == null) {
            utils.log('RosefinchPage.showAboutDialog, init modalAbout.');
            let that = this;

            this.modalAbout = new RosefinchDialog();
            this.modalAbout.init('divModalAbout');
            this.modalAbout.setTitle(this.titleName);

            let divMessage = $('<div/>');
            let pAboutMessage = $('<p/>');
            pAboutMessage.html('A web file manager with copy/paste, rename, delete and make new folder in browser.<br/>' +
                'Also, Rosefinch provides download, upload and other file manager features.<br/>' +
                'Rosefinch can be an alternative of Apache Directory Listing.');
            divMessage.append(pAboutMessage);
            this.modalAbout.appendBody(divMessage);

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
                aDownload.attr('href', dataLink).html(dataTitle);
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

                let imgPreviewWidth = that.getWindowWidth() - 160;
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
                let dataTitle = data['title'];
                let dataLink = data['link'];
                aDownload.attr('href', dataLink).html(dataTitle);
                imgObj = new Image();
                imgObj.onload = function () {
                    // load finished
                    calcPreviewSize();

                    imgPreview.attr('src', dataLink);
                    imgPreview.attr('alt', dataTitle);
                    imgPreview.on('click', function () {
                        window.location.href = dataLink;
                    });

                    divLoading.hide();
                    imgPreview.show();
                    that.modalImage.addClass(previewImageLoadedClass);
                    that.modalImage.handleUpdate();

                    imgObj.onload = function () {
                    };
                };

                setTimeout(function () {
                    imgObj.src = dataLink;
                }, 2000);
                //imgObj.src = dataLink;
            });

            this.modalImage.setCloseHandler(function () {
                utils.log('RosefinchPage.showImagePreviewDialog, close.');
                divLoading.show();
                imgPreview.hide();
                imgPreview.attr('src', '').width(0).height(0);
                imgObj = null;
                aDownload.attr('href', '').html('');
                divPreviewDownload.css('max-width', 'none');
                that.modalImage.removeClass(previewImageLoadedClass);
                that.currentDialog = null;
            });
        }

        utils.log('RosefinchPage.showImagePreviewDialog, imageTitle=[%s], imageLink=[%s]',
            imageTitle, imageLink);
        this.currentDialog = this.modalImage;
        this.modalImage.setData({
            'title': imageTitle,
            'link': imageLink
        });
        this.modalImage.show();
    }

    renderBreadcrumb() {
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
            aDir.html(utils.escapeHtmlPath(dirName));
            li.append(aDir);

            if (i == this.currentDir.length) {
                // last one
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
            let aFileLinkHref = '#';
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
            let spanFileName = $('<span/>').addClass('fileName text-truncate').html(utils.escapeHtmlPath(itemName));
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
                aFileLink.on('click', function () {
                    that.showImagePreviewDialog(itemName, aFileLinkHref);
                    return false;
                });
            }

            this.ulDetailView.append(liDetailLine);
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

    utils.log('init, apiPrefix=[%s]', apiPrefix)

    let page = new RosefinchPage(apiPrefix);
    page.initContent();
});
