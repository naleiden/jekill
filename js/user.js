/***********/
/*  Login  */
/***********/

function loadLogin (action) {
	var params = "success=";
	if (typeof(action) == "function")
		params += escape(action);
	else params += "'" + escape(action) + "'";
	openInnerWindow("login", "users/login.php", 300, "", "", params, "", "", false, 1, 1);
}

/* Use to ensure a visiter is logged in. */
function verifyLogin (action) {
	var userID = $("#user_ID").val();
	if (userID == "") {
		loadLogin(action);
		return false;
	}
	return true;
}

function loginGate (action) {
	var loggedIn = verifyLogin(action);
	if (loggedIn) {
		if (typeof(action) == "function")
			action.call(this);
		else window.location = action;
	}	
}

function login (successFunc) {
	var loginType = $("#login_type").val();
	var userID = $("#login_" + loginType).val();
	var password = $("#login_password").val();
	var remainLoggedIn = $("#remain_logged_in").attr("checked") ? true : false;

	var validUserID = false;
	if (loginType == "email") {
		validUserID = isValidEmail(userID);
}	
	else validUserID = isAlphaNumeric(userID);

	if (!validUserID || trim(password) == "") {
		$("#login_error").html("Please enter a valid " + loginType + " and password.").fadeIn("slow");
		return;
	}

	$.post("users/login_script.php", { login_type: loginType, user_identifier: userID, password: password, remain_logged_in: remainLoggedIn }, function (response) { loginCallback(response, successFunc); });
}

function loginCallback (response, successFunc) {

	if (Number(response) > 0) {

		if ($("#remain_logged_in").attr("checked"))
			setCookie("TRTTT_user_ID_COOKIE", response, "30");

		if (isInnerWindow("login"))
			closeInnerWindow("login");
		$("#user_ID").val(response);
		// $("#login_divide").html("You are logged in. <A href=\"log_out.php\">Log Out</A>");

		if (successFunc != "undefined" && typeof(successFunc) != "undefined") {
			if (typeof(successFunc) == "function")
				successFunc.call(this);
			else if (successFunc != "")
				window.location = successFunc;
		}
		else window.location = "index.php";
	}
	else {
		$("#login_error").html("The email and password you entered did not match.").fadeIn("slow");
		$("#password").val("");
		// $("#login_button").attr("disabled", "");
	}
}

function forgotPassword () {
	var email = $("#login_email").val();
	if (!isValidEmail(email)) {
		$("#login_error").html("Please enter a valid email to send a new password to.").fadeIn("slow");
		return;
	}
	$.post("users/password_reminder_email.php", { email: email }, function (response) { resetPasswordCallback(response); });
}

function resetPasswordCallback (response) {
	if (Number(response) == 0) {
		$("#login_error").html("The email you entered was not recognized by the system.").fadeIn("slow");
		return;
	}
	var email = $("#login_email").val();
	alert("A new password has been sent to '" + email + "'.");
}