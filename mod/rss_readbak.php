<?php
/**
*  Copyright (c) 2003-07  PHPWind.net. All rights reserved.
*  Write by xuanyan@2006-9-23	-	xuanyan1983@gmail.com
*/
!function_exists('readover') && exit('Forbidden');

class RssRead{
	var $items=array();
	var $items_data=array();
	var $charset;
	var $num=1;
	function RssRead($filename,$type='RSS'){
		$xml_parser = substr(PHP_VERSION,0,1)>4 ? @xml_parser_create('UTF-8') : @xml_parser_create();
		@xml_parse_into_struct($xml_parser,$this->read_file($filename),$this->items_data);
		xml_parser_free($xml_parser);
		if ($type=='RSS') {
			$this->get_items($this->items_data);
		} elseif ($type=='OBLOG') {
			$this->get_oblog($this->items_data);
		} elseif ($type=='BLOGBUS') {
			$this->get_blogbus($this->items_data);
		}
	}
	function read_file($filename){
		$first = '';
		set_time_limit(60);
		$ary_lines = @file($filename);
		$i = 0;
		while (empty($first)) {
			$first = $ary_lines[$i++];
		}
		strpos(strtoupper($first),"UTF")!== false && $this->charset='UTF';
		$contents = implode($ary_lines);
		return $contents;
	}
	function get_items($data){
		$imgmark  = false;
		$headmark = false;
		$itemmark = false;
		foreach ($data as $xml_elem) {
			if ($xml_elem['tag']=='CHANNEL') {
				if ($xml_elem['type']=='open') {
					$headmark = true;
				} elseif ($xml_elem['type']=='close') {
					$headmark = false;
				}
				continue;
			} elseif ($xml_elem['tag']=='ITEM') {
				$headmark == true && $headmark = false;
				if ($xml_elem['type']=='open') {
					$itemmark = true;
				} elseif ($xml_elem['type']=='close') {
					$headmark == false && $headmark = true;
					$itemmark = false;
					$this->num++;
				}
				continue;
			} elseif ($xml_elem['tag']=='IMAGE') {
				if ($xml_elem['type']=='open') {
					$headmark == true && $headmark = false;
					$imgmark = true;
				} elseif ($xml_elem['type']=='close') {
					$headmark == false && $headmark = true;
					$imgmark = false;
				}
				continue;
			} else {
				$itemmark && $this->items['ITEM'][$this->num][$xml_elem['tag']] = $xml_elem['value'];
				$headmark && empty($this->items[$xml_elem['tag']]) && $this->items[$xml_elem['tag']] = $xml_elem['value'];
				$imgmark && $this->items['IMAGE'][$xml_elem['tag']] = $xml_elem['value'];
			}
		}
	}
	function get_oblog($data){
		$itemmark = false;
		foreach ($data as $xml_elem) {
			if ($xml_elem['tag']=='LOG') {
				if ($xml_elem['type']=='open') {
					$itemmark = true;
				} elseif ($xml_elem['type']=='close') {
					$itemmark = false;
					$this->num++;
				} elseif ($xml_elem['type']=='cdata') {
					$this->items['ITEM'][$this->num]['DESCRIPTION'] = $xml_elem['value'];
				}
				continue;
			} else {
				$itemmark && $this->items['ITEM'][$this->num][$xml_elem['tag']] = $xml_elem['value'];
			}
		}
	}
	function get_blogbus($data){
		$itemmark = false;
		foreach ($data as $xml_elem) {
			if ($xml_elem['tag']=='LOG') {
				if ($xml_elem['type']=='open') {
					$itemmark = true;
				} elseif ($xml_elem['type']=='close') {
					$itemmark = false;
					$this->num++;
				}
				continue;
			} else {
				if ($itemmark) {
					switch ($xml_elem['tag']) {
						case 'LOGDATE':
							$this->items['ITEM'][$this->num]['PUBDATE'] = $xml_elem['value'];
							break;
						case 'CONTENT':
							$this->items['ITEM'][$this->num]['DESCRIPTION'] = $xml_elem['value'];
							break;
						default:
							$this->items['ITEM'][$this->num][$xml_elem['tag']] = $xml_elem['value'];
					}
				}
			}
		}
	}

};
?>