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
* Default Display Adapter
*/
class NiftyDisplay implements DisplayInterface
{
	/**
	* @inherit
	*/
	public function show(array $logs)
	{
		$stats = $this->stats($logs);
		$this->header($stats);

		while ($log = array_shift($logs))
		{
			$this->message($log);
		}
	}

	/**
	* Calculate the number of the different PHP error messages
	*
	* @param array $logs
	* @return array
	*/
	protected function stats(array $logs)
	{
		$stats = array(
			'all' => 0
		);

		foreach ($logs as $log)
		{
			$level = self::level($log[1]);
			if (empty($stats[$level]))
			{
				$stats[$level] = $log[0];
			} else
			{
				$stats[$level] += $log[0];
			}

			$stats['all'] += $log[0];
		}

		return $stats;
	}

	/**
	* Prints the JavaScript associated with the output
	*/
	protected function js()
	{
		?><script type="text/javascript">
wano = {
	"tab":function(el, what)
	{
		c = 'className',
		p = 'parentNode',
		g = 'getElementsByClassName',
		t = wano.toggleClass;

		// show\hide according to the selected tab
		//
		where = ('all' == what) ? '' : 'wano-level-' + what;
		if ('all' == what)
		{
			if (-1 != el[c].indexOf('wano-stats-active'))
			{
				// make the "All" tab expand and collapse
				//
				a = el[p][p][p][g]('wano-log')[0];
				if (-1 == a[c].indexOf('wano-log-hide'))
				{
					where = 'x';
				}

				t(el, 'wano-active-collapsed', 'x' == where);
				t(el, 'wano-active-expanded', 'x' != where);
			} else
			{
				t(el, 'wano-active-collapsed', false);
				t(el, 'wano-active-expanded', true);
			}
		}

		logs = el[p][p][p][g]('wano-message-level');
		for (var i=0; i < logs.length; i++)
		{
			m = logs[i];
			t(m[p][p][p], 'wano-log-hide', -1 == m[c].indexOf(where));
		}

		// mark the active tab
		//
		tabs = el[p][g]('wano-stats-tab');
		for (var i=0; i < tabs.length; i++)
		{
			t(
				tabs[i], 'wano-stats-active', (el == tabs[i])
			);
		}
	},

	"block":function(what, el)
	{
		where = 'wano-' + what;

		switch (el.tagName)
		{
			case 'A':
				button = el;
				log = el.parentNode.parentNode.parentNode;
				break;
			case 'SPAN':
				log = el.parentNode.parentNode;
				b = log.getElementsByClassName('wano-message')[0];
				c = b.getElementsByClassName('wano-extra')[0];
				button = c.getElementsByClassName(where)[0];
				break;
		}

		// mark active button and toggle block
		//
		close = wano.toggleClass(button, 'wano-active');
		b = log.getElementsByClassName(where)[1];
		b.style.display = close ? 'none' : 'block';

		// toggle expand class
		//
		x = 'wano-message-expnaded';
		y = log.getElementsByClassName('wano-block');
		z = 0;
		for (var i=0; i < y.length; i++)
		{
			if ('string' == (typeof y[i].style.display))
			{
				z += ('block' == y[i].style.display);
			}
		}
		wano.toggleClass(log, x, (z > 0));
	},

	"toggleClass":function(el,cl,expanded)
	{
		if ('undefined' == (typeof expanded))
		{
			expanded = (-1 == el.className.indexOf(cl));
		}

		if (expanded)
		{
			if (-1 == el.className.indexOf(cl))
			{
				el.className += ' ' + cl;
			}
		} else
		{
			el.className = el.className.replace(cl, '');
		}

		return !expanded;
	}
}

		</script><?php
	}

	/**
	* Prints the CSS styles for the output
	*/
	protected function css()
	{
		?><style>
		.wano-log {font-family: Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
			text-align:left; font-size:16px}
		.wano-log-hide {display:none}

		.wano-close {background: #3e4451; color: #abb2bf; cursor:pointer; padding: .09em .35em .1em;float:right;}
		.wano-close:before {content: "\00D7"} /* \000FBE \00D7  */
		.wano-close:hover {background:#555;color:#eee}

		.wano-message {font-family: Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
			background: #282c34;color: #abb2bf; font-size:.85em;
			padding:.9em 1em; border-bottom:solid 1px #667689;
			margin: 0 1.18em}

		.wano-message .wano-extra {float:right; font-size:.85em; margin-right: -.5em}
		.wano-message .wano-extra a {background: #3e4451;color: #64b6c3;cursor:pointer; padding: .1em .6em}
		.wano-message .wano-extra a:hover {background:#555;color:#eee}

		.wano-message .wano-extra a.wano-active .wano-icon {color:#fff}

		@media only screen and (max-width: 768px) {
			.wano-message .wano-extra a .wano-extra-title {display:none}
			.wano-message .wano-extra a {display:inline-block; text-align:center; width:2em;}
		}

		.wano-message .wano-message-level {color:#000; font-size:.9em; padding: .1em .6em .09em}
		.wano-level-unknown {background: #8b93a3}
		.wano-level-warning {background: #d86c74}
		.wano-level-notice {background: #c678dd}
		.wano-level-strict {background: #4bb1b1}
		.wano-level-deprecated {background: #6caff2}
		.wano-level-error,
		.wano-level-emergency,
		.wano-level-parse {background: #a00; color:#fff}

		.wano-message .wano-message-text {color:#fff}
		.wano-message .wano-message-count {background:khaki; color:#000; padding: 0 .45em; border-radius: 5px}
		.wano-message .wano-message-count:after {content: " times"}

		.wano-message .wano-origin {color: #abb2bf; font-size:.85em; margin-top:.5em}
		.wano-message .wano-origin-line:before {content:":"}
		.wano-message .wano-origin-line {color: #ccc}

		.wano-block {background: #282c34; color: #abb2bf; border-bottom:solid 1px #667689;
			border-left: solid .25em teal; padding: 0 0 0 1em; margin: 0 1.85em;
			font-size:.8em; padding: .9em 1em; display:none}
		.wano-block:hover {background: #2c3239}
		.wano-block b {color:white; display:block; cursor: default}

		.wano-block pre {font-family: Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; margin:1em 0 .5em; overflow:auto}

		.wano-message-expnaded {margin-bottom:.45em}

		.wano-source .wano-source-line {color:#667689; margin-right:1em}
		.wano-source .wano-highlight {background:#4b5363; color:#eee}

		#wano-header {font-family: Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
			background: #282c34; color: #abb2bf; margin: 1em 1em 0 1em;
			border-bottom:solid 1px #667689; font-size:16px}

		#wano-stats {font-size: .75em; border-bottom: solid 5px #3c4249}
		#wano-stats span.wano-stats-tab {cursor:pointer; display:inline-block;
			color:#667689;
			padding: .65em 3em .35em; border: solid 0px #2a2a2a;
			border-width: 0 1px 1px 0; text-transform: capitalize}
		#wano-stats span.wano-stats-tab:hover {background: #2c3239; color: #abb2bf}
		#wano-stats span.wano-stats-tab span {color:#667689; padding: .15em .65em .17em;
			min-width: .8em; background: #3c4249;
			display:inline-block; text-align: center; border-radius: .75em}
		#wano-stats span.wano-stats-tab:hover span {background: #4c5259; color: #abb2bf}

		#wano-stats span.wano-stats-active,
		#wano-stats span.wano-stats-active:hover {background:#3c4249; color:#fff;
			border-bottom: 0px; border-left: solid 2px #6caff2; position:relative; bottom:-1px;}
		#wano-stats span.wano-stats-active span,
		#wano-stats span.wano-stats-active:hover span {background:#fff; color: #667689}

		#wano-stats span.wano-active-collapsed:before {content: "\25B6   "}
		#wano-stats span.wano-active-expanded:before {content: "\25BC   "}

		#wano-about {float:right; background:#3c4249; font-size:.75em;
			padding: .3em .8em; margin: .5em; border-radius:.45em;
			color:#667689; cursor:default}
		#wano-about a {text-decoration:none; color:#ddd}
		#wano-about:hover {background: #4c5259; color: #abb2bf}
		#wano-about:hover a {color: #fff}

		#wano-header {min-height:2em; background: #383c44}

		</style><?php
	}

	/**
	* Prints the header leading the output
	*
	* @param array $stats number of different type of PHP error messages
	*/
	protected function header(array $stats)
	{
		$this->css();
		$this->js();

		?><div id="wano-header">
			<div id="wano-about">
				created with <a href="https://github.com/kktsvetkov/wano">Wano</a>
			</div>


			<div id="wano-stats"><?php
			foreach ($stats as $level => $number)
			{
				echo '<span onclick="wano.tab(this, \'', $level,
					'\')" class="wano-stats-tab', (
						'all' == $level
							? (' wano-stats-active'
								. (!!static::$collapsed
									? ' wano-active-collapsed'
									: ''
									)
								)
							: ''
						), '">';
				printf('%1$s <span>%2$d</span>',
					$level, $number);
				echo '</span>';
			}
			?></div>

		</div><?php
	}

	/**
	* Prints the details about a single recorded PHP error message
	*
	* @param \SplFixedArray $log
	*/
	protected function message(\SplFixedArray $log)
	{
		$level = static::level($log[1]);

		$code = !empty(static::$source)
			? self::getSource($log[3], $log[4])
			: null;

		echo '<div class="wano-log', (
			!!static::$collapsed
				? ' wano-log-hide'
				: ''
			), '">';
			echo '<div class="wano-message">';
				echo '<div class="wano-extra">';

					if (!empty($code))
					{
						echo '<a class="wano-source" onClick="wano.block(\'source\',this)"><span class="wano-icon">&lt;/&gt;</span> ';
							echo '<span class="wano-extra-title">Source Code</span></a> ';
					}

					if (isset($log[5]))
					{
						echo '<a class="wano-backtrace" onClick="wano.block(\'backtrace\',this)"><span class="wano-icon">&#9776;</span> ';
						echo '<span class="wano-extra-title">Backtrace</span></a> ';
					}
				echo '</div>';
				echo '<div class="wano-text">';
					echo '<span class="wano-message-level wano-level-', $level, '">', $level, '</span> ';
					echo '<span class="wano-message-text">', htmlspecialchars($log[2]),'</span> ';
					if ($log[0] > 1)
					{
						echo '<span class="wano-message-count">', $log[0], '</span> ';
					}
				echo '</div>';
				echo '<div class="wano-origin">';
					echo '<span class="wano-origin-file">', $log[3], '</span><span class="wano-origin-line">', $log[4], '</span> ';
				echo '</div>';
			echo '</div>';

			if (!empty($code))
			{
				echo '<div class="wano-block wano-source">';
					echo '<span class="wano-close" onClick="wano.block(\'source\',this)"></span>';
					echo '<b><span class="wano-icon">&lt;/&gt;</span> Source Code</b>';
					self::printSource($code, $log[4]);
				echo '</div>';
			}

			if (isset($log[5]))
			{
				echo '<div class="wano-block wano-backtrace">';
					echo '<span class="wano-close" onClick="wano.block(\'backtrace\',this)"></span>';
					echo '<b><span class="wano-icon">&#9776;</span> Backtrace</b>';
					echo '<pre>', (empty($log[5])
						? "#0 {$log[3]}({$log[4]})"
						: htmlspecialchars($log[5])),
						'</pre>';
				echo '</div>';
			}
		echo '</div>';
	}

	///////////////////////////////////////////////////////////////////////

	/**
	* Converts an integer error level value into string (e.g. "warning" or "notice")
	*
	* @param integer $errno
	* @return string
	*/
	protected static function level($errno)
	{
		$level = 'unknown';

		/* 674 = E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING */
		if ($errno & 674)
		{
			$level = 'warning';
		} else
		/* 1032 = E_NOTICE | E_USER_NOTICE */
		if ($errno & 1032)
		{
			$level = 'notice';
		} else
		if ($errno & E_STRICT)
		{
			$level = 'strict';
		} else
		/* 24576 = E_DEPRECATED | E_USER_DEPRECATED */
		if ($errno & 24576)
		{
			$level = 'deprecated';
		} else
		if ($errno & E_PARSE)
		{
			$level = 'parse';
		} else
		if ($errno & E_RECOVERABLE_ERROR)
		{
			$level = 'emergency';
		} else
		/* 337 = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR */
		if ($errno & 337)
		{
			$level = 'error';
		}

		return $level;
	}

	///////////////////////////////////////////////////////////////////////

	/**
	* @var integer how many lines of source-code to show for each message;
	*	set this to 0 to turn off the source-code block from the output
	*/
	public static $source = 7;

	/**
	* Get lines from source file
	*
	* @param string $filename file where the PHP error message was raised
	* @param integer $line the line from the file where the PHP error message was raised
	* @return array
	*/
	protected static function getSource($filename, $line)
	{
		$lines_count = (int) self::$source;

		$lines_count = ($lines_count < 1)
			? 8
			: ($lines_count > 20
				? 20
				: $lines_count);

		try {
			if($line < 0
				|| ($lines = file($filename)) === false
				|| ($_count = count($lines) ) <= $line)
			{
				return array();
			}

			$half_lines_count = ceil($lines_count/2);
			$begin_line = ($line - $half_lines_count > 0)
				? $line - $half_lines_count
				: 0;
			$end_line = $lines_count + $begin_line;

			$source_lines = array();
			for($i = $begin_line; $i <= $end_line; ++$i)
			{
				if (!empty($lines[$i]))
				{
					$source_lines[$i+1] = $lines[$i];
				}
			}

			unset($lines);
			return $source_lines;
		}
		catch (\Exception $e)
		{
			return array();
		}
	}

	/**
	* Prints source-code lines
	*
	* @param array $source key/value pairs, where the key is the line
	*	number, and the value is the actual source code line
	* @param integer $line the line from the file where the PHP error
	*	message was raised; this line will be highlighted
	*/
	protected static function printSource(array $source, $line)
	{
		echo '<pre>';
		foreach ($source as $number => $code)
		{
			if ($line == $number)
			{
				echo '<div class="wano-highlight">';
			}
			printf('<span class="wano-source-line">%05d:</span> %s',
				$number, htmlspecialchars($code));

			if ($line == $number)
			{
				echo '</div>';
			}
		}
		echo '</pre>';
	}

	/**
	* @var boolean whether the logs to start expanded or not
	*/
	public static $collapsed = true;
}
