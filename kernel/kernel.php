<?php

set_include_path(get_include_path() . PATH_SEPARATOR . "..");

include_once("base/HTML.php");
include_once("base/mysql_connection.php");
include_once("base/util.php");

class Kernel {

	/* File directory: For Windows, to strip leading C:/... off of directory so files can be correctly served by web server. (Probably not necessary for Linux Servers) */
	public $name, $directory, $file_directory, $file_filter, $icons, $thumbnails, $onClick, $dblClick;
	public $customOnClick, $customDblClick;

	function __construct ($directory=".", $name="browser", $file_filter="") {
		if ($directory == "" || $directory == "/")
			$directory = ".";

		if ($file_filter == "")
			$file_filter = new FileFilter();

		//if (startsWith($directory, "/"))
		//	$directory = substr($directory, 1);

		if (endsWith($directory, "/"))
			$directory = substr($directory, 0, -1);

		$root_dir = $_SERVER['DOCUMENT_ROOT'];
		if (!strncmp($directory, $root_dir, strlen($root_dir))) {
			$this->file_directory = substr($directory, strlen($root_dir));
		}
		else $this->file_directory = $directory;

		$file_filter->set_base_directory($directory);

		$this->directory = $directory;
		$this->name = $name;
		$this->file_filter = $file_filter;

		$this->onClick = array();
		$this->onClick['*'] = "select";

		$this->dblClick = array();
		$this->dblClick['folder'] = "explore";
		$this->dblClick['.bmp'] = "viewWebComponent";
		$this->dblClick['.jpg'] = "viewWebComponent";
		$this->dblClick['.gif'] = "viewWebComponent";
		$this->dblClick['.png'] = "viewWebComponent";
		$this->dblClick['.js'] = "edit";
		$this->dblClick['.html'] = "edit";
		$this->dblClick['.php'] = "edit";
		$this->dblClick['.txt'] = "edit";
		$this->dblClick['*'] = "edit";

		$this->file_icon = array();

		$this->icons = array();
		$this->icons['folder'] = "kernel/images/folder.gif";
		$this->icons['.jpg'] = "kernel/images/image.gif";
		$this->icons['.gif'] = "kernel/images/image.gif";
		$this->icons['.flv'] = "kernel/images/flv.gif";
		$this->icons['.js'] = "kernel/images/js.gif";
		$this->icons['.mp3'] = "kernel/images/mp3.gif";
		$this->icons['.php'] = "kernel/images/php.gif";
		$this->icons['.txt'] = "kernel/images/txt.gif";
		$this->icons['*'] = "kernel/images/unknown_type.gif";

		$this->customOnClick = array();
		$this->customDblClick = array();
	}

	function display_file ($filename) {
		return $this->file_filter->display_file($filename);
	}

	function display_thumbnails ($display=true) {
		$this->thumbnails = $display;
	}

	function get_kernel () {
		$kernel = unserialize($_SESSION['kernel']);
		if (!$kernel) {
			$kernel = new Kernel($directory, $name);
			Kernel::set_kernel($kernel);
		}
		return $kernel;
	}

	function set_kernel ($kernel) {
		$_SESSION['kernel'] = serialize($kernel);
	}

	function get_directory () {
		return $this->directory;
	}

	function clear_default_dblClick ($clear_folder=false) {
		foreach ($this->dblClick AS $type => $behavior) {
			if ($type == "folder") {
				if ($clear_folder)
					unset($this->dblClick[$type]);
			}
			else unset($this->dblClick[$type]);
		}
	}

	function prepend_onClick ($type, $function_name) {
		if (!isset($this->customOnClick[$type]))
			$this->customOnClick[$type] = array();
		$this->customOnClick[$type][] = $function_name;
	}

	function prepend_dblClick ($type, $function_name) {
		if (!isset($this->customDblClick[$type]))
			$this->customDblClick[$type] = array();
		$this->customDblClick[$type][] = $function_name;
	}

	function set_onClick ($type, $function_name) {
		$this->onClick[$type] = $function_name;
	}

	function set_dblClick ($type, $function_name) {
		$this->dblClick[$type] = $function_name;
	}

	function set_file_icon ($filename, $icon_filename) {
		$this->file_icons[$filename] = $icon_filename;
	}

	function get_click_behavior ($type, $click_behavior, $custom_click_behavior, $parameters) {
		if ($click_behavior[$type])
			$click = $click_behavior[$type];
		else $click = $click_behavior['*'];

		$click .= $parameters;

		if (isset($custom_click_behavior[$type])) {
			$nested_click = "";
			foreach ($custom_click_behavior[$type] as $click_function) {
				$nested_click .= "if ($click_function$parameters) ";
			}
			$click = $nested_click . $click;
		}
		return $click;
	}

	function get_file_divide ($file, $file_tag, $preview_images=false, $preview_filename=true) {
		global $html;

		if (is_dir($this->directory . "/" . $file)) {
			$type = "folder";
			if ($this->directory != "")
				$new_directory = "$this->directory/$file";
			else $new_directory = $file;

			if ($file == "..") {
				$last_slash_index = strrpos($this->directory, "/");
				$new_directory = substr($this->directory, 0, $last_slash_index);
			}
			$arguments = $new_directory;
		}
		else {
			$dot_index = strrpos($file, ".");
			$extension = substr($file, $dot_index, strlen($file));
			$type = strtolower($extension);

			if ($this->directory == "")
				$arguments = $file;
			else $arguments = "$this->directory/$file";
		}

		/* If file has icon assigned, use it. Otherwise use type-generic icon. */
		if (isset($this->file_icons["$this->directory/$file"])) {
			$icon = $this->file_icons["$this->directory/$file"];
		}
		else $icon = isset($this->icons[$type]) ? $this->icons[$type] : $this->icons['*'];

		if ($preview_images && ($type == ".jpg" || $type == ".gif" || $type == ".png")) {
			if ($this->directory != "") {
				// echo "$this->file_directory vs $this->directory";
				$icon = "preview_image.php?url={$this->file_directory}/{$file}&w=75&h=75&m=S";
			}
			else $icon = $file;
		}

		$image = $html->img()->id($file_tag . "_image")->src($icon)->class("kernel_icon");
		$file_div = $html->div()->add($image)->id($file_tag)->class("kernel_file");

		if ($preview_filename)
			$file_div->content("<BR>$file");

		$parameters = "(event, '$this->name', '$arguments', '$file_tag')";
		
		if ($this->onClick[$type])
			$onClick = $this->onClick[$type];
		else $onClick = $this->onClick['*'];

		$file_div->onClick = $this->get_click_behavior($type, $this->onClick, $this->customOnClick, $parameters);
		$file_div->onDblClick = $this->get_click_behavior($type, $this->dblClick, $this->customDblClick, $parameters);

		$file_type = $html->hidden()->id($file_tag . "_type")->value($type);
		$file_name = $html->hidden()->id($file_tag . "_name")->value("{$this->file_directory}/$file");
		$file_div->add($file_type)->add($file_name);

		return $file_div;
	}

	function get_files () {
		$files = array();

// echo "Directory: $this->directory<BR>";

		chdir($_SERVER['DOCUMENT_ROOT']);

		$directory = $this->directory;
		if (startsWith($directory, "./"))
			$directory = substr($directory, 2);

		$directory_handle = opendir($directory);
		while ($file = readdir($directory_handle)) {
			$files[] = $file;
		}
		sort($files);	// Order alphabetically.
		closedir($directory_handle);
		return $files;
	}

	function get_kernel_divide ($preview_images=false, $preview_filename=true) {
		global $html;

		$directory_hidden = $html->hidden()->id("{$this->name}_directory")->value($this->directory);

		/* So this can be persisted beyond changing folders. */
		if (isset($this->thumbnails)) {
			$preview_images = $this->thumbnails;
		}

		$explorer_div = $html->div()->id($this->name)->add($directory_hidden);
		$files = $this->get_files();

		/* First read all of the directories. */
		$i = 1;
		foreach ($files as $file) {
			if (!is_dir($file) || !$this->display_file($file))
				continue;
			else if ($file == "." || ($this->directory == "." && $file == ".."))
				continue;

			$file_tag = $this->name . "_" . $i++;
			$file_div = $this->get_file_divide($file, $file_tag, $preview_images, $preview_filename);

			$explorer_div->add($file_div);
		}

		$num_folders = $i;

		/* Next read all of the files. */
		foreach ($files as $file) {
			if (is_dir($file) || !$this->display_file($file))
				continue;

			$file_tag = $this->name . "_" . $i++;
			$file_div = $this->get_file_divide($file, $file_tag, $preview_images, $preview_filename);

			$explorer_div->add($file_div);
		}
		$select_multiple = $html->hidden("select_multiple")->value($this->select_multiple);
		$directory = $html->hidden()->id("directory")->value($this->directory);
		$num_files = $i - $num_folders;
		$num_total = $html->hidden()->id("num_total_files")->value($i);
		$num_folders = $html->hidden()->id("num_folders")->value($num_folders);
		$num_files = $html->hidden()->id("num_files")->id($num_files);

		$info_div = $html->div()->add($directory)->add($num_folders)->add($num_files)->add($num_total);
		$clear = $html->div()->class("clear");

		$explorer_div->add($info_div)->add($clear);

		return $explorer_div;
	}

	function set_directory ($directory) {
		if ($directory == "")
			$directory = ".";

		$this->directory = $directory;
		if (!strncmp($_SERVER['DOCUMENT_ROOT'], $directory, strlen($_SERVER['DOCUMENT_ROOT']))) {
			$this->file_directory = substr($directory, strlen($_SERVER['DOCUMENT_ROOT']));
		}
		$this->file_filter->set_base_directory($directory);
	}

}

class QueryKernel extends Kernel {

	public $query, $files;

	function __construct ($query) {
		parent::__construct();
		$this->query = $query;

		$this->files = array();
		$mysql_connection = new ARD_MySQLConnection();
		$results = $mysql_connection->sql($this->query);

		while ($results->has_next()) {
			$row = $results->next();
			$this->files[] = $row[filename];
			$this->directory = $row[path] . "/thumbs";
		}
	}

	function get_files () {
		return $this->files;
	}

}

class FileFilter {

	public $type, $base_directory;

	function __construct () {
		$this->type = "FileFilter";
	}

	function is_directory ($file) {
		$file_relative = $this->base_directory . $file;
// echo "Is directory: $file_relative<BR>";
		return is_dir($file_relative);
	}

	function set_base_directory ($directory) {
		if ($directory == "" || $directory == "/")
			return;
		else if (!endsWith($directory, "/"))
			$directory .= "/";

		$this->base_directory = $directory;
	}

	function display_file ($file) {
		return true;
	}

}

class QueryFileFilter extends FileFilter {

	public $query, $files;

	function __construct ($query) {
		$this->query = $query;
		$mysql_connection = new ARD_MySQLConnection();
		$results = $mysql_connection->sql($query);

		$this->files = array();
		while ($results->has_next()) {
			$row = $results->next();
			$this->files[] = $row[filename];
		}
		$this->type = "QueryFileFilter";
	}

	function display_file ($file) {
		if (is_dir($file))
			return false;

		return in_array($file, $this->files);
	}

}

class AntiQueryFileFilter extends QueryFileFilter {

	function __construct ($query) {
		parent::__construct($query);
		$this->type = "AntiQueryFileFilter";
	}

	function display_file ($file) {
		if ($this->is_directory($file))
			return false;

		return !parent::display_file($file);
	}

}

class ExtensionFileFilter extends FileFilter {

	public $extensions, $show_directories;

	function __construct ($extensions, $show_directories=true) {
		parent::__construct();
		if (!is_array($extensions))
			$this->extensions = array($extensions);
		else $this->extensions = $extensions;
		$this->type = "ExtensionFileFilter";
		$this->show_directories = $show_directories;
	}

	function display_file ($file) {
		if ($this->is_directory($file))
			return $this->show_directories;

		$extension = strrchr($file, ".");
		if ($extension == "")
			return false;

		$extension = strtolower($extension);
		$display = in_array($extension, $this->extensions);
		return $display;
	}

}

class ImageClassifierFilter extends ExtensionFileFilter {

	function __construct () {
		$extensions = array(".jpg", ".gif");
		parent::__construct($extensions);
		$this->type = "ImageClassifierFileFilter";
	}

	function display_file ($file) {
		if ($this->is_directory($file))
			return false;
		else return parent::display_file($file);
	}

}

class ImageDirectoryFilter extends ExtensionFileFilter {

	function __construct () {
		$extensions = array(".jpg", ".gif");
		parent::__construct($extensions);
		$this->type = "ImageDirectoryFileFilter";
	}

	function display_directory ($directory) {
		$less_relative_directory = $this->base_directory . $directory;
// echo "$less_relative_directory <BR>";
		$directory_handle = opendir($less_relative_directory);

		while ($file = readdir($directory_handle)) {
		 $filename = $directory . "/" . $file;
			if ($this->is_directory($file)) // Skip directories first.
				continue;

			$is_image = parent::display_file($filename);
			if ($is_image) {
				closedir($directory_handle);
				return true;
			}
		}
		rewinddir($directory_handle);
		while ($file = readdir($directory_handle)) {
			$filename = $directory . "/" . $file;
			if (!is_dir($filename) || $file == "." || $file == "..")
				continue;

			// See if the directory has images in it.
			$contains_images = $this->display_directory($filename);
			if ($contains_images) {
				closedir($directory_handle);
				return true;
			}
		}
		closedir($directory_handle);
		return false;
	}

	function display_file ($file) {
		$display = false;
		if ($file == ".")
			return false;

		if ($this->is_directory($file)) {
			if ($file == "..")
				$display = true;
			else $display = $this->display_directory($file);
		}
		else { // Do not display images at this time.
			return false; // $display = parent::display_file($file);
		}
		// echo "Display $file? $display<BR>";
		return $display;
	}

}

?>