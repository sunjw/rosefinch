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

class RosefinchPage {

    constructor(apiPrefix) {
        // consts
        this.apiBase = utils.isString(apiPrefix) ? apiPrefix : '';
        this.restApiEndpoint = 'func/rest.api.php';
        this.dlApiEndpoint = 'func/download.func.php';

        // elements
        this.divWrapper = $('#divWrapper');
        this.navToolbarWrapper = $('#navToolbarWrapper');
        this.divToolbarLeft = $('#divToolbarLeft');
        this.divToolbarRight = $('#divToolbarRight');
        this.navPathWrapper = $('#navPathWrapper');
        this.olPathWrapper = $('#olPathWrapper');
        this.divPathBtnWrapper = $('#divPathBtnWrapper');
        this.divMainWrapper = $('#divMainWrapper');
        this.divListWrapper = $('#divListWrapper');
        this.ulDetailView = null;

        // buttons
        this.buttonBack = null;
        this.buttonRefresh = null;
        this.buttonUpload = null;
        this.buttonNewFolder = null;
        this.buttonCut = null;
        this.buttonCopy = null;
        this.buttonPaste = null;
        this.buttonRename = null;
        this.buttonDelete = null;
        this.buttonDebug = null;
        this.buttonSetting = null;
        this.buttonAbout = null;
        this.buttonLoading = null;

        // vars
        this.currentDir = [];
        this.sort = '';
        this.sortOrder = '';

        this.mainList = null;
    }

    initContent() {
        this.initLayout();
        this.initFunc();

        // init call
    }

    initLayout() {
        let that = this;

        // prepare layout
        this.ulDetailView = $('<ul/>').attr('id', 'ulDetailView').addClass('list-group list-group-flush');
        this.divListWrapper.append(this.ulDetailView);

        // prepare event handler
        this.onWindowResize();
        $(window).on('resize', function () {
            that.onWindowResize();
        });
    }

    onWindowResize() {
        let windowWidth = $(window).width();
        let windowHeight = $(window).height();
        utils.log('RosefinchPage.onWindowResize, windowWidth=%dpx, windowHeight=%dpx', windowWidth, windowHeight);

        let divListWrapperTop = this.divListWrapper.offset().top;
        let divListWrapperHeight = windowHeight - divListWrapperTop - 2;
        this.divListWrapper.css('height', divListWrapperHeight + 'px');
    }

    initFunc() {
        let that = this;

        $(window).on('hashchange', function () {
            that.onHashChange();
        });
        this.onHashChange();

        // prepare buttons
        this.initButtons();
    }

    onHashChange() {
        let that = this;

        let locationHash = window.location.hash;
        utils.log('RosefinchPage.onHashChange, locationHash=[%s]', locationHash);

        if (locationHash.startsWith('#')) {
            locationHash = locationHash.slice(1);
        }

        let requestSort = utils.getUrlQueryVariable(locationHash, 's');
        let requestOrder = utils.getUrlQueryVariable(locationHash, 'o');
        let requestDir = utils.getUrlQueryVariable(locationHash, 'dir');

        let requestApi = this.generateRestApi('api/v1/fm/ls');
        requestApi += ('&s=' + requestSort);
        requestApi += ('&o=' + requestOrder);
        requestApi += ('&dir=' + requestDir);
        utils.log('RosefinchPage.onHashChange, requestApi=[%s]', requestApi);

        jqueryUtils.getRestRequest(requestApi, function (data) {
            if (!that.checkRestRespData(data)) {
                return;
            }

            that.currentDir = data.data['current_path'];
            that.mainList = data.data['main_list'];
            if (!Array.isArray(that.mainList)) {
                utils.log('RosefinchPage.onHashChange, mainList not an Array.');
                return;
            }

            that.renderBreadcrumb();
            that.renderMainList();
        })
    }

    generateToolbarButton(buttonId, iconName) {
        let button = $('<button/>').attr({
            'id': buttonId,
            'type': 'button'
        }).addClass('btn btn-light toolbarBtn');
        let buttonIcon = $('<i/>').addClass('bi').addClass(iconName);
        button.append(buttonIcon);
        return button;
    }

    initButtons() {
        this.buttonBack = this.generateToolbarButton('buttonBack', 'bi-chevron-left');
        this.buttonRefresh = this.generateToolbarButton('buttonRefresh', 'bi-arrow-clockwise');
        this.buttonUpload = this.generateToolbarButton('buttonUpload', 'bi-cloud-upload');
        this.buttonNewFolder = this.generateToolbarButton('buttonNewFolder', 'bi-folder-plus');
        this.buttonCut = this.generateToolbarButton('buttonCut', 'bi-scissors');
        this.buttonCopy = this.generateToolbarButton('buttonCopy', 'bi-files');
        this.buttonPaste = this.generateToolbarButton('buttonPaste', 'bi-clipboard');
        this.buttonRename = this.generateToolbarButton('buttonRename', 'bi-input-cursor-text');
        this.buttonDelete = this.generateToolbarButton('buttonDelete', 'bi-trash');

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
    }

    checkRestRespData(data) {
        if (!utils.isObject(data) || !('code' in data)) {
            // Not return proper object.
            return false;
        }
        return true;
    }

    generateRestApi(api) {
        return (this.apiBase + this.restApiEndpoint + '?api=' + api);
    }

    generateDlApi() {
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
        let href = this.generateDlApi() + '?file=' + paramFile;
        return href;
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
            aDir.text(dirName);
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
        const folderTypes = ['folder'];
        const imageTypes = ['jpg', 'jpeg', 'bmp', 'png', 'gif'];
        const audioTypes = ['mp3'];

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
            } else if (imageTypes.includes(itemType)) {
                itemIsImage = true;
            } else if (audioTypes.includes(itemType)) {
                itemIsAudio = true;
            }

            let li = $('<li/>').addClass('detailLine list-group-item d-flex');
            if (itemIsFolder) {
                li.addClass('detailLineFolder');
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
                aFileLink.addClass('previewImage');
            } else if (itemIsAudio) {
                aFileLink.addClass('previewAudio');
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
            let spanFileName = $('<span/>').addClass('fileName text-truncate').text(itemName);
            aFileLink.append(spanFileName);
            divDetailLineLeft.append(aFileLink);

            li.append(divDetailLineLeft);

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

            li.append(divDetailLineRight);

            this.ulDetailView.append(li);
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
