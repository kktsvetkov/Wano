# Wano
Wano (short for **WA**~~rnings~~ & **NO**~~tices~~), is a small PHP tool meant to help reporting PHP error messages.

It is meant to be very easy to use. If you have any experience with PHP error messages such as warnings and notices, you know how annoying it is to have them pop up in the middle of your printed markup, in attributes and between HTML tags, messing things up. ***Wano*** is meant to help with that.

It is also important to mention that it is a bad policy to ignore and silence PHP error messages. That has its toll on  your PHP code performance. If you clean up all the warnings, notices, etc. you are going to improve the execution time of your scripts.

## Basic use
In order to use it, you just need to call `\Wano\Nab::register()`

	\Wano\Nab::register();

...and that's it. Default settings and configurations will be used and you do not have to do anything else.

## How it works ?
***Wano*** does two things:

 * first, it registers a custom error_handler that will collect the PHP error messages raised by the code
 * second, it attached a callback through register_shutdown_function() that prints the collected PHP error messages at the end of the page

By doing the above, you are going to have the printed HTML content without being riddled with PHP error messages, and in the same time you are still going to see them as a report appended at the end of the page. It's that simple. The idea is not to have clumsy and overdressed library, but a simple tool that its job well.

## What's inside ?
As explained above, there are two tasks that ***Wano*** does: a) collects the PHP error messages and b) print them at the end of the page.

`\Wano\Nab` is the class that has the custom error_handler, which collects the PHP error messages.

`\Wano\Display\` namespace houses the classes used to render the list of collected PHP error messages. There is the `\Wano\Display\DisplayInterface` interface that must be implemented if you want to create a new such "Display" class.

## Advance use
Here's what you can do to step out of the default behaviour and settings.

You can choose what type of PHP error levels ***Wano*** should collect. The format is the same used for error_reporting() -- a bitmask of the error levels. This is provided as the argument for `\Wano\Nab::register()`

	\Wano\Nab::register(E_WARNING | E_USER_WARNING | E_NOTICE | E_USER_NOTICE);

Backtraces are really helpful when you want to track how a certain PHP error message was raised. On the other hand, in some occasions for some PHP error message levels having backtraces is just overhead. Using the same bitmask format you can declare which PHP error message levels to include backtraces when they are reported:

	\Wano\Nab::$backtrace = E_WARNING | E_USER_WARNING;

You can change how the results are printed by creating your own `\Wano\Display` class. To do that you have to create a new class that is implementing the `\Wano\Display\DisplayInterface` interface, and then use `\Wano\Nab::setDisplay()` method to attach it:

	\Wano\Nab::setDisplay(new \Wano\Display\BasicDisplay);

This is not recommended, but if you want to, you can manually report directly into ***Wano***, like this:

	\Wano\Nab::error_log(E_USER_WARNING, 'egati probata', __FILE__, __LINE__);

If for whatever reason you do not want to have a register_shutdown_function() print the results, you can do it manually. For that purpose you need to replace `\Wano\Nab::register()` with `\Wano\Nab::registerErrorHandler()` which will only attach the custom error_handler. Then, you are ready to print the results, you have to call `\Wano\Nab::display()`

	\Wano\Nab::registerErrorHandler();
	...

	/* It's time, print what you have collected already */
	\Wano\Nab::display();
