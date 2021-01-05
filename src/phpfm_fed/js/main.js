// css
require('../scss/bootstrap_phpfm.scss');
require('bootstrap/scss/bootstrap.scss');
require('bootstrap-icons/font/bootstrap-icons.css');
require('../css/main.css');

// js
window.$ = require('jquery');
require('bootstrap');
const utils = require('./utils');
const jqueryUtils = require('./jqueryUtils');

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

            let mainList = data.data['main_list'];
            if (Array.isArray(mainList)) {
                utils.log('RosefinchPage.onHashChange, mainList.length=%d', mainList.length);
            } else {
                utils.log('RosefinchPage.onHashChange, mainList not an Array.');
            }

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
