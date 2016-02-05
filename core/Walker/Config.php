<?php

namespace Walker;
use \Nether;

use \Exception;

////////////////
////////////////

Nether\Option::Define([
	'ConfigDir' => sprintf('%s/conf',dirname(__FILE__,3)),
	'Delay'     => 3,
	'SaveDir'   => sprintf('%s/save/{{ConfigName}}',dirname(__FILE__,3)),
	'UserAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.88 Safari/537.36 Vivaldi/1.0.385.5'
]);

////////////////
////////////////

class Config
extends Nether\Object {

	protected
	$ConfigFile = '';

	public function
	GetConfigFile():
	String {
		return $this->ConfigFile;
	}

	////////////////
	////////////////

	public function
	__Construct(String $ConfigName=null) {

		$Dataset = null;

		if($ConfigName)
		$Dataset = $this->ReadConfigFile($ConfigName);

		parent::__Construct($Dataset,[
			'Delay'         => Nether\Option::Get('Delay'),
			'Filename'      => '',
			'QueryDownload' => '',
			'QueryNext'     => '',
			'SaveDir'       => Nether\Option::Get('SaveDir'),
			'StartURL'      => '',
			'UserAgent'     => Nether\Option::Get('UserAgent')
		]);

		return;
	}

	////////////////
	////////////////

	protected function
	ReadConfigFile(String $Filename):
	StdClass {

		$this->ConfigFile = sprintf(
			'%s%s%s',
			Nether\Option::Get('ConfigDir'),
			DIRECTORY_SEPERATOR,
			$ConfigName
		);

		if(!file_exists($this->ConfigFile))
		throw new Exception("{$Filename} not found",1);

		if(!is_readable($this->ConfigFile))
		throw new Exception("{$Filename} not readable",2);

		$Dataset = json_decode(file_get_contents($this->ConfigFile));

		if(!is_object($Dataset))
		throw new Exception("{$Filename} had parsing errors.",3);

		return $Dataset;
	}

}
