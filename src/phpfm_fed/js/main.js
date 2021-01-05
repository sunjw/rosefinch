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
    }

    initContent() {
        this.initLayout();
        this.initFunc();

        // init call

    }

    initLayout() {
        var that = this;

        this.onWindowResize();
        $(window).on('resize', function () {
            that.onWindowResize();
        });
    }

    onWindowResize() {
        var windowWidth = $(window).width();
        var windowHeight = $(window).height();
        utils.log('RosefinchPage.onWindowResize, windowWidth=' + windowWidth + 'px, windowHeight=' + windowHeight + 'px');
    }

    initFunc() {
    }
}

// variables
var page = new RosefinchPage();

$(function () {
    utils.log('Rosefinch start...');
    page.initContent();
});
