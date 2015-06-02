<?php

include_once("base/HTML.php");
include_once("kernel/kernel.php");

$element_name = $_POST['element_name'];

$extensions = array(".jpg", ".jpeg", ".gif", ".png");
$file_filter = new ExtensionFileFilter($extensions, false);

$html = new HTML();

$kernel = new Kernel("{$_SERVER['DOCUMENT_ROOT']}/images", "image_explorer", $file_filter);

$kernel->clear_default_dblClick();
$kernel_div = $kernel->get_kernel_divide(true, true);

$kernel_div->style("overflow: auto; width: 475px; height: 300px");

$accept = $html->button()->value("Select Image")->onClick("selectImage('$element_name')");

$upload_label = $html->span()->content("Upload Image");
$upload_frame = $html->iframe()->class("inline")->frameborder(0)->src("upload_file.php?directory={$_SERVER['DOCUMENT_ROOT']}/images&callback=window.parent.loadJImageEditor('{$element_name}')");

$file_select_div = $html->div()->class("right");
$control_div = $html->div()->class("right")->add($upload_frame)->content("<br />")->add($accept);

$control_div->set_padding(10);

$file_select_div->add($kernel_div);
$file_select_div->add($control_div);

echo $file_select_div->html();

?>