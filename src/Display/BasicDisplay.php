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
* A Simpler Display Adapter
*/
class BasicDisplay implements DisplayInterface
{
	protected static $levels = array(
		E_ERROR => 'error',
		E_WARNING => 'warning',
		E_PARSE => 'error',
		E_NOTICE => 'notice',
		E_CORE_ERROR  => 'error',
		E_CORE_WARNING => 'warning',
		E_COMPILE_ERROR => 'error',
		E_COMPILE_WARNING => 'warning',
		E_USER_ERROR => 'error',
		E_USER_WARNING => 'warning',
		E_USER_NOTICE => 'notice',
		E_STRICT => 'strict',
		E_RECOVERABLE_ERROR => 'error',
		E_DEPRECATED => 'deprecated',
		E_USER_DEPRECATED => 'deprecated',
		);

	/**
	* @inherit
	*/
	public function show(array $logs)
	{
		echo '<div style="background:khaki; margin:1em; padding:1em">';
		foreach ($logs as $log)
		{
			echo '<div style="margin-bottom: 1em">';
			echo '<p>';
			echo '<span style="background:black;color:white">PHP ',
				isset(self::$levels[$log[1]])
					? self::$levels[$log[1]]
					: 'unknown',
				'</span> ';

			echo '<em>&quot;',
				htmlspecialchars($log[2]),
				'&quot;</em>';

			if ($log[0] > 1)
			{
				echo ' <b>(', $log[0], ' times)</b> ';
			}

			echo ' at <u>', $log[3], ':', $log[4], '</u>';
			echo '</p>';

			if (isset($log[5]))
			{
				echo '<pre style="margin-left:1em">',
					htmlspecialchars($log[5]), '</pre>';
			}
			echo '</div>';
		}
		echo '</div>';
	}
}
