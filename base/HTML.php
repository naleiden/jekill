<?php

class Content {

	protected $content;

	function __construct ($content) {
		$this->set($content);
	}

	function add ($content) {
		$this->content .= $content;
	}

	function content ($content) {
		$this->add($content);
	}

	function set ($content) {
		$this->content = $content;
	}

	function html () {
		if (is_object($this->content))
			throw new Exception("Element supplied as content: <" . $this->content->tag . "> " . var_dump($this->content->attributes));

		return $this->content;
	}

}

class Element extends Content {

	protected $html;
	protected $elements;
	protected $attributes;
	protected $tag, $close_tag, $inline_element;

	function __construct ($html, $tag, $parameters="") {
		$this->html = $html;
		$this->tag = strtolower($tag);

		if (!isset($parameters) || !isset($parameters['close_tag']))
			$this->close_tag = true;
		else {
			$this->close_tag = $parameters['close_tag'];
			unset($parameters[close_tag]);
		}

		$this->attributes = array();

		if (!isset($parameters)) {
			foreach ($parameters as $key => $value)
				$this->attributes[$key] = $value;
		}

		switch ($this->tag) {
			case "br":
			case "img":
			case "input":
			case "link":
			case "meta":
				$this->close_tag = false;
				break;

			default:
				$this->close_tag = true;
		}
		switch ($this->tag) {
			case "a":
			case "span":
				$this->inline_element = true;
				break;

			default:
				$this->inline_element = false;
		}
	}

	function add ($element, $index="") {
		if (!is_object($element))
			$element = new Content($element);

		if (!isset($this->elements))
			$this->elements = array();
		// TODO if ($index == "")
			$this->elements[] = $element;
		/*else {
			if ($index < 0)
				$index = count($this->elements) - $index;
			
		}*/
		return $this;
	}

	// Add this element to the other element.
	function add_to ($element) {
		$element->add($this);

		return $this;
	}

	function content ($content) {
		if( strtolower($this->tag) != "meta" ) {
			$content = new Content($content);
			$this->add($content);
		}
		else {
			$this->__set("CONTENT",$content);
		}
		return $this;
	}

	function __call ($function_name, $params) {
		$this->attributes[$function_name] = $params[0];
		return $this;
	}

	function data ($data_attr, $value) {
		if (strncmp($data_attr, "data-", 5)) {
			$data_attr = "data-" . $data_attr;
		}
		$this->__set($data_attr, $value);

		return $this;
	}

	function get_children () {
		return $this->elements;
	}

	function get_num_children () {
		return count($this->elements);
	}

	function __get ($fieldname) {
		return $this->attributes[$fieldname];
	}

	function __set ($fieldname, $value) {
		if (!isset($this->attributes[$fieldname]))
			$old_value = "";
		else $old_value = $this->attributes[$fieldname];
		$this->attributes[$fieldname] = $value;

		return $this;
	}

	function get_nth ($index) {
		return $this->elements[$index];
	}

	/* Retrieves the first element where $attributes[$property] = $value. */
	function get ($property, $value) {
		if ($this->elements == "")
			return false;

		/* See if the element you're looking for is a direct child. */
		foreach ($this->elements as $child) {
			if (get_class($child) == "Content")
				continue;

			if ($child->attributes[$property] == $value)
				return $child;
		}
		/* Recursively search through child elements. */
		foreach ($this->elements as $child) {
			if (get_class($child) == "Content")
				continue;

			$result = $child->get($property, $value);
			if ($result === false)
				continue;

			return $result;
		}
		return false;
	}

	function is_set ($fieldname) {
		return isset($this->attributes[$fieldname]);
	}

	function name ($name) {
		$this->attributes["name"] = $name;
		$this->attributes["id"] = $name;
		return $this;
	}

	function id ($id) {
		switch ($this->tag) {
			case "div":
			case "img":
			case "table":
			case "form":
			case "span":
				$this->attributes["id"] = $id;
				break;

			default:
				$this->attributes["id"] = $id;
				$this->attributes["name"] = $id;
		}

		return $this;
	}

	function prepend ($element) {
		if (!is_object($element))
			$element = new Content($element);

		if (!isset($this->elements))
			$this->elements = array();
		array_unshift($this->elements, $element);
		return $this;
	}

	function remove_all () {
		$this->elements = array();
		return $this;
	}

	function html ($indent="") {
		global $DEBUG_COMMENT;

		if ($this->tag)
			$element_out = "{$indent}<$this->tag";

		foreach ($this->attributes as $key => $value) {
			if (is_object($value)) {
				// echo "<B>Error:</B> Element supplied for attribute {$key}";
			}
			$element_out .= ' '.strtolower($key)."=\"$value\"";
		}

		if ($this->tag) {
			$tag_cap = ">";
			if (!$this->close_tag)
				$tag_cap = " />";
			
			$element_out .= $tag_cap;
		}

		$subelements = "";
		$inline = (count($this->elements) <= 1);
		if (isset($this->elements)) {
			foreach ($this->elements as $element) {
				$subelement_indent = "";
				if (!$inline) {
					$subelements .= "\n";
					$subelement_indent = "{$indent}\t";
				}
				$subelements .= $element->html($subelement_indent);
			}
			if (!$inline)
				$subelements .= "\n";
		}
		$element_out .= $subelements;

		if ($this->close_tag) {
			if (!$inline)
				$element_out .= "\n{$indent}";

			if ($this->tag) {
				$element_out .= "</{$this->tag}>";

				if ($DEBUG_COMMENT && !$inline) {
					if ($this->id != "")
						$element_out .= "<!-- #{$this->id} -->";
					else if ($this->class != "")
						$element_out .= "<!-- .{$this->class} -->";
				}
			}
		}

		return $element_out;
	}

}

class ConditionalImportElement extends Element {

	protected $condition;

	function __construct ($html, $condition) {
		parent::__construct(html, "");
		$this->condition = $condition;
	}

	function html ($indent="") {
		$html = "{$indent}<!--[if {$this->condition}]>";
		foreach ($this->elements AS $element) {
			$html .= "\n" . $element->html($indent . "\t");
		} 
		$html .= "\n{$indent}<![endif]-->";

		return $html;
	}

}

// An element that does not render itself.
class NullElement extends Element {

	function __construct ($html) {
		parent::__construct($html, "", "");
	}

	function html ($indent="") {
		return "";
	}

}

// A container that renders its children, but not itself.
class TransientContainer extends Element {

	function __construct ($html) {
		parent::__construct($html, "", "");
	}

}

class Table extends Element {

	public $cols, $current_row_num;

	function __construct ($html, $cols) {
		parent::__construct($html, "TABLE");
		$this->cols = $cols;
		$this->current_row_num = 0;
	}

	function add_row ($row) {
		parent::add($row);
		return $this;
	}

	function add_datum ($data, $colspan=1, $rowspan=1) {
		$datum = $this->html->td()->colspan($colspan)->rowspan($rowspan);
		$datum->add($data);
		$this->add($datum);
		return $this;
	}

	function add ($datum) {
		if (!$datum->is_set("colspan"))
			$datum->colspan = 1;
		if (!$datum->is_set("rowspan"))
			$datum->rowspan = 1;

		$num_rows = count($this->elements);
		if ($num_rows == 0) {
			$current_row = $this->html->tr();

			parent::add($current_row);
			$space_in_row = $this->cols;
		}
		else {
			$current_row = $this->elements[$this->current_row_num];
			$space_in_row = $this->cols;
			foreach ($current_row->elements as $element) {
				$colspan = 1;
				if ($colspan < $element->colspan)
					$colspan = $element->colspan;
				$space_in_row -= $colspan;
			}
		}
		if ($space_in_row < $datum->colspan) {
			$this->current_row_num++;
			if ($this->current_row_num == $num_rows) {	 // We need to add a new row
				$current_row = $this->html->tr();
				parent::add($current_row);
			}
			else $current_row = $this->elements[$this->current_row_num]; // We already have a next row
		}
		$current_row->add($datum);
		for ($i=1; $i<$datum->rowspan; $i++) {
			if ($this->elements[$this->current_row_num+$i] != "")
				$new_row = $this->elements[$this->current_row_num+$i];
			else {
				$new_row = $this->html->tr();
				parent::add($new_row);
			}

			for ($j=0; $j<$datum->colspan; $j++)
				$new_row->content("<!-- Table Spacer -->");
		}
		return $this;
	}

}

class HTML extends Element {

	public $title, $stylesheet, $doctype;
	public $head, $body, $style, $script, $script_container, $initialization;

	function __construct ($title="", $stylesheet="", $meta_desc="", $meta_keywords="", $charset="utf-8") {
		parent::__construct(null, "HTML");

		$this->head = $this->head();
		$this->body = $this->body();
		$this->initialization = new Content("");	// We will be able to add to this content block later, in init(), below.
		$this->script_container = new TransientContainer($this);	// This will hold the scripts that we import, so that they precede any init() content.
		$init_script = $this->script()->type("text/javascript")->add($this->initialization);
		$this->script = $this->scripts()->add($this->script_container)->add($init_script);
		$this->style = $this->style()->type("text/css");

		$this->title = $this->title()->content($title);
		$this->head->add($this->title);
		$metaDesc = $this->meta()->name("Description")->content($meta_desc);
		$this->head->add($metaDesc);
		$metaKW	= $this->meta()->name("Keywords")->content($meta_keywords);
		$this->head->add($metaKW);
		$metaEnc = $this->meta()->content("text/html;charset={$charset}");
		$metaEnc->__set("http-equiv","Content-Type");
		$this->head->add($metaEnc);
		$this->head->add($this->style);
		
		if ($stylesheet != "") {
			if (!is_array($stylesheet))
				$stylesheet = array($stylesheet);

			foreach ($stylesheet as $css) {
				$this->use_stylesheet($css);
			}
		}
		$this->set_doctype('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');

		/* Make sure head and body are not added to $this->body via override. */
		parent::add($this->head);
		parent::add($this->body);
	}

	function add ($element) {
		$this->body->add($element);
		return $this;
	}

	function button () {
		return $this->input()->type("button");
	}

	function __call ($function_name, $params) {
		return new Element($this, $function_name, $params);
	}

	function checkbox () {
		return $this->input()->type("checkbox");
	}

	function file () {
		return $this->input()->type("file");
	}

	function flash ($filename, $width, $height) {
	}

	function hidden () {
		return $this->input()->type("hidden");
	}

	function prepend_style ($css_file, $media="screen", $condition="") {
		$this->import_style($css_file, $media, $condition, "prepend");
	}

	function import_style ($css_file, $media="screen", $condition="", $action="add") {
		if (is_array($css_file)) {
			foreach ($css_file AS $css) {
				$this->import_style($css, $media, $ie_condition);
			}
		}
		else {
			$parent = $this->head;
			if ($condition) {
				$parent = new ConditionalImportElement($this, $condition);
				$this->head->add($parent);
			}
			$stylesheet_link = $this->link()->rel("stylesheet")->href($css_file)->type("text/css")->media($media);
			$parent->$action($stylesheet_link);
		}
		return $this;
	}

	function import ($js_file) {
		if (is_array($js_file)) {
			foreach ($js_file AS $file) {
				$this->import($file);
			}
			return $this;
		}
		$script = $this->script()->type("text/javascript")->src($js_file);
		$this->script_container->add($script);
		return $this;
	}

	function init ($init) {
		$this->initialization->content("\n\t\t{$init}");
	}

	function inline ($tagname) {
		$element = $this->$tagname();
		$this->add($element);
		return $element;
	}

	function null () {
		return new NullElement($this);
	}

	function radio () {
		return $this->input()->type("radio");
	}

	function select ($options, $value="") {
		$select = new Element($this, "select");
		if ($value != "")
			$select->value = $value;
		foreach ($options as $option_value => $text) {
			$option = $this->option()->value($option_value)->content($text);
			if (is_array($value)) {
				// TODO: Copy array and unset as matches are made.
				foreach ($value AS $val) {
					if ($val == $option_value) {
						$option->selected("true");
					}
				}
			}
			else {
				if ($value == $option_value)
					$option->selected("true");
			}
			$select->add($option);
		}
		return $select;
	}

	function set_doctype ($doctype) {
		$this->doctype = $doctype;
	}

	function submit () {
		return $this->input()->type("submit");
	}

	function table ($cols=1) {
		return new Table($this, $cols);
	}

	function text () {
		return $this->input()->type("text");
	}

	function audio ($audio_URL, $id, $width, $height=30) {
		$this->import("/flowplayer/3.1.2/flowplayer-3.1.2.min.js");

		$embed_script = $this->script()->type("text/javascript")->content("flowplayer('{$id}', {
 		  	width: {$width},
  		  	height: {$height},
   		  	src: \"/flowplayer/3.1.2/flowplayer-3.1.2.swf\",
   		  	wmode: 'transparent'
  		},
  		{
   			clip: {
     			baseUrl: '/'
     		},
      		playlist: [
       			{ url: '/{$audio_URL}', autoPlay: 'false', autoBuffering: 'true' }
      		],
		plugins: {
			controls: {
				playlist: true,
				url: '/flowplayer/3.1.2/flowplayer.controls-3.1.2.swf',
				backgroundColor: '#FFFFFF'
			}
		}
   		})");

		$this->script->add($embed_script);

		$audio_link = $this->a()->id($id)->style("display: block; width: {$width}px; height: {$height}px;");
		$audio_div = $this->div()->id("{$id}_container")->add($audio_link);
		return $audio_div;
	}

	function video ($video_URL, $id, $width, $height, $auto_play="false", $loop="false") {
		$this->import("/flowplayer/js/flashembed.min.js");
		$script = $this->script()->content("flashembed('$id', { src: 'flowplayer/FlowPlayerDark.swf', width: $width, height: $height, wmode: 'transparent' }, {config: { autoPlay: {$auto_play}, loop: {$loop}, autoBuffering: true, controlBarBackgroundColor: '0x9999AA', initialScale: 'scale', videoFile: '$video_URL' }})");
		$this->script->add($script);
		$video_div = $this->div()->id($id);
		return $video_div;
	}

	function videoNew ($video, $id, $width, $height, $auto_play="false", $loop="false", $splash_image="", $ads="", $cuepoints="") {
		global $SETTINGS;

		$this->import("/flowplayer/3.1.2/flowplayer-3.1.2.min.js");

		$base_URL = $SETTINGS['COMPANY_URL'];
		if ($base_URL[strlen($base_URL)-1] != "/")
			$base_URL .= "/";
		$auto_buffering = "true";

		if (!$auto_play)
			$auto_play = "false";
		if (!$loop)
			$loop = "false";
		if ($splash_image != "")
			$auto_play = "false";

		if (!is_array($video))
			$video = array($video);

		if ($cuepoints) {
			$cuetimes = array();
			$cuefuncs = array();
				foreach ($cuepoints AS $cuepoint => $function) {
					$cuetimes[] = $cuepoint;
					$cuefuncs[] = "case {$cuepoint}: {$function}; break;";
				}

			$cuepoints = "onCuepoint: [[" . implode(",", $cuetimes) . "], function (clip, point) {
				switch (point) {
					" . implode("\n", $cuefuncs) . "
				}
			}]";
		}

		//flowplayer-3.1.2.swf
		$video_script = "\n\nflowplayer('{$id}', { 
			width: {$width}, 
			height: {$height}, 
			src: \"/flowplayer/3.1.2/flowplayer-3.1.2.swf\", 
			wmode: 'transparent' 
		}, 
		{ 
		clip: 
			{ 
				baseUrl: '{$base_URL}',
				{$cuepoints}
			}, 
		playlist: [";
		if ($splash_image != "") {
			$video_script .= "\n{ url: '{$base_URL}{$splash_image}', autoPlay: true }, ";
			/* The splash image will prevent the autoplay. */
			if (!$auto_play)
				$auto_play = true;
		}
		for ($i=0; $i<count($video); $i++) {
			if ($i == 0)
				$video_auto_play = $auto_play;
			else $video_auto_play = "true";
			
			$instream_playlist = "";
			$cuepoints = "";
			if (is_array($ads[$i])) {
				$j = 0;
				$instream_playlist = ", playlist: [";
				$ad_tracker = "function (clip, cuepoint) {\n\tvar adID = \"\";\n\tswitch (cuepoint) {";
				foreach ($ads[$i] AS $ad) {
					if ($j != 0)
						$instream_playlist .= ",";
					$instream_playlist .= "\n\t\t{ url: '{$ad['URL']}', controls: { scrubber: false }, autoBuffering: true, position: {$ad['position']} }";
					$cuepoint = $ad['position'] * 1000;
					$cuepoints[] = $cuepoint;
					$ad_tracker .= "\n\t\tcase {$cuepoint}: adID = {$ad['ID']}; break;";
					$j++;
				}
				$ad_tracker .= "\n\t}\n\tif (adID != \"\")\n\t\tviewAd(adID);\n}";
				$cuepoints = implode(", ", $cuepoints);
				$cuepoints = ", onCuepoint: [[{$cuepoints}], {$ad_tracker}]";
				$instream_playlist .= "]";
			}
			$video_script .= "\n\t{ url: '{$base_URL}{$video[$i]}', autoPlay: {$video_auto_play}, autoBuffering: {$auto_buffering}{$cuepoints}{$instream_playlist} }";
			if ($i != count($video)-1)
				$video_script .=",\n";
		}
		$video_script .= "]})";
		$script = $this->script()->content($video_script);

		$this->script->add($script);
		$video_link = $this->a()->id($id)/*->href($base_URL . $video)*/->style("display: block; width: {$width}px; height: {$height}px;");
		$video_div = $this->div()->id("{$id}_container")->add($video_link);

		return $video_div;
	}

	function use_stylesheet ($stylesheet) {
		$link = $this->link()->rel("stylesheet")->type("text/css")->href($stylesheet);
		$this->head->add($link);
	}

	function html () {
		global $ga_tracking_js;
	
		$html = $this->doctype;
		$html .= "\n<html lang=\"en-US\" xml:lang=\"en-US\" xmlns=\"http://www.w3.org/1999/xhtml\">";
		$html .= "\n" . $this->head->html();
		$html .= "\n" . $this->body->html();

		$html .= $this->script->html();
/*
		foreach ($this->script->elements as $includes)
			$html .= $includes->html();
*/

		$html .= $ga_tracking_js;
		$html .= "</html>";
		return $html;
	}

}

?>
