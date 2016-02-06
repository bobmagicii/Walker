<?php

namespace Walker;
use \Nether;
use \Walker;

use \Exception;
use \StdClass;

////////////////
////////////////

Nether\Option::Define([
	'ConfigDir' => sprintf(
		// dirname()/conf
		'%1$s%2$sconf',
		dirname(__FILE__,3),
		DIRECTORY_SEPARATOR
	),
	'SaveDir'   => sprintf(
		// dirname()/save/%CONFIGNAME%
		'%1$s%2$ssave%2$s%%CONFIGNAME%%',
		dirname(__FILE__,3),
		DIRECTORY_SEPARATOR
	),
	'Delay'     => 3,
	'UserAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.88 Safari/537.36 Vivaldi/1.0.385.5'
]);

////////////////
////////////////

class Config
extends Nether\Object {
/*//
this class defines the unique configurations that can be created to process
the various jobs you may want to perform. this file also happens to define
the default settings for the entire app (currently).
//*/

	protected
	$File = '';
	/*//
	@generated
	the filename that this config file is written or will be written to in the
	future when saved to disk as json. this property is automatically populated
	by the GetFile() method when needed based on current configuration.
	//*/

	public function
	GetFile():
	String {
	/*//
	@get File
	//*/

		if(!$this->File)
		$this->File = sprintf(
			'%s%s%s.json',
			Nether\Option::Get('ConfigDir'),
			DIRECTORY_SEPARATOR,
			$this->Name
		);

		return $this->File;
	}

	////////////////
	////////////////

	protected
	$Name = '';
	/*//
	the name of this configuration. it is used to generate the filename that
	should be read and written to mostly, as well as just define an alias for
	the specific task we want to perform.
	//*/

	public function
	GetName():
	String {
	/*//
	@get $Name
	//*/

		return $this->Name;
	}

	public function
	SetName(String $Name):
	Self {
	/*//
	@set $Name
	also regenerates the config file name when changed.
	//*/

		$this->Name = $Name;

		$this->GetFile();
		return $this;
	}

	////////////////
	////////////////

	public
	$Delay;
	/*//
	@type Int
	defines how long we should wait between each step to not punch websites
	in the face or piss off their network admins.
	//*/

	public
	$LastIter;
	/*//
	@type Int
	defines the last iter used while processing. each time it downloads
	a file it increments this up one.
	//*/

	public
	$LastURL;
	/*//
	@type String
	defines the URL the process left off on. when it reaches the end of
	pages to scan it will write the url here so that you can use the same
	job again to pick up where it left off in the event more things are
	added to the series you were ninjaing later.
	//*/

	public
	$QueryDownload;
	/*//
	@type String
	defines the pattern that will result in what elements in the html to find
	that contain what we want to download. you should more or less be writing
	the same type of string you would as a css selector or jquery query.
	//*/

	public
	$QueryDownloadAttr;
	/*//
	@type String
	defines which attribute of the found element contains the data that
	describes the url we want to download. if your QueryDownload targeted
	an img tag then you would put 'src' here for example.
	//*/

	public
	$QueryNext;
	/*//
	@type string
	defines the pattern that will result in what elements in the html contain
	the url to the next page to scan.
	//*/

	public
	$QueryNextAttr;
	/*//
	@type string
	defines which attribute of the found element contains the data that
	describes the url for the next page. if your QueryNext targeted an a tag
	then you would put 'href' here for example.
	//*/

	public
	$SaveDir;
	/*//
	@type String
	defines the directory files will be saved into while running. this string
	accepts a few variables to make setting easier.
	//*/

	public
	$SaveFile;
	/*//
	@type String
	defines the filename for saving files. this string accepts a few varibles
	so that they can be saved with sequental numbering or whatever. if left
	empty then the original filename from the web will be used.
	//*/

	public
	$StartURL;
	/*//
	@type String
	defines the URL to begin walking at. it will hit this url, download what
	it should, then look for the next page button and repeat until it runs
	out of next page buttons.
	//*/

	public
	$TransformDownload;
	/*//
	@type String or Array
	define what classes should be used to transform a url that we want to
	download. you can use this to ensure that you are getting the largest
	size available or whatever. if it is a string it will be convereted
	into an array. Transforms must implement Proto\TransformURL. you can
	have multiple with the ability for each one to turn down handling the
	URL if the pattern doesn't match what they want.
	//*/

	public
	$TransformNext;
	/*//
	@type String or Array
	define what classes should be used to transform a url for the next page.
	same principle as TransformDownload. probably less useful but why not be
	feature complete.
	//*/

	public
	$UserAgent;
	/*//
	@type String
	these hips don't lie but your process will need to in order to not get
	banned by really anal devops. the app default is the vivaldi ua because
	why not.
	//*/

	public
	$Verbose;
	/*//
	@type Bool
	controls how much stdout the thing dumps while working.
	//*/

	////////////////
	////////////////

	public function
	__Construct(String $ConfigName=null) {
	/*//
	given a configuration name will attempt to load it from disk. else it will
	generate a fresh config object with default settings.
	//*/

		$Dataset = null;
		$ForceDefaultValues = true;

		if($ConfigName) {
			try { $Dataset = $this->Read($ConfigName); }
			catch(Exception $Error) {
				throw $Error;
			}
		}

		if($Dataset)
		$ForceDefaultValues = false;

		parent::__Construct($Dataset,[
			'Delay'             => Nether\Option::Get('Delay'),
			'LastIter'          => 1,
			'LastURL'           => '',
			'QueryDownload'     => '',
			'QueryDownloadAttr' => '',
			'QueryNext'         => '',
			'QueryNextAttr'     => '',
			'SaveDir'           => Nether\Option::Get('SaveDir'),
			'SaveFile'          => '',
			'StartURL'          => '',
			'PadFileNums'       => 0,
			'TransformDownload' => [],
			'TransformNext'     => [],
			'UserAgent'         => Nether\Option::Get('UserAgent'),
			'Verbose'           => true
		],['ForceDefaultValues'=>$ForceDefaultValues]);

		if(is_string($this->TransformDownload))
		$this->TransformDownload = [$this->TransformDownload];

		if(is_string($this->TransformNext))
		$this->TransformNext = [$this->TransformNext];

		return;
	}



	////////////////
	////////////////

	public function
	Read(String $Name):
	StdClass {
	/*//
	performs the the reading of the config file from disk with any checks
	deemed nessessary to safely do so.
	//*/

		$this->SetName($Name);

		////////

		if(!file_exists($this->File))
		throw new Walker\Error\FileNotFound($this->File);

		if(!is_readable($this->File))
		throw new Walker\Error\FileNotReadable($this->File);

		////////

		$Dataset = json_decode(file_get_contents($this->File));

		if(!is_object($Dataset))
		throw new Exception("{$this->File} had parsing errors.");

		////////

		return $Dataset;
	}

	public function
	Write():
	Self {
	/*//
	performs the writing of the config file to disk with any checks deemed
	nessessary to safely do so.
	//*/

		if(!$this->Name || !$this->File)
		throw new Exception('this Config object needs to know its Name before it can write to disk.');

		////////

		if(file_exists($this->File)) {
			if(!is_writable($this->File))
			throw new Walker\Error\FileNotWritable($this->File);
		}

		else {
			if(!is_dir(dirname($this->File)))
			if(!@mkdir(dirname($this->File),0777,true))
			throw new Walker\Error\FileNotWritable($this->File);

			if(!is_writable(dirname($this->File)))
			throw new Walker\Error\FileNotWritable($this->File);
		}

		////////

		file_put_contents(
			$this->File,
			json_encode($this,JSON_PRETTY_PRINT)
		);

		return $this;
	}

}
