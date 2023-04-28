// npm install dayjs --save
const dayjs = require('dayjs/dayjs.min');
// npm install filesize --save
const filesize = require('filesize');
const platform = require('platform');

function formatSize(size) {
    return filesize.filesize(size);
}

function formatTimestamp(timestamp) {
    return dayjs.unix(timestamp).format('YYYY-MM-DD HH:mm');
}

function getPlatformOS() {
    return platform.os.toString();
}

function isMacOS() {
    let platformOS = getPlatformOS();
    return (platformOS.indexOf('macOS') > -1 || platformOS.indexOf('OS X') > -1);
}

function isiOS() {
    return (getPlatformOS().indexOf('iOS') > -1);
}

// exports
exports.formatSize = formatSize;
exports.formatTimestamp = formatTimestamp;
exports.getPlatformOS = getPlatformOS;
exports.isMacOS = isMacOS;
exports.isiOS = isiOS;
