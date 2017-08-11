<?php /**
* Wano: reports raised PHP error messages (mainly warnings and notices)
*
* @author Kaloyan Tsvetkov (KT) <kaloyan@kaloyan.info>
* @package Wano
* @link https://github.com/kktsvetkov/wano/
* @license http://opensource.org/licenses/LGPL-3.0 GNU Lesser General Public License, version 3.0
*/

namespace Wano\Display;

/**
 * Interface that all Wano Display adapters must implement
 */
interface DisplayInterface
{
	/**
	* Prints the harvested PHP error messages
	*
	* @param array $logs the collected messages; each message is recorded in
	*	that list as \SplFixedArray with 5 to 6 elements:
	*	0 - (integer) reference count, how many times this particular PHP error message has occured
	*	1 - (integer) error level
	*	2 - (string) error message
	*	3 (string) and 4 (integer) - filename and line, where the PHP error message was raised
	*	5 - (string) backtrace, if enabled for this error level, see {@link \Wano\Nab::$backtrace}
	*/
	public function show(array $logs);
}
