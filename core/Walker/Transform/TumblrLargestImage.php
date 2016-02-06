<?php

namespace Walker\Transform;
use \Nether;
use \Walker;

use \Exception;
use \StdClass;

class TumblrLargestImage
implements Walker\Proto\TransformURL {

	static public function
	WillHandleTransform(String $URL):
	Bool {
		$Ext = strtolower(pathinfo(
			basename($URL),
			PATHINFO_EXTENSION
		));

		// does it look like an image?
		if(!in_array($Ext,['bmp','gif','jpg','jpeg','png']))
		return false;

		// and match their usual pattern?
		return (bool)preg_match(
			'/_(\d)+\./',
			$URL
		);
	}

	static public function
	Transform(String $URL):
	String {
		foreach([1280,500,400,250] as $Size) {
			$URL = preg_replace(
				'/_(\d+)\./',
				"_{$Size}.",
				$URL
			);

			// todo: test that we don't get back the not found xml that
			// tumblr returns when... not... found.

			return $URL;
		}
	}

}
