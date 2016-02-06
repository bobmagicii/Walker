<?php

namespace Walker\Proto;
use \Walker;

interface TransformURL {

	static public function
	WillHandleTransform(String $URL):
	Bool;
	/*//
	define this method to tell the system if this class will be willing to
	deal with the url in question. this way if multiple are defined they
	can refuse to handle them if the pattern does not fit. you'll mostly
	be using regex in here or something like that.
	//*/

	static public function
	Transform(String $URL):
	String;
	/*//
	define this method to actually do the transform you want to perform.
	give a string take a string.
	//*/

}
