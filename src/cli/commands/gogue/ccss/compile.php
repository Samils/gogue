<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Sami\CommandLineInterface\gogue
 * - Autoload, application dependencies
 */
namespace Sammy\Packs\Sami\CommandLineInterface\gogue\ccss {
	use Sammy\Packs\Sami\CommandLineInterface\Parameters;
	use Sammy\Packs\Sami\CommandLineInterface\Options;
	use Sammy\Packs\Gogue\Builder;
	/**
	 * Make sure the command base internal function is not
	 * declared in the php global scope defore creating
	 * it.
	 */
	if (!function_exists ('Sammy\Packs\Sami\CommandLineInterface\gogue\ccss\compile')) {
	/**
	 * @function compile
	 * Base internal function for the
	 * Sami\Cli module command 'compile'.
	 * -
	 * This is (in the ils environment)
	 * an instance of the php module,
	 * wich should contain the module
	 * core functionalities that should
	 * be extended.
	 * -
	 * For extending the module, just create
	 * an 'exts' directory in the module directory
	 * and boot it by using the ils directory boot.
	 * -
	 * -
	 * @param array $args
	 * list of sent arguments to the
	 * current cli command.
	 */
	function compile (Parameters $parameters, Options $options) {
		return Builder::Build ($parameters, $options, [
      'target' => 'capsule-css-parser'
    ]);
	}}
}
