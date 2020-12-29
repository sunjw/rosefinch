// css
require('../css/main.css');
require('../scss/bootstrap_phpfm.scss');
require('bootstrap/scss/bootstrap.scss')

// js
window.$ = require('jquery');
require('bootstrap');
const utils = require('./utils');

const pageMaxWidth = 1000;

function initContent() {

    initLayout();
    initFunc();

    // init call

}

function initLayout() {

    onWindowResize();
    $(window).resize(function () {
        onWindowResize();
    });

}

function onWindowResize() {
    var windowWidth = $(window).width();
    utils.log('onWindowResize, windowWidth=' + windowWidth);
    var pageWidth = windowWidth;
    if (pageWidth > pageMaxWidth) {
        pageWidth = pageMaxWidth;
    }

}

function initFunc() {
}

$(function () {
    utils.log('Rosefinch start...');
    initContent();
});
