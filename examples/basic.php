<?php /**
* Output with \Wano\Display\BasicDisplay
*/

/* you do not need this if you are using composer */
require __DIR__ . '/../autoload.php';

\Wano\Nab::setDisplay(new \Wano\Display\BasicDisplay);

include __DIR__ . '/demo.php';
