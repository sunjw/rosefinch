function log(...logString) {
    console.log(...logString);
}

function clone(obj) {
    let copy;

    // Handle the 3 simple types, and null or undefined
    if (null == obj || 'object' != typeof obj) {
        return obj;
    }

    // Handle Buffer
    if (obj instanceof Buffer) {
        copy = Buffer.from(obj);
        return copy;
    }

    // Handle Date
    if (obj instanceof Date) {
        copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (let i = 0, len = obj.length; i < len; i++) {
            copy[i] = clone(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (let attr in obj) {
            if (obj.hasOwnProperty(attr))
                copy[attr] = clone(obj[attr]);
        }
        return copy;
    }

    throw new Error('Unable to copy obj! Its type is not supported.');
}

function isString(variable) {
    return (typeof variable === 'string' || variable instanceof String);
}

function isObject(variable) {
    return (typeof variable === 'object' && variable !== null);
}

function stringReplaceAll(string, target, replace) {
    return string.replace(new RegExp(target, 'g'), replace);
}

function escapeShellPath(path) {
    const needEscape = [' ', '&', '*', '$', '?', '|', '"', '\'',
        ';', '<', '>', '#', '(', ')'];
    let pathEscape = path;
    for (let escape of needEscape) {
        let escapeReg = escape;
        if (escapeReg == '*' || escapeReg == '$' ||
            escapeReg == '?' || escapeReg == '|' ||
            escapeReg == '(' || escapeReg == ')') {
            escapeReg = '\\' + escapeReg;
        }
        pathEscape = stringReplaceAll(pathEscape, escapeReg, '\\' + escape);
    }
    return pathEscape;
}

function escapeHtmlPath(path) {
    let pathEscape = path;
    pathEscape = stringReplaceAll(pathEscape, '&', '&amp;');
    pathEscape = stringReplaceAll(pathEscape, '<', '&lt;');
    pathEscape = stringReplaceAll(pathEscape, '>', '&gt;');
    pathEscape = stringReplaceAll(pathEscape, ' ', '&nbsp;');
    return pathEscape;
}

function getRandomInt(max) {
    return Math.floor(Math.random() * Math.floor(max));
}

function byteSizeToShortSize(size) {
    const shortSizeUnit = ['K', 'M', 'G'];
    let shortSize = size;
    for (let i = 0; i < shortSizeUnit.length; ++i) {
        if (size >= 1024) {
            shortSize = (size / 1024.0).toFixed(2) + shortSizeUnit[i];
            size = size / 1024;
        } else {
            break;
        }
    }
    return shortSize;
}

function getParentDir(path) {
    let curPath = path;
    if (curPath.endsWith('/')) {
        curPath = curPath.substr(0, curPath.length - 1);
    }
    let pathDelimIdx = curPath.lastIndexOf('/');
    let parentDirPath = curPath.substr(0, pathDelimIdx);
    parentDirPath = parentDirPath + '/';
    return parentDirPath;
}

function getUrlQueryVariable(queryStr, key) {
    let vars = queryStr.split('&');
    for (let i = 0; i < vars.length; i++) {
        let pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == key) {
            return decodeURIComponent(pair[1]);
        }
    }
    return '';
}

function getQueryVariable(key) {
    let query = window.location.search.substring(1);
    return getUrlQueryVariable(query, key);
}

function getLocation() {
    return window.location.href;
}

function getFileName(fullPath) {
    return fullPath.replace(/^.*[\\\/]/, '');
}

function navToLocation(url) {
    window.location.href = url;
}

function navToHash(hash) {
    window.location.hash = hash;
}

// function isWindows() {
//     return (process.platform == 'win32');
// }

// function isMacOS() {
//     return (process.platform == 'darwin');
// }

// function fixWindowsPath(path) {
//     if (isWindows()) {
//         path = stringReplaceAll(path, '\/', '\\');
//     }
//     return path;
// }

// exports
exports.log = log;
exports.clone = clone;
exports.isString = isString;
exports.isObject = isObject;
exports.stringReplaceAll = stringReplaceAll;
exports.escapeShellPath = escapeShellPath;
exports.escapeHtmlPath = escapeHtmlPath;
exports.getRandomInt = getRandomInt;
exports.byteSizeToShortSize = byteSizeToShortSize;
exports.getParentDir = getParentDir;
exports.getUrlQueryVariable = getUrlQueryVariable;
exports.getQueryVariable = getQueryVariable;
exports.getLocation = getLocation;
exports.getFileName = getFileName;
exports.navToLocation = navToLocation;
exports.navToHash = navToHash;
// exports.isWindows = isWindows;
// exports.isMacOS = isMacOS;
// exports.fixWindowsPath = fixWindowsPath;
