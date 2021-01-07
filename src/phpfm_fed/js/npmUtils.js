// npm install dayjs --save
const dayjs = require('dayjs/dayjs.min');
// npm install filesize --save
const filesize = require('filesize');

function formatSize(size) {
    return filesize(size);
}

function formatTimestamp(timestamp) {
    return dayjs.unix(timestamp).format('YYYY-MM-DD HH:mm');
}

// exports
exports.formatSize = formatSize;
exports.formatTimestamp = formatTimestamp;
