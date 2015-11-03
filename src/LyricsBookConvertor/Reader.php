<?php
namespace LyricsBookConvertor;

class Reader {

	public function __construct() {

	}

	protected function getContent($filename){
	    $striped_content = '';
	    $content = '';

	    if(!$filename || !file_exists($filename)) throw new Exception("file $filename doesn't exist");

	    $zip = zip_open($filename);
	    if (!$zip || is_numeric($zip)) throw new Exception("file $filename can't be openned as zip file");

	    while ($zip_entry = zip_read($zip)) {

	        if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

	        if (zip_entry_name($zip_entry) != "word/document.xml") continue;

	        $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

	        zip_entry_close($zip_entry);
	    }
	    zip_close($zip);      

	    return $content;
	}

	public function parse($sFileName) {
		$content = $this->getContent($sFileName); 
		echo mb_detect_encoding($content);die;
		XMLReader::xml($content);

		echo $content."\n";
	}
}