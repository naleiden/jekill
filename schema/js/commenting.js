function comment () {
	swapInteract("comment", 350);
}

function validateComment () {
	var nickname = $("#nickname").val();
	var comment = $("#segment_comment").val();
	var email = $("#email").val();
	var error = "";

	if (trim(comment) == "") {
		$("#segment_comment").addClass("error_input");
		error += "<LI>Please supply a comment.";
	}
	else $("#segment_comment").removeClass("error_input");

	if (trim(nickname) == "") {
		$("#nickname").addClass("error_input");
		error += "<LI>Please supply your name.";
	}
	else $("#nickname").removeClass("error_input");

	if (!isValidEmail(email)) {
		$("#email").addClass("error_input");
		error += "<LI>Please supply a valid email.";
	}
	else $("#email").removeClass("error_input");
		
	if (error != "") {
		error = "Oops! You forgot to give us some required info!<P><UL>" + error + "</UL>";
		$("#error").html("Please supply your name, a valid email, and a comment.").slideDown("slow");
		return false;
	}
	else {
		$("#error").slideUp("slow");
		$("#comment").animate({ height: 415 }, "slow");
		return true;
	}
}

function submitComment () {
	submitCommentVerifyHuman();
/* // No longer require login. (Email included in form.)
	var success = function () { submitCommentVerifyHuman(); };
	loginGate(success);
*/
}

function submitCommentVerifyHuman () {
	if (!validateComment())
		return;

	if (!$("#verify_text").is("input")) {
		loadVerification("comment_verification");
	}
	else {
		$("#comment_verification").slideDown("slow");
		verify($("#verify_text").val(), "comment_verification", verifiedSubmitComment);
	}
}

function verifiedSubmitComment () {
	var nickname = $("#nickname").val();
	var comment = $("#segment_comment").val();
	var email = $("#email").val();
	var website = $("#website").val();
	var entityType = $("#entity_type").val();
	var entityID = $("#entity_ID").val();
	var subEntityID = $("#sub_entity_ID").val();

	$.post("ajax/save_comment.php", { nickname: nickname, email: email, website: website, entity_type: entityType, entity_ID: entityID, sub_entity_ID: subEntityID, comment: comment }, function (response) { putComment(response); });
}

function putComment (commentHTML) {
	closeInteraction();
	$("#segment_comment").val("");
	$("#comment_verification").html("");
	$("#comments").prepend(commentHTML);
}

function swapInteract (openID, height) {
	if (typeof(height) == "undefined")
		height = 175;

	$(".interact").animate({height: 0}, "slow", function () {
		$(".interact").css("display", "none").css("border-width", 0);
		$("#" + openID).css("display", "block");
		$("#" + openID).animate({height: height}, "slow", function () { $("#" + openID).css("border-width", 1); });
	});
}

function closeInteraction () {
	$(".interact").animate({height: 0}, "slow", function () {
		$(".interact").css("border-width", 0).css("display", "none");
	});
}