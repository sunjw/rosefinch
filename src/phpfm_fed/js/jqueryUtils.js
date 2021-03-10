const jq = require('jquery');

function restRequest(method, api, reqObj, successCallback, errorCallback) {
    jq.ajax({
        type: method,
        url: api,
        data: reqObj ? JSON.stringify(reqObj) : null,
        contentType: "application/json",
        success: function (data) {
            if (successCallback) {
                successCallback(data);
            }
        },
        error: function () {
            if (errorCallback) {
                errorCallback();
            }
        }
    });
}

function postRestRequest(api, reqObj, successCallback, errorCallback) {
    restRequest('POST', api, reqObj, successCallback, errorCallback);
}

function getRestRequest(api, successCallback, errorCallback) {
    restRequest('GET', api, null, successCallback, errorCallback);
}

function focusOnInput(inputElem) {
    inputElem.focus();
    inputElem.get(0).select();
}

function stopBubble(event) {
    let event = event ? event : window.event;
    if (window.event) {
        event.cancelBubble = true;
    } else {
        event.stopPropagation();
    }
}

// exports
exports.restRequest = restRequest;
exports.postRestRequest = postRestRequest;
exports.getRestRequest = getRestRequest;
exports.focusOnInput = focusOnInput;
exports.stopBubble = stopBubble;
