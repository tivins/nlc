<?php

namespace parse;

/**
 * The `Walker` object is a data structure used to hold an internal position
 * pointer into a text during the tokens analysis.
 *
 * Multibyte string.
 */
class Walker
{
    /// The internal pointer inside the source.
    private int $idx = 0 ;

    /// The line number where the current character is located in.
    /// NB: Lines starts from 1.
    /// @see Walker::get_line().
    private int $line = 1 ;

    /// The number of characters between the begining of the line.
    /// NB: Columns starts from 1.
    /// @see Walker::get_column().
    private int $coln = 1 ;

    /// The full source string to traverse.
    private string $source = '' ;

    /// The number of characters of the source.
    /// @see Walker::get_length().
    private int $src_len = 0 ;

    /**
     * Creates a new Walker object with given source.
     */
    public function __construct(string $source)
    {
        $this->source = $source ;
        $this->src_len = mb_strlen($this->source) ;
    }

    /**
     * Gets the number of multibyte-characters of the source.
     *
     * @return  Returns an integer or false if the encoding is invalid.
     */
    public function get_length() : int {
        return $this->src_len ;
    }

    /**
     * Gets the current line number of the internal pointer.
     *
     * @return  Returns an integer greater or equals to `1`.
     */
    public function get_line() : int {
        return $this->line;
    }

    /**
     * Gets the current column number of the internal pointer.
     *
     * @return Returns an integer greater or equals to `1`.
     */
    public function get_column() : int {
        return $this->coln;
    }

    /**
     * Gets the current character from the source.
     *
     * @return  Returns an integer (multibyte character)
     *          or false if the encoding is invalid.
     */
    public function get_char()
    {
        return mb_substr($this->source, $this->idx, 1) ;
    }

    /**
     * Peek a multibyte character relative to the current position of the
     * internal pointer.
     */
    public function get_relchar(int $relative = 0)
    {
        $index = $this->idx + $relative ;
        return mb_substr($this->source, $index, 1) ;
    }

    /**
     * Advance the internal pointer of one multibyte-character, and update
     */
    public function advance() : void
    {
        $char = $this->get_char() ;
        $this->coln++ ;
        if ($char == "\n") {
            $this->coln = 1 ;
            $this->line += 1 ;
        }
        $this->idx++ ;
    }

    /**
     * Checks if the current internal pointer is "inside" the source.
     *
     * @return  Returns true if the internal pointer is greater or equals to zero,
     *          or false if it's lower than the source length.
     *
     * @const   const
     */
    public function is_inside() : bool
    {
        return $this->idx >= 0 && $this->idx < $this->src_len ;
    }
}

