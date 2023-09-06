<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Gogue\Builder
 * - Autoload, application dependencies
 *
 * MIT License
 *
 * Copyright (c) 2020 Ysare
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace Sammy\Packs\Gogue\Builder {
	use Sammy\Packs\Sami\CommandLineInterface\Parameters;
	use Sammy\Packs\Sami\CommandLineInterface\Options;
	use Sammy\Packs\Sami\CommandLineInterface\Console;
  use Sammy\Packs\Gogue\Helper;
	use Sammy\Packs\Gogue;
  use Sammy\Packs\Path;
	use Saml;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\Gogue\Builder\Base')) {
  /**
   * @trait Base
   * Base internal trait for the
   * Gogue\Builder module.
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
   */
  trait Base {
  	/**
  	 * @method void description
  	 *
  	 */
  	public static function Build (Parameters $params, Options $options, array $buildOptions = []) {
  		$gogueConfig = Gogue::GetConfig ();

  		$gogueTarget = $options->target;

      $gogueConfig = array_merge ($gogueConfig, $buildOptions);

  		if (empty ($gogueTarget)) {
  			$gogueTarget = $gogueConfig ['target'];
  		}

      $path = new Path;

  		$filePath = $path->join ($params->first ());

  		if (!is_file ($filePath)) {
  			Console::Error ("File not found: {$filePath}");

  			exit (0);
  		}

  		$buildOptions = array_merge ([
  			'log' => true,
  			'keepExtensionInOutFile' => true
  		], $buildOptions);

  		$targetHandler = Gogue::GetTargetHandler ($gogueTarget, $filePath);

  		if (!is_object ($targetHandler)) {
  			Console::Error ("Invalid gogue target config for: {$gogueTarget}");

  			exit (0);
  		}

  		if (isset ($gogueConfig ['outDir'])) {
  		  /**
  		   * [$outDir Out Directory]
  		   * @var string
  		   */
  		  $configOutDir = preg_replace ('/^(\\\|\/)+/', '',
  		    $gogueConfig ['outDir']
  		  );

  		  $gogueConfig ['outDir'] = join (DIRECTORY_SEPARATOR, [
  		  	$gogueConfig ['baseDir'], (string)($configOutDir)
  		  ]);

  		  $outDir = $gogueConfig ['outDir'];

        if (!is_dir ($outDir)) {
          Helper::MkDir ($outDir);
        }

  		  $outDirRe = self::slashReSpecialChars ($outDir);

  		  $outDirRe = "/^({$outDirRe})/";
  		}

  		if (method_exists ($targetHandler, 'gogueConfigInit')) {
  			$gogueTargetConfig = [];

  			if (!!isset ($gogueConfig [$gogueTarget])) {
  				$gogueTargetConfig = $gogueConfig [ $gogueTarget ];
  			}

  			$targetHandler->gogueConfigInit ($gogueTargetConfig);
  		}

  		$ds = DIRECTORY_SEPARATOR;
  		$fileDirName = dirname ($filePath);
  		$fileName = pathinfo ($filePath, PATHINFO_FILENAME);

  		if (!!($buildOptions ['keepExtensionInOutFile'])) {
  			$filePathSlices = preg_split ('/(\\\|\/)+/', $filePath);

  			$fileName = $filePathSlices [-1 + count ($filePathSlices)];
  		}

  		$newFilePath = join (DIRECTORY_SEPARATOR, [
  			$fileDirName, "{$fileName}.cache.php"
  		]);

  		if (!!(is_dir ($gogueConfig ['outDir']))) {
  		  $baseDirRe = self::slashReSpecialChars ($gogueConfig ['baseDir']);

  		  $baseDirRe = "/^({$baseDirRe})/";
  		  $fileDirParent = preg_replace ('/^(\\\|\/)+/', '',
  		    preg_replace ($baseDirRe, '', $fileDirName)
  		  );

  		  $newFilePath = join (DIRECTORY_SEPARATOR, [
	  			$outDir, $fileDirParent, "$fileName.cache.php"
	  		]);
  		}

  		if (!is_dir (dirname ($newFilePath))) {
  		  Helper::MkDir (dirname ($newFilePath));
  		}

  		$newFileHandle = fopen ($newFilePath, 'w');

  		fwrite ($newFileHandle, $targetHandler->run ());

  		fclose ($newFileHandle);

  		if (isset ($outDirRe)) {
  		  $nFilePath = preg_replace ('/^(\\\|\/)+/', '',
  		    preg_replace ($outDirRe, '', $newFilePath)
  		  );
  		} else {
  		  $nFilePath = $newFilePath;
  		}

  		if (is_bool ($buildOptions ['log']) && $buildOptions ['log']) {
  			echo join ('', [
  				"\033[47m\033[1;34m- Compiled: ",
  				"{$nFilePath}\033[m\033[m\n"
  			]);
  		}
  	}

  	protected static function slashReSpecialChars ($string) {
			/**
			 * [$re Regular Expression]
			 * @var string
			 */
			$re = '/[\/\^\$\[\]\{\}\(\)\\\\.]/';
  		return preg_replace_callback ($re, function ($match) {
		    return '\\' . $match[0];
		  }, $string);
  	}
  }}
}
