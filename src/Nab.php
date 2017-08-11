<?php /**
* Wano: reports raised PHP error messages (mainly warnings and notices)
*
* @author Kaloyan Tsvetkov (KT) <kaloyan@kaloyan.info>
* @package Wano
* @link https://github.com/kktsvetkov/wano/
* @license http://opensource.org/licenses/LGPL-3.0 GNU Lesser General Public License, version 3.0
*/

namespace Wano;

/**
* Collects reported PHP error messages, and prints them when the script is done
*
* The work this class has to do is simple:
* - first, it registers a custom error handler (with
*	{@link set_error_handler()}) which collects
*	the PHP error messages raised;
* - second, using {@link register_shutdown_function()} it
*	will call one the Wano\Display\* classes to display them.
*/
class Nab
{
	/**
	* @var integer which PHP error levels are to be collected
	*	from the PHP error messages, see {@link error_reporting()}
	*/
	protected static $error_types = E_ALL;

	///////////////////////////////////////////////////////////////////////

	/**
	* Registers a custom error_handler, that will harvest all the reported
	* PHP error messages for the provided error levels
	*
	* @param integer $level (optional) error reporting level, see
	*	{@link error_reporting()}; if left empty then the default
	*	value from {@link \Wano\Nab::$error_types} will be used
	* @uses set_error_handler()
	*/
	public static function registerErrorHandler($level = null)
	{
		return set_error_handler(
			array(__CLASS__,'error_log'),
			!empty($level)
				? self::$error_types = (int) $level
				: self::$error_types
		);
	}

	/**
	* Reigsters a shutdown function ({@link \Wano\Nab::display()}),
	* which will print the rendered results
	*
	* @uses register_shutdown_function()
	*/
	public static function registerDisplay()
	{
		return register_shutdown_function(
			array(__CLASS__, 'display')
		);
	}

	/**
	* Shortcut for calling both {@link \Wano\Nab::registerErrorHandler()}
	* and {@link \Wano\Nab::registerDisplay()}
	*
	* @param integer $level (optional) error reporting
	*	level, see {@link \Wano\Nab::registerErrorHandler()}
	*	and {@link error_reporting()}
	*
	* @uses \Wano\Nab::registerErrorHandler()
	* @uses \Wano\Nab::registerDisplay()
	*/
	public static function register($level = null)
	{
		static::registerErrorHandler($level);
		static::registerDisplay();
	}

	///////////////////////////////////////////////////////////////////////

	/**
	* @var integer bitmask integer value, used to indicate for which
	* error levels to attach backtraces; this makes it possible for you
	* to pick and choose which error levels will include backtraces
	*/
	public static $backtrace = E_ALL;

	/**
	* Returns the backtrace to be used in the logged PHP error message
	* @return string
	*/
	protected static function backtrace()
	{
		// found out that the best way to get a formatted
		// and somewhat tiny backtrace is to get it from
		// \Exception::getTraceAsString()
		//
		$e = new \Exception;
		$trace = $e->getTraceAsString();

		// cut the top two lines (wano::error_log() and
		// wano::backtrace()), cut the last one ("{main}")
		// and ...
		//
		$trace = preg_replace(
			'~(#(0|1) [^\x0A]+\x0A)*|(\x0A#\d+ \{main\})?|(#\d+ )~Uis',
			'', $trace);

		// ...and check if it is empty - no need
		// to process empty stacktrace; if it is,
		// continue and...
		//
		if ('{main}' == $trace)
		{
			return '';
		}

		// ...and redo the numbering, so that it starts from "#0"
		//
		$t = preg_split('~\x0A~', trim($trace));
		$trace = '';
		foreach ($t as $line => $callback)
		{
			$trace .= sprintf("#%d %s\n", $line, $callback);
		}

		return $trace;
	}

	///////////////////////////////////////////////////////////////////////

	/**
	* @var boolean whether to count duplicates:
	*	FALSE = do allow duplicate PHP error messages,
	*	TRUE = do not allow duplicate PHP error messages and
	*	instead just count how many times they have been repeated
	*/
	public static $count = true;

	/**
	* @var array where the reported error messages are collected
	*/
	protected static $log = array();

	/**
	* Custom error_handler for collecting raised PHP error messages
	*
	* @param integer $errno level of the error raised
	* @param string $errstr error message
	* @param string $errfile (optional) filename that the error was raised in
	* @param integer $errline (optional) line number the error was raised at
	* @see set_error_handler()
	*/
	public static function error_log($errno, $errstr, $errfile = null, $errline = null)
	{
		$doBacktrace = empty(self::$backtrace)
			? false
			: ($errno & (int) self::$backtrace);

		// create a "signature" (if counting duplicates is
		// enabled) and check...
		//
		$signature = empty(static::$count)
			? count(static::$log)
			: $errno
				. (strlen($errstr) > 80
					? substr($errstr, 0, 80)
					: $errstr)
				. $errfile
				. $errline;

		if (!empty(static::$count) && isset(self::$log[$signature]))
		{
			// ... and check if this has been reported
			// earlier; if it is, just increase its
			// reference count
			//
			self::$log[$signature][0] = 1 + self::$log[$signature][0];

		} else
		{
			// ... and if doesn't exists, add it as reported.
			//
			// Using SplFixedArray instead of an array is a
			// desperate attempt not to eat to much memory
			//
			$error = new \SplFixedArray(
				$doBacktrace
					? 6
					: 5
				);
			$error[0] = 1;		// count
			$error[1] = $errno;	// error level
			$error[2] = $errstr;	// error message
			$error[3] = $errfile;
			$error[4] = $errline;

			if ($doBacktrace)
			{
				$error[5] = self::backtrace();
			}

			static::$log[$signature] = $error;
		}
	}

	///////////////////////////////////////////////////////////////////////

	/**
	* @var \Wano\Display\DisplayInterface this object will be used to render
	*	the harvested PHP error messages
	*/
	protected static $display;

	const defaultDisplay = '\\Wano\\Display\\NiftyDisplay';

	/**
	* Replace default display adapter
	*
	* @param \Wano\DisplayInterface $display
	* @return \Wano\DisplayInterface
	*/
	public static function setDisplay(\Wano\Display\DisplayInterface $display)
	{
		return static::$display = $display;
	}

	/**
	* Renders the harvested PHP error messages
	*/
	public static function display()
	{
		if (empty(self::$log))
		{
			return;
		}

		if (empty(self::$display))
		{
			$_ = static::defaultDisplay;
			self::$display = new $_;
		}

		return self::$display->show(self::$log);
	}
}
