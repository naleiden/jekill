<?php

include("header.php");

$html->import_style("css/v3/subpage.css");

ob_start(); ?>

	<div class="page-callout">
		Sorry, the page you're looking<br/> for could not be found.
	</div>

<?php

$content_HTML = ob_get_clean();

$content->content($content_HTML);

include("footer.php");

?>