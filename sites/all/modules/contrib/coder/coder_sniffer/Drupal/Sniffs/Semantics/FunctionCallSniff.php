<?php
/**
 * Drupal_Sniffs_Semantics_FunctionCallSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Helper class to sniff for specific function calls.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class Drupal_Sniffs_Semantics_FunctionCallSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Collection of objects that get notified during processing. This array is keyed
     * by the function names the listeners are interested in.
     *
     * @var array
     */
    protected static $listeners = array();

    /**
     * The currently processed file.
     *
     * @var PHP_CodeSniffer_File
     */
    protected $phpcsFile;

    /**
     * The token position of the function call.
     *
     * @var int
     */
    protected $functionCall;

    /**
     * The token position of the opening bracket of the function call.
     *
     * @var int
     */
    protected $openBracket;

    /**
     * The token position of the closing bracket of the function call.
     *
     * @var int
     */
    protected $closeBracket;

    /**
     * Internal cache to save the calculated arguments of the function call.
     *
     * @var array
     */
    protected $arguments;


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens       = $phpcsFile->getTokens();
        $functionName = $tokens[$stackPtr]['content'];
        if (isset(self::$listeners[$functionName]) === false) {
            // No listener is interested in this function name, so return early.
            return;
        }

        // Find the next non-empty token.
        $openBracket = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);

        if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
            // Not a function call.
            return;
        }

        if (isset($tokens[$openBracket]['parenthesis_closer']) === false) {
            // Not a function call.
            return;
        }

        // Find the previous non-empty token.
        $search   = PHP_CodeSniffer_Tokens::$emptyTokens;
        $search[] = T_BITWISE_AND;
        $previous = $phpcsFile->findPrevious($search, ($stackPtr - 1), null, true);
        if ($tokens[$previous]['code'] === T_FUNCTION) {
            // It's a function definition, not a function call.
            return;
        }

        if ($tokens[$previous]['code'] === T_OBJECT_OPERATOR) {
            // It's a method invocation, not a function call.
            return;
        }

        if ($tokens[$previous]['code'] === T_DOUBLE_COLON) {
            // It's a static method invocation, not a function call.
            return;
        }

        $this->phpcsFile    = $phpcsFile;
        $this->functionCall = $stackPtr;
        $this->openBracket  = $openBracket;
        $this->closeBracket = $tokens[$openBracket]['parenthesis_closer'];
        $this->arguments    = array();

        foreach (self::$listeners[$functionName] as $listener) {
            $listener->processFunctionCall($phpcsFile, $stackPtr, $openBracket, $this->closeBracket, $this);
        }

    }//end process()


    /**
     * Returns start and end token for a given argument number.
     *
     * @param int $number Indicates which argument should be examined, starting with
     *                    1 for the first argument.
     *
     * @return array(string => int)
     */
    public function getArgument($number)
    {
        // Check if we already calculated the tokens for this argument.
        if (isset($this->arguments[$number]) === true) {
            return $this->arguments[$number];
        }

        $tokens = $this->phpcsFile->getTokens();
        // Start token of the first argument.
        $start = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($this->openBracket + 1), null, true);
        if ($start === $this->closeBracket) {
            // Function call has no arguments, so return false.
            return false;
        }

        // End token of the last argument.
        $end = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($this->closeBracket - 1), null, true);
        $lastArgEnd    = $end;
        $nextSeperator = $this->openBracket;
        $counter       = 1;
        while (($nextSeperator = $this->phpcsFile->findNext(T_COMMA, ($nextSeperator + 1), $this->closeBracket)) !== false) {
            // Make sure the comma belongs directly to this function call,
            // and is not inside a nested function call or array.
            $brackets    = $tokens[$nextSeperator]['nested_parenthesis'];
            $lastBracket = array_pop($brackets);
            if ($lastBracket !== $this->closeBracket) {
                continue;
            }

            // Update the end token of the current argument.
            $end = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextSeperator - 1), null, true);
            // Save the calculated findings for the current argument.
            $this->arguments[$counter] = array(
                                          'start' => $start,
                                          'end'   => $end,
                                         );
            if ($counter === $number) {
                break;
            }

            $counter++;
            $start = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextSeperator + 1), null, true);
            $end   = $lastArgEnd;
        }//end while

        // If the counter did not reach the passed number something is wrong.
        if ($counter !== $number) {
            return false;
        }

        $this->arguments[$counter] = array(
                                      'start' => $start,
                                      'end'   => $end,
                                     );
        return $this->arguments[$counter];

    }//end getArgument()


    /**
     * Registers a listener object so that it will be called during processing.
     *
     * @param Drupal_Sniffs_Semantics_FunctionCall $listener
     *   The listener object that should be notified.
     *
     * @return void
     */
    public static function registerListener($listener)
    {
        $funtionNames = $listener->registerFunctionNames();
        foreach ($funtionNames as $name) {
            self::$listeners[$name][] = $listener;
        }

    }//end registerListener()


}//end class

?>
