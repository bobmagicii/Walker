<?php

namespace Walker;
use \Nether;

use \Exception;

class Walker {

	protected
	$Config = null;
	/*//
	@type Walker\Config
	stores the reference to the config object if we loaded a config from
	disk so that we can interact with it later.
	//*/

	////////////////
	////////////////

	protected
	$QueryDownload = '';
	/*//
	@type String
	stores the pattern that will be used to find othe url of the thing we want
	to ninja from the source of the current page.
	//*/

	public function
	GetQueryDownload():
	String {
		return $this->QueryDownload;
	}

	public function
	SetQueryDownload(String $Query):
	Self {
		$this->QueryDownload = $Query;
		return $this;
	}

	////////////////
	////////////////

	protected
	$QueryNext = '';
	/*//
	@type String
	stores the pattern that will be used to find out what url we should go
	to next from the source of the current page.
	//*/

	public function
	GetQueryNext():
	String {
		return $this->QueryNext;
	}

	public function
	SetQueryNext(String $Query):
	Self {
		$this->URL = $Query;
		return $this;
	}

	////////////////
	////////////////

	protected
	$SavePath = '';

	public function
	GetSavePath():
	String {
		return $this->SavePath;
	}

	public function
	SetSavePath(String $Path):
	Self {
		$this->SavePath = $Path;
		return $this;
	}

	////////////////
	////////////////

	protected
	$StartURL = '';
	/*//
	@type String
	stores the URL that we will start walking from.
	//*/

	public function
	GetStartURL():
	String {
		return $this->URL;
	}

	public function
	SetStartURL(String $URL):
	Self {
		$this->URL = $URL;
		return $this;
	}

	////////////////
	////////////////

	public function
	__Construct(String $Config=null) {

		if($Config)
		$this->LoadConfig($Config);

		return;
	}

	////////////////
	////////////////

	public function
	LoadConfig(String $ConfigName):
	Self {

		$this->Config = new Config($ConfigName);
		return $this;
	}

	////////////////
	////////////////

	public function
	Run():
	Self {

		if(!$this->SavePath)
		throw new Exception('no save path is defined');

		return $this;
	}

}
