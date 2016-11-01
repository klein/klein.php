<?php
/**
 * Klein (klein.php) - A fast & flexible router for PHP
 *
 * @author      Chris O'Hara <cohara87@gmail.com>
 * @author      Trevor Suarez (Rican7) (contributor and v2 refactorer)
 * @copyright   (c) Chris O'Hara
 * @link        https://github.com/klein/klein.php
 * @license     MIT
 */

namespace Klein\Exceptions;

use RuntimeException;

/**
 * DispatchHaltedException
 *
 * Exception used to halt a route callback from executing in a dispatch loop
 */
class DispatchHaltedException extends RuntimeException implements KleinExceptionInterface
{

    /**
     * Constants
     */

    /**
     * Skip this current match/callback
     *
     * @type int
     */
    const SKIP_THIS = 1;

    /**
     * Skip the next match/callback
     *
     * @type int
     */
    const SKIP_NEXT = 2;

    /**
     * Skip the rest of the matches
     *
     * @type int
     */
    const SKIP_REMAINING = 0;


    /**
     * Properties
     */

    /**
     * The number of next matches to skip on a "next" skip
     *
     * @type int
     */
    protected $number_of_skips = 1;


    /**
     * Methods
     */

    /**
     * Gets the number of matches to skip on a "next" skip
     *
     * @return int
     */
    public function getNumberOfSkips()
    {
        return $this->number_of_skips;
    }

    /**
     * Sets the number of matches to skip on a "next" skip
     *
     * @param int $number_of_skips
     * @return DispatchHaltedException
     */
    public function setNumberOfSkips($number_of_skips)
    {
        $this->number_of_skips = (int) $number_of_skips;

        return $this;
    }
}
