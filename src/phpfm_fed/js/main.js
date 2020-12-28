// css
require('../css/main.css');

// js
window.$ = require('jquery');
const utils = require('./utils');

const bodyMinWidth = 250;
const pageMaxWidth = 1000;

function initContent() {

    initLayout();
    initFunc();

    // init call

}

function initLayout() {
    $('body').css({
        'min-width': bodyMinWidth + 'px'
    });

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

function initFunc() {}

$(function () {
    utils.log('Rosefinch start...');
    initContent();
});
