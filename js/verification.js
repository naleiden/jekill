function loadVerification (divID) {
	// load(divID, "/verification/verification.php", "divide_ID=" + divID);
	$.post("/verification/verification.php", { divide_ID: divID }, function (response) { $("#" + divID).html(response); });
}

function verify (verification, divideID, callback) {
	$.post("/verification/verify.php", { verification: verification }, function (response) { verifyCallback(response, divideID, callback); });
}

function verifyCallback (response, divideID, callback) {
	if (Number(response) != 1) {
		loadVerification(divideID);
	}
	else {
		callback.call(this);
	}
}