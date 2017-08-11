<?php /**
* Demonstration of what Wano does
*/

/* you do not need this if you are using composer */
require dirname(__FILE__) . '/../autoload.php';

/* enable Wano to start collecting PHP error messages */
\Wano\Nab::register();

/* make it collect backtraces only for warnings */
\Wano\Nab::$backtrace = E_WARNING | E_USER_WARNING;

/* Now let's do some stuff that raise PHP error messages */

/* Will trigger PHP notice "Undefined variable: y" */
$x[$y]['b'][] = 123;

/* Will trigger PHP notice "Undefined variable: a" */
$z = $x[$a];

/* Will trigger PHP notice "Array to string conversion" */
$stuff = array(1,2,3);
(string) $stuff;

/* Let's nest some functions so that we get a nice backtrace */
function a123()
{
	/* Will trigger PHP warning "proba" */
	trigger_error('proba', E_USER_WARNING);

	/* Will trigger PHP warning "Invalid error type specified" */
	trigger_error('test', E_WARNING);
}
function b456()
{
	a123();
}
b456();

/* Will trigger xxxx */

/* Will trigger:
 - PHP notice "Undefined variable: c"
 - PHP warning "Invalid argument supplied for foreach()"
*/
foreach ($c as $i) {}

/* Will trigger 5 times each:
 - PHP notice "Undefined variable: b"
 - PHP warning "fopen() expects at least 2 parameters, 1 given"
*/
for ($i=0; $i<5; $i++) fopen($b);

/* Will trigger PHP strict "Non-static method x::c() should not be called statically" */
class x { function c() {}}
x::c();

/* Will trigger PHP deprecated "mysql_connect(): The mysql extension is deprecated and will be removed in the future: use mysqli or PDO instead" */
mysql_connect();
