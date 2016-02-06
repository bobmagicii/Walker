<?php

namespace Walker;
use \Nether;
use \QueryPath;
use \Walker;

use \Exception;
use \FilesystemIterator;
use \StdClass;

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
	Download($URL) {
	/*//
	@todo migrate to a chunked transfer.
	//*/

		$Filename = $this->GetDownloadFilename($URL);

		$this->PrintLine(">> Downloading {$URL}...");
		file_put_contents(
			$Filename,
			file_get_contents($URL)
		);

		$this->PrintLine(sprintf(
			'>> Saved %s (%s)',
			$Filename,
			filesize($Filename)
		));

		return;
	}

	public function
	Reset():
	Self {
	/*//
	resets the configuration so that the job will start over from the start
	instead of where it left off.
	//*/

		$this->Config->LastIter = 1;
		$this->Config->LastURL = '';
		$this->Config->Write();
		return $this;
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

		$Iter = $this->Config->LastIter;
		$URL = $this->Config->LastURL;
		$DownloadURL = null;
		$Document = null;
		$Element = null;

		while($URL) {

			$Document = $this->GetDocumentFromURL($URL);

			////////

			$Element = $this->GetElementFromDocument(
				$Document,
				$this->Config->QueryDownload
			);

			if(!count($Element)) {
				$this->PrintLine(">> No download elements found on {$URL}");
				continue;
			}

			foreach($Element as $Iter => $Item) {
				$DownloadURL = $this->GetAttributeFromElement(
					$Element,
					$this->Config->QueryDownloadAttr
				);

				if(!$DownloadURL) {
					$this->PrintLine(">> Nothing found for element {$Iter}");
					continue;
				}

				$DownloadURL = $this->TransformDownloadURL($DownloadURL);
				if(!$DownloadURL) {
					$this->PrintLine(">> Transforms canceled download for element {$Iter}");
					continue;
				}

				$this->Download($DownloadURL);
			}

			////////

			// find out where to go next.

			$Element = $this->GetElementFromDocument(
				$Document,
				$this->Config->QueryNext
			);

			if(!count($Element)) {
				$this->PrintLine(">> No next elements found on {$URL}");
				$URL = null;
				continue;
			}

			$URL = $this->GetAttributeFromElement(
				$Element,
				$this->Config->QueryNextAttr
			);

			$URL = $this->TransformNextURL($URL);

			if($URL) {
				$this->Config->LastURL = $URL;
				$this->Config->LastIter++;
				$this->Config->Write();

				$this->PrintLine(">> Waiting {$this->Config->Delay}sec...");
				sleep($this->Config->Delay);
			}
		}

		return $this;
	}

	////////////////
	////////////////

	protected function
	TransformDownloadURL(String $URL):
	String {
	/*//
	apply transforms to the download url.
	//*/

		if(is_array($this->Config->TransformDownload))
		return (string)$this->TransformURL(
			$URL,
			$this->Config->TransformDownload
		);

		return $URL;
	}

	protected function
	TransformNextURL(String $URL):
	String {
	/*//
	apply transforms to the next url.
	//*/

		if(is_array($this->Config->TransformNext))
		return (string)$this->TransformURL(
			$URL,
			$this->Config->TransformNext
		);

		return $URL;
	}

	protected function
	TransformURL(String $URL, Array $Classes) {
	/*//
	given a list of classes that know how to transforms urls, run through
	them and let them have at it. if configuring multiple it is best that
	only one really edits it, unless you build your transforms to chain.
	transform classes have the ability to refuse to transform so you can
	stack them and have them test in the event you may be fetching from
	multiple sources somehow.
	//*/

		foreach($Classes as $Class) {
			if(!is_a($Class,'Walker\\Proto\\TransformURL',TRUE)) {
				$this->PrintLine(">> {$Class} is not a valid TransformURL");
				continue;
			}

			if(!$Class::WillHandleTransform($URL)) {
				$this->PrintLine(">> {$Class} refused transformation");
				continue;
			}

			$this->PrintLine(">> Applying URL transform {$Class}");
			$URL = $Class::Transform($URL);
		}

		return (string)$URL;
	}

	////////////////
	////////////////

	protected function
	GetDocumentFromURL(String $URL):
	QueryPath\DOMQuery {
	/*//
	download the page from the specified thing and attempt to parse it as
	a valid html thing.
	//*/

		$this->PrintLine(">> Fetching {$URL}");
		$HTML = file_get_contents($URL);

		if(!$HTML)
		throw new Exception("unable to fetch {$URL}");

		////////

		$Document = @htmlqp($HTML);

		if(!$Document)
		throw new Exception("unable to parse {$URL}");

		////////

		return $Document;
	}

	protected function
	GetElementFromDocument(QueryPath\DOMQuery $Document, String $Query):
	QueryPath\DOMQuery {
	/*//
	attempt to find the element(s) described by the specified query.
	//*/

		$this->PrintLine(">> Searching HTML for {$Query}");

		// god damn is this library (QueryPath) noisy as fuck.
		// we already accepted the HTML may be poorly formed. its the
		// internet after all...
		return @$Document->Find($Query);
	}

	protected function
	GetAttributeFromElement(QueryPath\DOMQuery $Element, String $Query):
	String {
	/*//
	attempt to extract data from the specified attribute.
	//*/

		switch($Query) {
			case 'text': {
				$DownloadURL = $Element->Text();
				break;
			}
			default: {
				$DownloadURL = $Element->Attr($Query);
				break;
			}
		}

		return $DownloadURL;
	}

	////////////////
	////////////////

	protected function
	GetFileCount(String $Dir):
	Int {
	/*//
	lol lol lol lol lol @ this.
	//*/

		return iterator_count(new FilesystemIterator(
			$this->ParseStringVariables($Dir),
			FilesystemIterator::SKIP_DOTS
		));
	}

	protected function
	GetFileExtension(String $Input):
	String {
	/*//
	get what file extension to save the file with.
	//*/

		// todo - handle filenames that do not have extensions on them which
		// you know will happen on the internet. this will most likely require
		// an HTTP head to check the content type.

		$Ext = pathinfo($Input,PATHINFO_EXTENSION);
		if(!$Ext) return '.none';

		return $Ext;
	}

	protected function
	GetDownloadFilename(String $Input):
	String {
	/*//
	get the full filename to use when writing to disk.
	//*/


		if(!$this->Config->SaveFile)
		return $this->GetDownloadFilename_FromOriginal($Input);

		else
		return $this->GetDownloadFilename_FromConfig($Input);
	}

	protected function
	GetDownloadFilename_FromOriginal(String $Input):
	String {
	/*//
	generate a full filename from the original file's basename.
	//*/

		return sprintf(
			'%s%s%s',
			$this->ParseStringVariables($this->Config->SaveDir),
			DIRECTORY_SEPARATOR,
			basename($Input)
		);
	}

	protected function
	GetDownloadFilename_FromConfig(String $Input):
	String {
	/*//
	generate a full filename from our configuration option.
	//*/

		$Filename = sprintf(
			'%s%s%s',
			$this->Config->SaveDir,
			DIRECTORY_SEPARATOR,
			$this->Config->SaveFile
		);

		return $this->ParseStringVariables($Filename,[
			'DATE'       => date('Y-m-d'),
			'DATETIME'   => date('Y-m-d-H-i-s'),
			'EXT'        => $this->GetFileExtension(basename($Input)),
			'TIMESTAMP'  => date('U'),
			'FILENUM'    => sprintf(
				"%0{$this->Config->PadFileNums}d",
				$this->Config->LastIter
			),
			'FILENUMDIR' => sprintf(
				"%0{$this->Config->PadFileNums}d",
				$this->GetFileCount($this->Config->SaveDir)
			)
		]);
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
	PrintLine(String $Input):
	Self {
	/*//
	send messages to the console if enabled.
	//*/

		if(!$this->Config->Verbose)
		return;

		Nether\Console\Client::PrintLine($Input);

		return $this;
	}

	////////////////
	////////////////

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
