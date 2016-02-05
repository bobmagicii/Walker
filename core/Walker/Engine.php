<?php

namespace Walker;
use \Nether;
use \Walker;

use \Exception;

class Engine {

	protected
	$Config = null;
	/*//
	@type Walker\Config
	stores the reference to the config object if we loaded a config from
	disk so that we can interact with it later.
	//*/

	public function
	GetConfig():
	Walker\Config {
	/*//
	@get Config
	//*/

		return $this->Config;
	}

	public function
	SetConfig(Walker\Config $Config):
	Self {
	/*//
	@set Config
	//*/

		$this->Config = $Config;
		return $this;
	}

	////////////////
	////////////////

	public function
	__Construct(String $Config=null) {

		$this->SetConfig(new Config($Config));
		return;
	}

	////////////////
	////////////////

	public function
	Run($Opt=null):
	Self {
	/*//
	begin the process that this library is actually designed to do, first
	of course checking that it can actually do it.
	//*/

		$this->Run_CheckRequirements();
		$this->Run_CheckSaveDir();
		$this->Run_MainLoop();

		return $this;
	}

	protected function
	Run_CheckRequirements():
	Self {
	/*//
	check that we have all the configuration options and features that we need
	to actually perform this task.
	//*/

		if(!ini_get('allow_url_fopen'))
		throw new Exception('allow_url_fopen is not enabled in php.ini');

		if(!$this->Config->SaveDir)
		throw new Exception('no SaveDir is defined');

		if(!$this->Config->LastURL && !$this->Config->StartURL)
		throw new Exception('no StartURL is defined');

		return $this;
	}

	protected function
	Run_CheckSaveDir():
	Self {
	/*//
	check that the save directory is in a usable state and attempt to create
	it if it does not yet exist.
	//*/

		$Dir = $this->ParseStringVariables($this->Config->SaveDir);

		if(file_exists($Dir)) {
			if(!is_dir($Dir))
			throw new Exception("{$Dir} is not a directory");

			if(!is_writable($Dir))
			throw new Exception("{$Dir} is not writable");
		}

		else {
			if(!@mkdir($Dir,0777,true))
			throw new Exception("unable to create {$Dir}");
		}

		$this->Message(">> Save Location: {$Dir}");
		return $this;
	}

	protected function
	Run_MainLoop():
	Self {
	/*//
	like a bullet train.
	//*/

		if(!$this->Config->LastURL) {
			$this->Config->LastURL = $this->Config->StartURL;
			$this->Config->Write();
		}

		$URL = $this->Config->LastURL;

		while($URL) {

			$HTML = file_get_contents($URL);

			if(!$HTML)
			throw new Exception("unable to fetch {$URL}");

			////////

			// find the thing we want to keep.

			// ...

			////////

			// find out where to go next.

			$URL = null;

			////////

			if($URL) {
				$this->Config->LastURL = $URL;
				$this->Config->Write();

				sleep($this->Config->Delay);
			}
		}

		return $this;
	}

	////////////////
	////////////////

	public function
	Message(String $Input):
	Self {
	/*//
	send messages to the console if enabled.
	//*/

		if(!$this->Config->Verbose)
		return;

		Nether\Console\Client::Message($Input);

		return $this;
	}

	public function
	ParseStringVariables(String $Input, Array $Replace=null):
	String {
	/*//
	parse variables in a string with dynamic data. this method includes some
	default variables and you can pass additional ones for later use like
	when we need to pass in iteration counts for file names.
	//*/

		$Table = [
			'CONFIGNAME' => $this->Config->GetName()
		];

		if(is_array($Replace))
		$Table = array_merge($Table,$Replace);

		////////
		////////

		foreach($Table as $Var => $Val)
		$Input = str_replace("%{$Var}%",$Val,$Input);

		////////
		////////

		return $Input;
	}

}
