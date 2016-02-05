<?php

namespace Walker;
use \Nether;
use \Walker;

use \Exception;

class Client
extends Nether\Console\Client {
/*//
this class handles the actions which can be performed by the client client.
technically, this class is the cli client.
//*/

	public function
	HandleHelp():
	Int {
	/*//
	print out the info a user needs to know when needing help or failing at
	trying to do something.
	//*/

		$this::Messages(
			'',
			'create <configname>',
			'creates a new config file with the default settings for you to fill in.',
			'',
			'walk <configname>',
			'walks the path specified by the conf/<configname>.json file',
			''
		);

		return 0;
	}

	////////////////
	////////////////
	
	public function
	HandleCreate():
	Int {
		
		$ConfigName = $this->GetInput(2);
		if(!$ConfigName) {
			$this::Message('no config specified.');
			$this->Run('help');
			return 1;
		}
		
		$Config = new Walker\Config;
		$Config->SetName($ConfigName);
		
		if(file_exists($Config->GetFile()) && !$this->GetOption('force')) {
			$this::Message("a config for {$ConfigName} already exists. use --force to overwrite.");
			return 1;
		}
		
		$this::Message("writing default settings to {$Config->GetFile()}");
		$Config->Write();	
		return 0;
	}
	
	////////////////
	////////////////

	public function
	HandleWalk():
	Int {
	/*//
	run a specified job. no. walk the specified job.
	//*/

		$Config = $this->GetInput(2);
		if(!$ConfigName) {
			$this::Message('no config specified.');
			$this->Run('help');
			return 1;
		}

		try { $Walker = new Walker\Engine($Config); }
		catch(Exception $Error) {
			$this::Message($Error->GetMessage());
		}

		return 0;
	}

}
