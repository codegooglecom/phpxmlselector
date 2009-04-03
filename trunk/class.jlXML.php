<?php
/*
	Author: Jennal
	Email: jennalcn@gmail.com
	Create Date: 2008-11-21

	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
define('JLXML_ERROR_MSG', 'Error('.__FILE__.", %s)\n%s: %s");

define('JLXML_SELECT_TAGNAME', 0);
define('JLXML_SELECT_ID', 1);
define('JLXML_SELECT_TAGNAME_ATTR', 2);

function jluDie($msg, $line='', $method=''){
	die(sprintf(JLXML_ERROR_MSG, $line, $method, $msg));
}

function jluPerror($msg){
	$message = $msg;
	if( is_array($msg) ){
		$message = '';
		foreach ($msg as &$value){
			if ( $value->nodeType == XML_ELEMENT_NODE ){
				$message .= $value->tagName . '<br />';
			}elseif (is_string($value)){
				$message .= $value . '<br />';
			}
		}
	}
	echo '<br /><span style="color:red;">' . $message . '</span><br />';
}

class jlXML{
	private $filename='';
	private $xmlstr='';
	private $doc=null; //DOMDocument
	private $root=null; //DOMElement
	
	const JL_TYPE_XML = 4;
	const JL_TYPE_SINGLEXML = 6;
	const JL_TYPE_CDATA = 7;
	const JL_TYPE_TEXT = 8;

	/*@args:
	** filename
	** xmlstr, filename=null
	** DOMDocument, filename=null
	** DOMElement, filename=null
	*/
	public function __construct(){
		$args = func_get_args();
//		var_dump($args[0]);
//		var_dump(is_string($args[0]) && strstr($args[0], '.xml'));
		if( get_class($args[0]) == 'DOMDocument' ){
			$this->init_from_doc($args[0], $args[1]);
		}elseif( get_class($args[0]) == 'DOMElement' ){
			$this->init_from_element($args[0], $args[1]);
		}elseif( is_string($args[0]) && strstr($args[0], '.xml') ){
			//it is a xml file
			$this->init_from_file($args[0]);
		}elseif( is_string($args[0]) && strstr($args[0], '<?xml') ){
			//it is a xml string
			$this->init_from_str($args[0], $args[1]);
		}else{
			jluDie('Unkown argument', __LINE__, __METHOD__);
		}
	}

	private function init_from_doc($doc, $filename=null){
		$this->doc = $doc;
		$this->filename = $filename;
		$this->xmlstr = $this->doc->saveXML();
		$this->init_root();
	}
	
	private function init_from_element($element, $filename=null){
		$this->doc = new DOMDocument('1.0', 'utf-8');
		$this->appendChild($element, $this->doc);
		$this->filename = $filename;
		$this->xmlstr = $this->doc->saveXML();
		$this->init_root();
	}
	
	private function init_from_file($filename){
		$this->doc = new DOMDocument();
		$this->filename = $filename;
		$this->doc->load($filename);
		$this->xmlstr = $this->doc->saveXML();
		$this->init_root();
	}

	private function init_from_str($xmlstr, $filename=null){
		$this->doc = new DOMDocument();
		$this->filename = isset($filename) ? $filename : '';
		$this->doc->loadXML($xmlstr);
		$this->xmlstr = $xmlstr;
		$this->init_root();
	}
	
	/*@return:
	** DOMElement
	*/
	private function init_root(){
/*		$pattern = '/((<\?.*\?>)+<)(\w+)(.*>.*)/';
//		$replacement = '$3';
//		$root = preg_replace($pattern, $replacement, $this->getNoneNlXML());
//		$this->root = $this->doc->getElementsByTagName($root)->item(0);
*/
		$this->root = $this->doc->documentElement;	
	}
	
	/*@return:
	** string
	**@des:
	** return a string with none new line of this->xmlstr
	*/
	public function getNoneNlXML(){
		$pattern = '/[\n\r]/';
		$replacement = '';
//		$this->xmlstr = preg_replace($pattern, $replacement, $this->xmlstr);
		return preg_replace($pattern, $replacement, $this->xmlstr);
	}
	
	public function loadFile($filename){
		$this->init_from_file($filename);
	}
	
	public function loadXML($xmlstr, $filename=null){
		$this->init_from_str($xmlstr, $filename);
	}
	
	public function saveFile($filename=null){
		!empty($filename) || ($filename=$this->filename);
//		echo $filename;
		return $this->doc->save($filename);
	}
	
	public function saveXML(){
		return $this->doc->saveXML();
	}
	
	public function getDoc(){
		return $this->doc;
	}
	
	public function getRoot(){
		return $this->root;
	}
	
	public function importNode($node){
		if( $this->isNode($node) ){
			return $this->doc->importNode($node, true);
		}else{
			jluDie('Unkown argument', __LINE__, __METHOD__);
		}
	}
	
	public function importNodeTo($node, $root){
		if( $this->isDocument($root) ){
			return $root->importNode($node, true);
		}elseif($this->isElement($root)){
			return $root->ownerDocument->importNode($node, true);
		}else{
			jluDie('Unkown argument', __LINE__, __METHOD__);
		}
	}
	
	public function isNode($node){
		return isset($node->nodeType) && get_class($node) != 'DOMDocument';
	}
	
	public function isDocument($doc){
		return get_class($doc) == 'DOMDocument';
	}
	
	public function isElement($e){
		return get_class($e) == 'DOMElement';
	}
	
	/*@return:
	** DOMNodeList or DOMElement
	**@des:
	** if $index is not setted return DOMNodeList else return DOMElement
	*/
	public function getElementsByTagName($tagname, $index='all'){
		$index!='all' && $index=intval($index);
		return $index!='all' ? $this->root->getElementsByTagName($tagname)->item($index) : $this->getElementsByTagName($tagname);
	}
	
	public function getElementById($id){
		return $this->doc->getElementById($id);
	}
	
	/*@return:
	** array or DOMElement
	**@des:
	** depends on $str
	**@argument example:
	** xmlstr = 
	**	<?xml version="1.0"?>
	**	<lover>
	**		<person id="jennal" age="22">
	**			<favorite>love sue</favorite>
	**		</person>
	**		<person id="sue" age="21">
	**			<favorite>love jennal</favorite>
	**		</person>
	**	</lover>
	** "lover person" will get an array of elements whose tagName is person
	**	 "person" could do the same thing
	** "#jennal" will get an array of elements which has only one element, its id="jennal"
	** "person[age=22]" will get an array of elements whose age="22"
	** "#jennal favorite" will get an array of elements which are jennal's favorite
	**	 "person[age=22] favorite" could do the same thing
	*/
	public function select($str){
		//filter all before the last id
		$id_pos = strripos($str, '#');
		$str = substr($str, $id_pos, strlen($str));
		
		//split tokens
		$tokens = array();
		$tokens_pre = preg_split('/\s+/', $str);
		
		//check tokens type and init tokens
		foreach ($tokens_pre as $token){
			if( strstr($token, '#') ){//#id
				list($id) = sscanf($token, '#%s');
				$token = array('type' => JLXML_SELECT_ID, 'id' => $id);
			}elseif (strstr($token, '[') && strstr($token, ']')){//tagname[attr=value]
				list($tagname, $attr, $value) = preg_split('/[\[\]=]/', $token);
				$token = array('type' => JLXML_SELECT_TAGNAME_ATTR, 'tagname' => $tagname, 'attr' => $attr, 'value' => $value);
			}else{//tagname
				$token = array('type' => JLXML_SELECT_TAGNAME, 'tagname' => $token);
			}
			array_push($tokens, $token);
		}
		
//		var_dump($tokens);
		//find the elements
		$token_remain = count($tokens);
		$search_arr = array( $this->doc );
		$goal_arr = array();
		foreach ($tokens as $token){
//			jluPerror($token['tagname'] .':'. $token['type']);
			while( !empty($search_arr) ){
				$next_node = array_shift($search_arr);
				$element = null;
				switch ( $token['type'] ){
					case JLXML_SELECT_ID:
//						jluPerror('Here???ID');
						if( $token_remain == count($tokens) ){//first time in
							$element = $this->getElementById($token['id']);
						}else{//if id isset, id must be the first one
							jluDie('this path cant be pass twice!', __LINE__, __METHOD__);
						}
						break;
					case JLXML_SELECT_TAGNAME:
//						jluPerror('Here???TAGNAME');
						$element = $this->findElementsByTagname($token['tagname'], $next_node);
//						jluPerror('find it:');
//						jluPerror($element);
						break;
					case JLXML_SELECT_TAGNAME_ATTR:
//						jluPerror('Here???TAGNAME_ATTR');
						$element = $this->findElementsByTagnameAttr($token['tagname'], $token['attr'], $token['value'], $next_node);
						break;
					default:break;
				}

				if( isset($element) ){
					if( is_array($element) ){
//						jluPerror('push to arr, $element:');
//						var_dump($element);
//						var_dump($goal_arr);
//						jluPerror($element);
//						$goal_arr = $goal_arr + $element;
						foreach ( $element as $e ){
							array_push($goal_arr, $e);
						}
//						jluPerror('push to arr, $goal_arr:');
//						var_dump($goal_arr);
					}elseif($element->nodeType == XML_ELEMENT_NODE){
//						jluPerror('push to arr:' . $element);
						array_push($goal_arr, $element);
					}
				}
			}
//			echo $token_remain . ":\n";
//			var_dump($token);
//			var_dump($goal_arr);
//			echo "==================================\n";
			$search_arr = $goal_arr;
			$goal_arr = array();
			--$token_remain;
//			jluPerror('token_remain:'.$token_remain);
		}

		$goal_arr = $search_arr;
		//while return check if $goal_arr has only one item
		return $goal_arr;
	}
	
	/*@description:
	** select a single element and return
	*/
	public function selectSingle($str){
		$elements = $this->select($str);
		if( count($elements)>1 ){
			jluDie('Not a single element returned!', __LINE__, __METHOD__);
		}
		return !empty($elements) ? $elements[0] : null;
	}
	
	/*@return:
	** array of DOMElement
	** if find nothing return false
	*/
	public function findElementsByTagname($tagname, $root=null){
		$root == null && $root = $this->root;
		$search_arr = array($root);
		$goal_arr = array();
		while( !empty($search_arr) ){
			$search_node = array_shift($search_arr);
			if( $search_node->hasChildNodes() ){
				$childs = $search_node->childNodes;
				for ( $i=0; $i!=$childs->length; ++$i ){
					$element = $childs->item($i);
					if( $element->nodeType == XML_ELEMENT_NODE ){
						array_push($search_arr, $element);
						if( $element->nodeName == $tagname ){
							array_push($goal_arr, $element);
						}
					}
				}
			}
		}
		
		return !empty($goal_arr) ? $goal_arr : false;
	}
	
	/*@return:
	** array of DOMElement
	** if find nothing return false
	*/
	public function findElementsByTagnameAttr($tagname, $attr, $value, $root=null){
		$root == null && $root = $this->root;
		$search_arr = array($root);
		$goal_arr = array();
		while( !empty($search_arr) ){
			$search_node = array_shift($search_arr);
			if( $search_node->hasChildNodes() ){
				$childs = $search_node->childNodes;
				for ( $i=0; $i!=$childs->length; ++$i ){
					$element = $childs->item($i);
					if( $element->nodeType == XML_ELEMENT_NODE ){
						array_push($search_arr, $element);
						if( $element->tagName == $tagname && $element->getAttribute($attr) == $value ){
							array_push($goal_arr, $element);
						}
					}
				}
			}
		}
		
		return !empty($goal_arr) ? $goal_arr : false;
	}
	
	/*@description:
	** $select is the code to call jlXML::select,
	** $attr is the attribute name which you wanna check
	**@return
	** if has the attr return true, else false
	*/
	public function hasAttr($select, $attr){
		$value = $this->getAttr($select, $attr);
		return $value != '';
	}
	
	/*@description:
	** $select is the code to call jlXML::select
	** $text is the text you want to set to the element
	*/
	public function setText($select, $text, $cdata=false){
		$elements = $this->select($select);
		foreach ($elements as $e){
			$textNode = $cdata == false ? new DOMText($text) : $this->doc->createCDATASection($text);
			$this->removeAllChilds($e)->appendChild($textNode);
		}
	}
	
	/*@description:
	** $select is the code to call jlXML::select
	** $attr_arr can be an array of a list of attribute name and value pairs
	** array: array('name' => 'Jennal', 'author' => 'yes' ...)
	** pairs: 'name', 'Jennal', 'author', 'yes' ...
	*/
	public function setAttr($select, $attr_arr){
		$elements = $this->select($select);
		if( !is_array($attr_arr) ){
			if( func_num_args()<3 ){
				jluDie('Wrong argument amount!', __LINE__, __METHOD__);
			}
			$total = func_num_args();
			$attr_arr = array();
			for ( $i=1; $i<$total; $i+=2 ){
				$attr_arr[func_get_arg($i)] = func_get_arg($i+1);
			}
		}
		foreach ($elements as $e){
			foreach ( $attr_arr as $attr => $value ){
				$e->setAttribute($attr, $value);
			}
		}
	}
	
	/*@description:
	** $select is the code to call jlXML::select, 
	** if result is not a single element, die
	**@return
	** return the value of all child textNode
	*/
	public function getText($select){
		$element = $this->selectSingle($select);
		if( $element && $element->hasChildNodes() ){
			$childs = $element->childNodes;
			$text = '';
			for( $i=0; $i!=$childs->length; ++$i ){
				$e = $childs->item($i);
				if( $e->nodeType == XML_TEXT_NODE || $e->nodeType == XML_CDATA_SECTION_NODE ){
					$text .= $e->wholeText;
				}
			}
			return $text;
		}else{
			return '';
		}
	}
	
	/*@description:
	** get the Text content from a element
	**@return
	** return the value of all child textNode in the Element
	*/
	public function getElementText($element){
		if( $this->isElement($element) == false ){
			jluDie('Parameter is not a Element!', __LINE__, __METHOD__);
		}
		if( $element && $element->hasChildNodes() ){
			$childs = $element->childNodes;
			$text = '';
			for( $i=0; $i!=$childs->length; ++$i ){
				$e = $childs->item($i);
				if( $e->nodeType == XML_TEXT_NODE || $e->nodeType == XML_CDATA_SECTION_NODE ){
					$text .= $e->wholeText;
				}
			}
			return $text;
		}else{
			return '';
		}
	}
	
	/*@description:
	** $select is the code to call jlXML::select,
	** if result is not a single element, die
	**@return
	** return the attr value
	*/
	public function getAttr($select, $attr){
		$element = $this->selectSingle($select);
		return $element ? $element->getAttribute($attr) : '';
	}
	
	/*@description:
	** $select is the code to call jlXML::select,
	** if value is not a int or string, die
	** add $value to the attribute value
	**@return
	** return new value
	*/
	public function plusAttr($select, $attr, $value){
		$element = $this->selectSingle($select);
		$attr_val = $element->getAttribute($attr);
		if( is_int($value) ){
			$attr_val = intval($attr_val) + intval($value);
		}elseif( is_string($value) ){
			$attr_val .= strval($value);
		}else{
			jluDie('$value must be a int or string!', __LINE__, __METHOD__);
		}
		
		$element->setAttribute($attr, $attr_val);
		return $attr_val;
	}
	
	/*@description:
	** $select is the code to call jlXML::select,
	** if value is not a int or string, die
	** add $value to the text value
	**@return
	** return new text
	*/
	public function plusText($select, $value){
		$text = $this->getText($select);
		if( is_int($value) ){
			$text = intval($text) + intval($value);
		}elseif( is_string($value) ){
			$text .= strval($value);
		}else{
			jluDie('$value must be a int or string!', __LINE__, __METHOD__);
		}
		
		$this->setText($select, $text);
		return $text;
	}
	
	/*@arguments:
	** $select could be a string to run $this->selectSingle
	** or $select could be a DOMElement 
	**@description:
	** $select is the code to call jlXML::select,
	** remove all childs
	**@return
	** return the element which just remove its whole childs
	*/
	public function removeAllChilds($select){
		$element = null;
		if( is_string($select) ){
			$element = $this->selectSingle($select);
		}elseif( get_class($select) == 'DOMElement' ){
			$element = $select;
		}else{
			jluDie('Wrong argument type! Only string and DOMElement is accepted!', __LINE__, __METHOD__);
		}
		while( $element->hasChildNodes() ){
			$delete = $element->firstChild;
			$element->removeChild($delete);
		}
		return $element;
	}
	
	/*@description:
	** append the $node to $root
	** if $root is not setted, append to the main root
	*/
	public function appendChild(&$node, $root=null){
		$root == null && $root = $this->root;
		if( $this->isNode($node) ){
			$node = $this->importNodeTo($node, $root);
			$root->appendChild($node);
		}else{
			jluDie('parameter must be a Node!', __LINE__, __METHOD__);
		}
	}
	
	/*@arguments:
	** $nodelist could be an array of DOMNode or a DOMNodeList
	** $root is the root you wanna append to
	**@description:
	** append the $nodelist to $root
	** if $root is not setted, append to the main root
	*/
	public function appenChilds($nodelist, $root=null){
		if( is_array($nodelist) && $this->isNode($nodelist[0]) ){
			foreach ( $nodelist as $node ){
				$node = $this->importNodeTo($node, $root);
				$this->appendChild($node, $root);
			}
		}elseif ( get_class($nodelist) == 'DOMNodeList' ){
			for ( $i=0; $i!=$nodelist->length; ++$i ){
				$node = $this->importNodeTo($nodelist->item($i), $root);
				$this->appendChild($node, $root);
			}
		}else{
			jluDie('parameter must be an Array or a DOMNodeList!', __LINE__, __METHOD__);
		}
	}
	
	/*@description:
	** insert $node before $before
	*/
	public function insertBefore(&$node, $before){
		if( $this->isNode($before) && $this->isNode($node) ){
			$node = $this->importNodeTo($node, $before);
			$before->parentNode->insertBefore($node, $before);
		}else{
			jluDie('parameter must be a instance of DOMNode or its child class!', __LINE__, __METHOD__);
		}
	}
	
	/*@description:
	** insert $node after $after
	*/
	public function insertAfter(&$node, $after){
		if( $this->isNode($after) && $this->isNode($node) ){
			$node = $this->importNodeTo($node, $after);
			if( $after->nextSibling != null ){
				$after->parentNode->insertBefore($node, $after->nextSibling);
			}else{
				$after->parentNode->appendChild($node);
			}
		}else{
			jluDie('parameter must be a instance of DOMNode or its child class!', __LINE__, __METHOD__);
		}
	}
	
	/*@description:
	** swap $nodea and $nodeb
	*/
	public function swapNode($nodea, $nodeb){
		$tmpNode = $this->createTmpNode();
		$this->replaceNode($tmpNode, $nodea);
		$nodeb = $this->replaceNode($nodea, $nodeb);
		$this->replaceNode($nodeb, $tmpNode);
	}
	
	/*@description:
	** replace $old with $new
	** then return $old
	*/
	public function replaceNode($new, $old){
		$new = $this->importNodeTo($new, $old);
		return $old->parentNode->replaceChild($new, $old);
	}
	
	/*@description:
	** replace $old with $new
	** then return $old
	*/
	public function removeNode($node){
		return $node->parentNode->removeChild($node);
	}
	
	/*@description:
	** create a temp DOMNode
	** you could create this to mark some place
	*/
	public function createTmpNode($doc=null){
		return $this->createElement('<tmp />', $doc);
	}
	
	/*@arguments:
	** $xml is the code of xml document
	** $doc_root should be the DOMDocument which you wanna append the created element
	**@description:
	** create the elements by the code you input
	**@return
	** return the first element of the array of elements
	*/
	public function createElement($xml, $doc_root=null){
		$arr = $this->createElementArray($xml, $doc_root);
		return !empty($arr) ? $arr[0] : null;
	}
	
	/*@arguments:
	** $xml is the code of xml document
	** $doc_root should be the DOMDocument which you wanna append the created element
	**@description:
	** create the elements by the code you input
	**@return
	** return the array of elements
	*/
	public function createElementArray($xml, $doc_root=null){
//		$doc_root == null && $doc_root = $this->root; //version 1 && 2
		$doc_root == null && $doc_root = $this->doc;
		
		$doc = new DOMDocument($doc_root->version);
		try{
			$doc->loadXML('<root>' . $xml . '</root>');
//			echo $doc->saveXML();
			$nodelist = $doc->documentElement->childNodes;
			$element_arr = array();
			for ($i=0; $i!=$nodelist->length; ++$i){
				$element = $doc_root->importNode($nodelist->item($i), true);
				array_push($element_arr, $element);
			}
			return $element_arr;
		}catch (Exception $err){
			throw $err;
		}
		
/*version 1
		$element = $root = null;
		$xml = trim($xml);
		$xml = preg_replace('/[\r\n]/', '', $xml);
		
		//Match <xml></xml>|<xml />|<![CDATA[data]]>|Text
		$pattern_xml = "/\s*<([\w]+)([^>]*)>([^<]*)<\/\\1>\s*|\s*<([\w]+)([^>]*)\/>\s*|\s*<!\[CDATA\[(.*)\]\]>\s*|\s*([^<>]+)\s* /";
		preg_match_all($pattern_xml, $xml, $matches, PREG_SET_ORDER);
		foreach ( $matches as $match_item ){
			$type = count($match_item);
			switch ($type){
				case jlXML::JL_TYPE_XML :
					//matches[0][1] == tagName && matches[0][2] == attrs && matches[0][3] == innerHTML
					$root = new DOMElement($match_item[1]);
					$this->appendChild($root, $doc_root);
					$this->createAttributeNodeArray($match_item[2], $root);
					if(!empty($match_item[3])){
						$this->createElement($match_item[3], $root);
					}
					break;
				case jlXML::JL_TYPE_SINGLEXML :
					//matches[0][4] == tagName && matches[0][5] == attrs
					$root = new DOMElement($match_item[4]);
					$this->appendChild($root, $doc_root);
					$this->createAttributeNodeArray($match_item[5], $root);
					break;
				case jlXML::JL_TYPE_CDATA :
					//matches[0][6] == text
					$root = new DOMCdataSection($match_item[6]);
					$this->appendChild($root, $doc_root);
					break;
				case jlXML::JL_TYPE_TEXT  :
					//matches[0][7] == text
					$root = new DOMText($match_item[7]);
					$this->appendChild($root, $doc_root);
					break;
				default:break;
			}
		}
		
		return $doc_root;
*/
		
		/*version 2
//		test XML
//		$pattern_xml = "/\s*<([\w]+)([^>]*)>([^<]*)<\/\\1>\s* /";
//		test XML
//		$pattern_xml_single = "/\s*<([\w]+)([^>]*)\/>\s* /";
//		test CDATA
//		$pattern_cdata = "/\s*<!\[CDATA\[(.*)\]\]>\s* /";
//		test Text
//		$pattern_text = "/\s*([^<>]+)\s* /";
//$pattern_xml = "/\s*<([\w]+)([^>]*)>([^<]*)<\/\\1>\s*|\s*<([\w]+)([^>]*)\/>\s*|\s*<!\[CDATA\[(.*)\]\]>\s*|\s*([^<>]+)\s* /";
		
		if( preg_match_all($pattern_xml, $xml, $matches, PREG_SET_ORDER) ){
			foreach ( $matches as $match_item ){
				//matches[0][1] == tagName && matches[0][2] == attrs && matches[0][3] == innerHTML
				$root = new DOMElement($match_item[1]);
				$this->appendChild($root, $doc_root);
				$this->createAttributeNodeArray($match_item[2], $root);
				if(!empty($match_item[3])){
					$this->createElement($match_item[3], $root);
				}
				
//				if( !empty($match_item[3]) && ($element = $this->createElement($match_item[3], $doc_root)) != null ){
//					$this->appendChild($element, $root);
//				}else{
//					preg_match_all($pattern_xml, $match_item[3], $matches_in, PREG_SET_ORDER);
//					foreach ( $matches_in as $m ){
//						$root->appendChild($this->createElement($m[0], $doc_root));
//					}
//				}
			}
		}elseif( preg_match_all($pattern_xml_single, $xml, $matches, PREG_SET_ORDER) ){
			var_dump($matches);
			foreach ( $matches as $match_item ){
				//matches[0][1] == tagName && matches[0][2] == attrs
				$root = new DOMElement($match_item[1]);
				$this->appendChild($root, $doc_root);
				$this->createAttributeNodeArray($match_item[2], $root);
			}
		}elseif( preg_match_all($pattern_cdata, $xml, $matches, PREG_SET_ORDER) ){
//			if( count($matches)!=1 ){
//				jluDie('you can create only one Element!', __LINE__, __METHOD__);
//			}
			foreach ( $matches as $match_item ){
				//matches[0][0] == text
				$root = new DOMCdataSection($match_item[1]);
				$this->appendChild($root, $doc_root);
			}
		}elseif( preg_match_all($pattern_text, $xml, $matches, PREG_SET_ORDER) ){
//			if( count($matches)!=1 ){
//				jluDie('you can create only one Element!', __LINE__, __METHOD__);
//			}
			//matches[0][0] == text
			foreach ( $matches as $match_item ){
				$root = new DOMText($match_item[0]);
				$this->appendChild($root, $doc_root);
			}
		}
		
		return $doc_root;
		*/
	}
	
	public function createAttributeNodeArray($str, $root=null){
		$arr = array();
		$str = trim($str);
		$pattern_attr = "/\s*([^<>=]+)=([\'\"])([^<>=]+)\\2\s*/";
		preg_match_all($pattern_attr, $str, $matches, PREG_SET_ORDER);
		//matches[i][1] == name && matches[i][3] == value
		foreach ( $matches as $node ){
			$attr = new DOMAttr($node[1], $node[3]);
			$root!=null && get_class($root) == 'DOMElement' && $root->setAttribute($node[1], $node[3]);
			array_push($arr, $attr);
		}
		
		return $arr;
	}
	
}
?>
