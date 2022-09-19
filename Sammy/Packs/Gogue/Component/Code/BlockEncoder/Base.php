<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Gogue\Component\Code\BlockEncoder
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
namespace Sammy\Packs\Gogue\Component\Code\BlockEncoder {
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted 
   * when trying to run the current command by the cli 
   * API.
   */
  if (!trait_exists ('Sammy\Packs\Gogue\Component\Code\BlockEncoder\Base')) {
  /**
   * @trait Base
   * Base internal trait for the
   * Gogue\Component\Code\BlockEncoder module.
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
  	 * 
  	 */
  	public function encodeBlocks ($partialCode = '') {

  		$blockStore = [];
  	  $partialCodeLen = strlen ($partialCode);

  	  $blocksSyntaxes = [
  	  	['{', '}', 'block'],
	      ['(', ')', 'group'],
	      ['[', ']', 'array']
  	  ];

  	  for ($n = 0; $n < 1; $n++) {
  	    for ($i = 0; $i < strlen ($partialCode); $i++) {
  	      # Map the syntaxes inside the
  	      # blocksSyntaxes array in order
  	      # getting one of them being used
  	      # in the current position in the
  	      # code.
  	      foreach ($blocksSyntaxes as $syntax) {
  	        $codeSlice = substr ($partialCode, $i, strlen ($syntax [0]));

  	        if ($codeSlice === $syntax [0]) {

  	          #exit ($codeSlice);

  	          #echo $codeSlice, " => ", $i, "\n\n";

  	          $blockBody = $this->getBlockEnd (
  	            $partialCode,
  	            $syntax,
  	            $i + 1
  	          );

  	          #if (!$blockBody)
  	          #   continue;

  	          #echo "\n", $blockBody, "\n\n";

  	          $blockType = $syntax [2];
  	          $blockId = $this->generateBlockId ();
  	          #array_push ($blockStore, null);
  	          $syntaxName = "::={$blockType}-block{$blockId}::";

  	          $openChar = $syntax [0];
  	          $closeChar = $syntax [1];

  	          $blockBodyContent = $this->encodeBlocks (
  	            substr ($blockBody [0], 1, strlen ($blockBody[0]) - 2)
  	          );

  	          $blockStore = array_merge ($blockStore, $blockBodyContent ['store']);

  	          $blockBody [0] = join ('', [
  	            $openChar,
  	            $blockBodyContent [0],
  	            $closeChar
  	          ]);

  	          $blockStore [$blockId] = $blockBody;

  	          $replacement = $syntaxName;

  	          $partialCode = substr_replace (
  	            $partialCode,
  	            $replacement,
  	            $blockBody ['start'],
  	            $blockBody ['end']
  	          );
  	        }
  	      }
  	    }
  	  }

  	  #echo "\n\n";
  	  #print_r($blockStore);
  	  #echo "\n\n";
  	  #print_r($blockStore);
  	  return [$partialCode, 'store' => $blockStore];
  	}

  	/**
  	 * Encode blocks
  	 */
  	public function decodeBlocks ($partialCode, array $options = []) {
  		$store = $options;

  		if (isset ($options ['store'])) {
  			$store = $options ['store'];
  		}

  		$context = new static;

  		$context->store = $store;

  		$callback = \Closure::bind (function ($match) {
  			$id = $match [ 2 ];

  			if (!isset ($this->store [$id])) {
  			  return $match [0];
  			}

  			$blockDatas = $this->store [$id];

  			return trim ($this->decodeBlocks ($blockDatas [0], $this->store));
  		}, $context, Context::class);

  		$re = '/::=(array|block|group)-block([0-9]+)::/i';

  		return preg_replace_callback ($re, $callback, $partialCode);
  	}

    /**
     * Encode blocks
     */
    public function decodeBlock ($partialCode, array $options = []) {
      $store = $options;

      if (isset ($options ['store'])) {
        $store = $options ['store'];
      }

      $context = new static;

      $context->store = $store;

      $callback = \Closure::bind (function ($match) {
        $id = $match [ 2 ];

        if (!isset ($this->store [$id])) {
          return $match [0];
        }

        $blockDatas = $this->store [$id];

        return trim ($blockDatas [0]);
      }, $context, Context::class);

      $re = '/::=(array|block|group)-block([0-9]+)::/i';

      return preg_replace_callback ($re, $callback, $partialCode);
    }

  	private function getBlockEnd ($partialCode, $block, $point) {
  	  $codeLen = strlen ($partialCode);
  	  # Get the end of the given block in the
  	  # given position.
  	  # While doing that, get others possible
  	  # blocks that should be inside it and
  	  # save them inside the a capsule too.
  	  # ---
  	  # Map the code string to start reading
  	  # blocks beggining in the code from the
  	  # given point in the code ($point)
  	    $expectedEnds = 1;
  	  for ($i = $point; $i < $codeLen; $i++) {

  	    $codeSlice = substr($partialCode, $i, strlen($block[0]));

  	    if ($codeSlice === $block [0]) {
  	      $expectedEnds++;
  	    }
  	    # Map the syntaxes inside the
  	    # blocksSyntaxes array in order
  	    # getting one of them being used
  	    # in the current position in the
  	    # code.
  	    /**
  	    foreach ($this->blocksSyntaxes as $syntax) {
  	      $codeSlice = substr($partialCode, $i, strlen($syntax[0]));

  	      if ($codeSlice === $syntax[0]) {

  	        #echo $codeSlice, " => ", $i, "\n\n";

  	        $blockBody = $this->getBlockEndInPartialCode ($partialCode, $syntax, $i + 1);

  	        if ($syntax [0] === $block [0]) {
  	          $expectedEnds++;
  	        }
  	        # echo "\n\n\n", $blockBody[0], "\n\n\n";
  	        $blockType = $syntax [2];
  	        $blockId = count( self::$blockStore );
  	        $syntaxName = "::={$blockType}-block{$blockId}::";

  	        array_push (self::$blockStore,
  	          $blockBody
  	        );

  	        $replacementCodeBody = substr (
  	          $partialCode,
  	          $blockBody ['start'],
  	          $blockBody ['end']
  	        );

  	        $replacement = $syntaxName;

  	        $partialCode = substr_replace (
  	          $partialCode,
  	          $replacementCodeBody . '-',
  	          $blockBody ['start'],
  	          $blockBody ['end']
  	        );
  	      }
  	    }
  	    */

  	    # A code slice that should
  	    # contain the block end
  	    $endSlice = substr($partialCode, $i, strlen($block[1]));
  	    # Verify if the block end
  	    # has been acieved
  	    if ($endSlice === $block[1]) {
  	      if ($expectedEnds <= 1) {
  	        $endPoint = ($i - $point) + strlen($block[1]) + 1;

  	        return [
  	          substr ($partialCode, $point - 1, $endPoint),
  	          'start' => $point - 1,
  	          'end' =>  $endPoint
  	        ];
  	      } else {
  	        $expectedEnds--;
  	      }
  	    }
  	  }
  	}

  	private function generateBlockId () {
  		static $idCounter = 1;

  		return join ('', [
  			rand (0, 999999999),
  			$idCounter++,
  			$idCounter * rand (222, 99999),
  			(int)time () * rand (111, 99999)
  		]);
  	}
  }}
}
