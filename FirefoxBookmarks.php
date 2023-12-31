<?php

namespace evan_klein\firefox_bookmarks;

require_once('ek.php');
use evan_klein\ek as ek;

class FirefoxBookmarks {
	private $_bookmarks = [
		'menu'=>[],
		'toolbar'=>[]
	];
	private $_types = [
		1=>'text/x-moz-place',
		2=>'text/x-moz-place-container',
		3=>'text/x-moz-place-separator'
	];


	public function __construct(){
		return $this;
	}


	private function _date(){
		return time()*1000*1000;
	}


	private function _elem(
		$type_code,
		$index=-1,
		$title='',
		$uri=null,
		$keyword=null,
		$root=null,
		$guid=null,
		$children=[]
	){
		$elem = [
			'guid'=>$guid ?? \ek\randStr(12),
			'index'=>$index,
			'dateAdded'=>$this->_date(),
			'lastModified'=>$this->_date(),
			'title'=>$title,
			'typeCode'=>$type_code,
			'type'=>$this->_types[$type_code]
		];

		// Conditional variables
		if( isset($uri) ) $elem['uri'] = $uri;
		if( isset($keyword) ) $elem['keyword'] = $keyword;
		if( isset($root) ) $elem['root'] = $root;
		if(
			isset($children)
			&&
			is_array($children)
			&&
			count($children)>0
		) $elem['children'] = $children;

		return $elem;
	}


	private function _bookmark($title, $uri, $index=-1, $keyword=null){
		return $this->_elem(
			type_code: 1,
			index: $index,
			title: $title,
			uri: $uri,
			keyword: $keyword
		);
	}


	public function bookmark($title, $uri, $keyword=null){
		return $this->_bookmark(
			title: $title,
			uri: $uri,
			keyword: $keyword
		);
	}


	private function _folder(
		$title='',
		$index=0,
		$root=null,
		$guid=null,
		$children=[]
	){
		$i = 0;
		foreach($children as $key=>$child){
			$children[$key]['index'] = $i;
			$i++;
		}

		return $this->_elem(
			type_code: 2,
			index: $index,
			title: $title,
			root: $root,
			guid: $guid,
			children: $children
		);
	}


	public function folder(
		$title,
		$children
	){
		return $this->_folder(
			title: $title,
			children: $children
		);
	}


	private function _spacer($index=-1){
		return $this->_elem(
			type_code: 3,
			index: $index
		);
	}


	public function spacer(){
		return $this->_spacer();
	}


	public function addMenuBookmark($title, $uri, $keyword=null){
		$this->_bookmarks['menu'][]=$this->_bookmark(
			title: $title,
			uri: $uri,
			index: count($this->_bookmarks['menu']),
			keyword: $keyword
		);
		return $this;
	}


	public function addMenuFolder($title, $children){
		$this->_bookmarks['menu'][]=$this->_folder(
			title: $title,
			index: count($this->_bookmarks['menu']),
			children: $children
		);
		return $this;
	}


	public function addMenuSpacer(){
		$this->_bookmarks['menu'][]=$this->_spacer(
			index: count($this->_bookmarks['menu'])
		);
		return $this;
	}


	public function addToolbarBookmark($title, $uri, $keyword=null){
		$this->_bookmarks['toolbar'][]=$this->_bookmark(
			title: $title,
			uri: $uri,
			index: count($this->_bookmarks['toolbar']),
			keyword: $keyword
		);
		return $this;
	}


	public function addToolbarFolder($title, $children){
		$this->_bookmarks['toolbar'][]=$this->_folder(
			title: $title,
			index: count($this->_bookmarks['toolbar']),
			children: $children
		);
		return $this;
	}


	public function addToolbarSpacer(){
		$this->_bookmarks['toolbar'][]=$this->_spacer(
			index: count($this->_bookmarks['toolbar'])
		);
		return $this;
	}


	public function output($format='json'){
		$root = $this->_folder(
			root: 'placesRoot',
			guid: 'root________',
			children: [
				$this->_folder(
					title: 'menu',
					index: 0,
					root: 'bookmarksMenuFolder',
					guid: 'menu________',
					children: $this->_bookmarks['menu']
				),
				$this->_folder(
					title: 'toolbar',
					index: 1,
					root: 'toolbarFolder',
					guid: 'toolbar_____',
					children: $this->_bookmarks['toolbar']
				),
				$this->_folder(
					title: 'unfiled',
					index: 2,
					root: 'unfiledBookmarksFolder',
					guid: 'unfiled_____'
				),
				$this->_folder(
					title: 'mobile',
					index: 3,
					root: 'mobileFolder',
					guid: 'mobile______'
				)
			]
		);

		return $format=='object' ? $root:json_encode($root);
	}
}

?>