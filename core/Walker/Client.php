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
			'walk <configname>',
			'walks the path specified by the conf/<configname>.json file',
			''
		);

		return 0;
	}

	public function
	HandleWalk():
	Int {
	/*//
	run a specified job. no. walk the specified job.
	//*/

		$Config = $this->GetInput(2);

		if(!$Config) {
			$this::Message('no config specified.');
			$this->Run('help');
			return 1;
		}

		$this::Message("> loading conf/{$Config}.json");

		try { $Walker = new Walker\Walker($Config); }
		catch(Exception $Error) {
			$this::Message($Error->GetMessage());
		}

		return 0;
	}

}
