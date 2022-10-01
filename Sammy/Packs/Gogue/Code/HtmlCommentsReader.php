<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Gogue\Code
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
namespace Sammy\Packs\Gogue\Code {
  /**
   * Make sure the module base internal class is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!class_exists ('Sammy\Packs\Gogue\Code\HtmlCommentsReader')) {
  /**
   * @class HtmlCommentsReader
   * Base internal class for the
   * Gogue\Code module.
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
  class HtmlCommentsReader {
    use HtmlCommentsReader\Base;

    /**
     * @method  string encodeStringsAndComments
     * @param  string $code
     * @return string
     */
    public function encodeComments ($code = '', array $commandSyntaxesCodeList = []) {
      if (!(is_string ($code) && $code)) {
        return '';
      }

      /**
       * - Comments Syntaxes
       * - php supported comments
       * - syntaxes
       */
      $commentsSyntaxes = [];
      $i = 0;
      $commentsSyntaxesList = self::commentSyntaxes ();

      foreach ($commandSyntaxesCodeList as $syntaxCode) {
        /**
         * 0 => /*
         * 1 => #
         * 2 => //
         */
        if (in_array ($syntaxCode, range (0, 2))) {
          array_push ($commentsSyntaxes, $commentsSyntaxesList [$syntaxCode]);
        }
      }

      if (count ($commentsSyntaxes) <= 0) {
        $commentsSyntaxes = $commentsSyntaxesList;
      }

      for ( ; $i < strlen ($code); $i++ ) {

        foreach ($commentsSyntaxes as $index => $syntax) {
          #$len = isset($synt[0]) && is_int($synt[0]) ? $synt[0] : 1;
          $slice = substr ($code, $i, strlen ($syntax ['init']));

          if ($slice === $syntax ['init']) {

            # Go to the end of the comment
            # and encode it
            $codeLen = strlen ($code);

            for ($e = ($i + 1); $e < $codeLen; $e++) {
              $len = strlen ($syntax ['end']);
              # Finding the end encode the
              # current comment and keep to
              # the next step for encoding
              # whole found inside the current
              # code.
              $endSlice = substr ($code, $e, $len);

              if ($endSlice === $syntax ['end']) {
                $commentEndPoint = ($e - $i) + $len;
                $commentSlice = substr ($code, $i, $commentEndPoint);
                $commentReplacer = str_repeat (' ', strlen ($commentSlice));

                $code = substr_replace ($code, $commentReplacer, $i, $commentEndPoint);

                break;
              }
            }
          }
        }

        foreach (self::$stringDelimiters as $stringDelimiter) {

          if (substr ($code, $i, strlen ($stringDelimiter [0])) == $stringDelimiter [0]) {

            $str_end = 0;

            for ($e = $i + 1; $e < strlen ($code); $e++) {

              $str_end++;

              if (substr ($code, $e, strlen ($stringDelimiter [1])) == $stringDelimiter [1]) {

                $str_body = substr ($code, $i, $str_end + 1);
                $str_index = count ($this->Store);

                $code = str_replace ($str_body, '::$' . $str_index . ':', $code);

                array_push ($this->Store, $str_body);

                break;
              }
            }
          }
        }
      }

      return $this->decodeStrings ($code);
    }

    /**
     * @method string descriptiondecodeStrings
     *
     * @param  string $str
     *
     * @return string
     */
    public function decodeStrings ($str = null) {
      if (!is_string ($str)) {
        return;
      }

      $encodedStringRe = '/:{2,}\$([0-9]+):/';

      $str = preg_replace_callback ($encodedStringRe, [$this, 'replaceStr'], $str);

      return $str;
    }

    private function replaceStr (array $stringMatch) {
      if (isset ($this->Store [$stringMatch [1]])) {
        return $this->Store [$stringMatch [1]];
      }
    }
  }}
}
