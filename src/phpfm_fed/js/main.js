// css
require('../scss/bootstrap_phpfm.scss');
require('bootstrap/scss/bootstrap.scss');
require('bootstrap-icons/font/bootstrap-icons.css');
require('../css/main.css');

// js
window.$ = require('jquery');
require('bootstrap');
const utils = require('./utils');

class RosefinchPage {

    constructor() {
        this.divWrapper = $('#divWrapper');
        this.navToolbarWrapper = $('#navToolbarWrapper');
        this.navPathWrapper = $('#navPathWrapper');
        this.divMainWrapper = $('#divMainWrapper');
        this.divListWrapper = $('#divListWrapper');
        this.ulDetailView = $('#ulDetailView');
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
        let locationHash = window.location.hash;
        utils.log('RosefinchPage.onHashChange, locationHash=[%s]', locationHash);

        if (locationHash.startsWith('#')) {
            locationHash = locationHash.slice(1);
        }
    }
}

$(function () {
    utils.log('init, Rosefinch start...');

    let page = new RosefinchPage();
    page.initContent();
});
