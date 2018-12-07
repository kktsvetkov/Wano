<?php /**
* Output with \Wano\Display\NiftyDisplay
*/

/* you do not need this if you are using composer */
require __DIR__ . '/../autoload.php';

\Wano\Nab::setDisplay(new \Wano\Display\NiftyDisplay);

/* Make all log messages expanded when page loads */
\Wano\Display\NiftyDisplay::$collapsed = false;

include __DIR__ . '/demo.php';
