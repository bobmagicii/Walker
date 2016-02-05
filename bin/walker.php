<?php

require(sprintf(
	'%s/vendor/autoload.php',
	dirname(__FILE__,2)
));

(new Walker\Client)->Run();
