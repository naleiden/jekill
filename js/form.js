
(function ($) {

    $.fn.Ajaxify = function (config) {
        config = $.extend({
            error: null,
            fail: null,
            success: null,
            warn: null,
            requestType: $(this).data("request-type") ? $(this).data("request-type") : "json"
        }, config);

        var $form = $(this),
            method = $form.attr("method"),
            url = $form.attr("action");

        $form.formCheck({
            requiredSelector: ".required",
            success: function (arguments) {
                _submitAjaxRequest(arguments);
                // Prevent default form submission.
                return false;
            }
        });

        function _submitAjaxRequest (arguments) {
            arguments['_request_type'] = config.requestType;

            $.ajax({
                url: url,
                type: method,
                dataType: config.requestType,
                data: arguments,
                error: function (xhr, error) {
                    _handleAjaxFailure(error);
                },
                success: function (response) {
                    _evaluateAjaxResponse(response);
                }
            });
        }

        function _evaluateAjaxResponse (response) {
            if (config.requestType == "json") {
                _evaluateJsonResponse(response);
            } else {
                // HTML or other content, etc.
                _executeCallback(config.success, response);
            }
        }

        function _evaluateJsonResponse (response) {
            var responseJson = response;    // $.parseJSON(response);
            // A response object?
            if (responseJson.status) {
                if (responseJson.status == "success") {
                    _executeCallback(config.success, responseJson);
                } else if (responseJson.status == "success-warn") {
                    if ($.isFunction(config.warn)) {
                        _executeCallback(config.warn, responseJson);
                    } else {
                        _executeCallback(config.success, responseJson);
                    }
                } else if ($.isFunction(config.error)) {
                    _executeCallback(config.error, responseJson);
                } else {
                    _reportError(response.errors);
                }
            } else {
                _executeCallback(config.success, responseJson);
            }
        }

        function _executeCallback (callback, response) {
            if ($.isFunction(callback)) {
                callback.call(this, response);
            }
        }

        function _handleAjaxFailure (error) {
            if ($.isFunction(config.fail)) {
                config.fail.call(this, error);
            } else {
                _reportError("Sorry, there was an unknown error submitting your request. Please try again later.");
            }
        }

        function _reportError (error) {
            if (typeof error != "string") {
                if (error.length > 1) {
                    error = "We encountered the following errors when submitting your request:<ul><li>" + error.join("</li><li>") + "</li></ul>";
                } else {
                    error = error[0];
                }
            }

            $("<div/>").html(error).dialog({
                 buttons: {
                     "Ok": function () { $(this).dialog("close"); }
                 },
                 modal: true,
                 resizable: false
            });
        }
    };

    $.fn.formCheck = function (config) {

        config = $.extend({
            requiredSelector: null,	// A selector describing required inputs. This or optionalSelector must be defined.
            optionalSelector: null,	// A selector describing optional inputs
            errorClass: "error-input",
            success: null
        }, config);

        var $form = $(this);

        // Remove error class on focus
        $("input, select, textarea").on("focus", function () {
            $(this).removeClass(config.errorClass);
        });

        $form.on("submit", function (e) {
            var $self = $(this),
                errorFields = { },
                numErrors = 0,
                values = { };

            $("input, select, textarea", $self).each(function () {

                var $input = $(this),
                    value = $input.val(),
                    validator = $input.data("validator"),
                    regex = $input.data("regex"),
                // If optionalSelector is defined, anything not optional is required. Otherwise see if the input is required.
                    required = (config.optionalSelector) ? !$input.is(config.optionalSelector) : $input.is(config.requiredSelector),
                    // Field is required, or has a value and a validator.
                    inputName = $input.attr('name') ? $input.attr("name") : $input.attr("id");

                if ($input.attr("type") == "checkbox") {
                    if (!$input.is(":checked")) {
                        return true;
                    }
                }

                if (typeof value == "string") {
                    value = trim(value);
                }
                // Array, etc.
                else if (!value) {
                    value = "";
                }

                if (!inputName) {
                    return true;    // continue
                }

                // Don't check disabled fields.
                if ($input.prop("disabled")) {
                    return true;	// true will not break loop.
                }
                // Optional, no value, or value and no defined validator to evaluate value.
                else if (!required && (!value || (!validator && !regex))) {
                    values[inputName] = value;
                    return true;
                }

                if (validator && window[validator]) {
                    try {
                        value = window[validator](value);
                    } catch (error) {
                        value = "";
                    }
                }
                else if (regex) {
                    var $regex = new RegExp(regex);

                    if (!$regex.test(value))
                        value = "";
                }

                if (!value) {
                    $input.addClass("error-input");

                    errorFields[inputName] = $input;
                    numErrors++;
                }
                else {
                    $input.removeClass("error-input");
                    values[inputName] = value;
                }
            });

            var $error = $(".error", $form);
            if (numErrors) {
                e.preventDefault();
                if (!$error.size()) {
                    $error = $("<div/>").addClass("error")
                                        .prependTo($form);
                }
                $error.html("Please complete the form.")
                    .slideDown("slow");
                return false;
            }
            else if (config.success) {
                $error.fadeOut("fast");
                return config.success.call(this, values);
            }
            else return true;
        });
    };

})(jQuery);
