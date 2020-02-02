/* developed to stop other plugins and themes erroneous code from stopping our plugin from working */
window.onerror = function (errorMsg, url, lineNumber, column, errorObj) {
    if (window.console) { console.log('Error: ' + errorMsg + ' \nScript: ' + url + ' \nLine: ' + lineNumber + ' \nColumn: ' + column + ' \nStackTrace: ' +  errorObj); }
    return true;
}
