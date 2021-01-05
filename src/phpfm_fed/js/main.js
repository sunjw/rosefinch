// css
require('../scss/bootstrap_phpfm.scss');
require('bootstrap/scss/bootstrap.scss');
require('bootstrap-icons/font/bootstrap-icons.css');
require('../css/main.css');

// js
const dayjs = require('dayjs/dayjs.min');
const filesize = require('filesize');
window.$ = require('jquery');
require('bootstrap');
const utils = require('./utils');
const jqueryUtils = require('./jqueryUtils');

function formatSize(size) {
    return filesize(size, {spacer: ''});
}

function formatTimestamp(timestamp) {
    return dayjs.unix(timestamp).format('YYYY-MM-DD HH:mm');
}

class RosefinchPage {

    constructor(apiPrefix) {
        // consts
        this.apiBase = utils.isString(apiPrefix) ? apiPrefix : '';
        this.apiEndpoint = 'func/rest.api.php';

        // elements
        this.divWrapper = $('#divWrapper');
        this.navToolbarWrapper = $('#navToolbarWrapper');
        this.navPathWrapper = $('#navPathWrapper');
        this.divMainWrapper = $('#divMainWrapper');
        this.divListWrapper = $('#divListWrapper');
        this.ulDetailView = $('#ulDetailView');

        // vars
        this.currentDir = '';
        this.sort = '';
        this.order = '';

        this.mainList = null;
    }

    initContent() {
        this.initLayout();
        this.initFunc();

        // init call
    }

    initLayout() {
        let that = this;

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
    }

    onHashChange() {
        let that = this;

        let locationHash = window.location.hash;
        utils.log('RosefinchPage.onHashChange, locationHash=[%s]', locationHash);

        if (locationHash.startsWith('#')) {
            locationHash = locationHash.slice(1);
        }

        let requestDir = utils.getUrlQueryVariable(locationHash, 'dir');
        let requestSort = utils.getUrlQueryVariable(locationHash, 's');
        let requestOrder = utils.getUrlQueryVariable(locationHash, 'o');

        let requestApi = this.prepareRestApi('api/v1/fm/ls');
        requestApi += ('&s=' + requestSort);
        requestApi += ('&o=' + requestOrder);
        requestApi += ('&dir=' + requestDir);
        utils.log('RosefinchPage.onHashChange, requestApi=[%s]', requestApi);

        jqueryUtils.getRestRequest(requestApi, function (data) {
            if (!that.checkRestRespData(data)) {
                return;
            }

            that.mainList = data.data['main_list'];
            if (!Array.isArray(that.mainList)) {
                utils.log('RosefinchPage.onHashChange, mainList not an Array.');
                return;
            }

            that.renderMainList();
        })
    }

    prepareRestApi(api) {
        return (this.apiBase + this.apiEndpoint + '?api=' + api);
    }

    checkRestRespData(data) {
        if (!utils.isObject(data) || !('code' in data)) {
            // Not return proper object.
            return false;
        }
        return true;
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

            // left
            let divDetailLineLeftPart = $('<div/>').addClass('detailLineLeftPart d-flex flex-grow-1');

            let spanFileCheck = $('<span/>').addClass('fileCheck d-flex align-items-center');
            let inputCheckbox = $('<input/>').attr({
                'type': 'checkbox',
                'name': item['item_path']
            });
            spanFileCheck.append(inputCheckbox);
            divDetailLineLeftPart.append(spanFileCheck);

            let aFileLink = $('<a/>').addClass('fileLink noOutline flex-grow-1 d-flex align-items-center');
            if (itemIsImage) {
                aFileLink.addClass('previewImage');
            } else if (itemIsAudio) {
                aFileLink.addClass('previewAudio');
            }
            aFileLink.attr({
                'title': item['name'],
                'href': '#'
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
            let spanFileName = $('<span/>').addClass('fileName').text(item['name']);
            aFileLink.append(spanFileName);
            divDetailLineLeftPart.append(aFileLink);

            li.append(divDetailLineLeftPart);

            // right
            let divDetailLineRightPart = $('<div/>').addClass('detailLineRightPart d-flex align-items-center');

            let spanFileType = $('<span/>').addClass('fileType').text(item['type_html']);
            divDetailLineRightPart.append(spanFileType);
            let spanFileSize = $('<span/>').addClass('fileSize')
            if (itemIsFolder) {
                spanFileSize.html('&nbsp;');
            } else {
                spanFileSize.text(formatSize(item['size']));
            }
            divDetailLineRightPart.append(spanFileSize);
            let spanFileTime = $('<span/>').addClass('fileTime').text(formatTimestamp(item['mtime']));
            divDetailLineRightPart.append(spanFileTime);

            li.append(divDetailLineRightPart);

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
