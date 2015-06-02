
String.prototype.camelCase = function () {
    var parts = this.split(/[^a-z0-9]+/i);
    var camelCaseString = parts[0].toLowerCase();
    for (var i=1; i<parts.length; i++) {
        camelCaseString += parts[i].substring(0, 1).toUpperCase();
        camelCaseString += parts[i].substring(1);
    }
    return camelCaseString
};