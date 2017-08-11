<?php /**
* Output with \Wano\Display\NiftyDisplay
*/

include __DIR__ . '/demo.php';
\Wano\Nab::setDisplay(new \Wano\Display\NiftyDisplay);

/* Make all log messages expanded when page loads */
\Wano\Display\NiftyDisplay::$collapsed = false;
