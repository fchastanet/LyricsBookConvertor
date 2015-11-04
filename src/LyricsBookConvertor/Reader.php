<?php
namespace LyricsBookConvertor;
use XMLReader;

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
		$contentEncoding = mb_detect_encoding($content);
		$reader = new XMLReader();
		$ret = $reader->xml($content,$contentEncoding, LIBXML_PARSEHUGE );
		if (!$ret) {
			throw new Exception("xml content unparseable", $content);
		}
		$nodes = array();
		$currentIsAP = false;
		$defaultNode = array(
			'TitreChanson' => '',
			'AuteurGroupe' => '',
			'Paroles' => array(),
			'indexes' => array(),
		);
		$currentNode = $defaultNode;
		$metaFields = array('TitreChanson', 'AuteurGroupe', 'Paroles');
		$currentField = '';
		$firstNode = true;

		$closeNode = function() use(&$currentNode, &$nodes, $defaultNode) {
			if (!empty($currentNode['Paroles'])) {
	  			$nodes[] = $currentNode;
	  			$currentNode = $defaultNode;
	  		}
		};
		

		while($reader->read()) {

		  	if ($reader->nodeType == XMLReader::ELEMENT) {
		  		
		  		if ($reader->localName == 'p') {

		  			$currentIsAP = true;
		  			$currentLeft = 0;
		  			$currentField = 'Paroles'; //par defaut c'est des paroles
		  			continue;
		  		} 
		  		if ($currentIsAP) {
			  		if ($reader->localName == 'pStyle') {
					    $currentField = $reader->getAttribute('w:val');
					}
					elseif ($reader->localName == 't') {
						if (in_array($currentField, $metaFields)) {
							if($currentField == 'TitreChanson') {
								if ($firstNode) {
									$firstNode = false;
								} else {
									$closeNode($currentNode);
								}
							}

							$text = trim( $reader->readString());
							if (!empty($text)) {
								$currentFieldValue = array('text' => $text);
								if (!empty($currentLeft)) {
									$currentFieldValue['left'] = $currentLeft;
								}
								if (is_array($currentNode[$currentField])) {
									$currentNode[$currentField][] = $currentFieldValue;
								} else {
									$currentNode[$currentField] = $currentFieldValue;
								}
							}
						}
					}
					elseif ($reader->localName == 'ind') {
						$currentLeft = intval($reader->getAttribute('w:left'));
					}
					elseif ($reader->localName == 'instrText') {
						$value = trim($reader->readString());
						if (startsWith($value, 'XE')) {
							//XE \f titre "21 Guns"
							//XE \f LANGUE "Anglophone:Green Day:21 Guns"
							//XE \f groupe "Green Day:21 Guns"
							//XE \f harmo "Harmo D"
						    //XE \f capo "2"
						    //XE \f selection "Rock Nawak"
						    //XE \f bpm "172"
							$ret = preg_match('/^XE[ ]+\\\\f[ ]+(?P<indexName>[a-zA-Z0-9]+)[ ]+["](?P<indexValue>[^"]+)["]$/ui', $value, $matches);
							if ($ret) {
								$currentNode['indexes'][] = array(
									'type' => strtolower($matches['indexName']),
									'value' => $matches['indexValue'],
								);
							} 
							
						}
						
					}
					
				}
			}
			elseif ($reader->nodeType == XMLReader::END_ELEMENT) {
				if ($reader->localName == 'p') {
					$currentIsAP = false;
		  		} 

			}
		}
		$closeNode($currentNode);
		$reader->close();
		return $nodes;
	}
}