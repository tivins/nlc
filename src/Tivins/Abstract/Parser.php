<?php

namespace Tivins\Abstract;

use Tivins\ObjectModel\Program;
use Tivins\parse\Source;
use Tivins\parse\Token;

class Parser
{
    private $root = null;
    private $prog = null;

    public function __construct()
    {
        $this->root = new Token(['type' => Token::TYPE_ROOT]);
        $this->prog = new Program($this->root);
    }

    public function get_program()
    {
        return $this->prog;
    }

    public function add_source(Source $source)
    {
        // On ajoute les jetons de la source à la fin de notre arbre de jetons
        // en cours.
        $last = $this->root->get_last();

        // On passe le jeton "root",
        $first           = $source->root->next;
        $first->previous = $last;
        $last->next      = $first;
        while (true) {
            if (!$last->next) break;
            $last = $last->next;
        }
    }

    /**
     * @deprecated Use `add_source()` instead.
     */
    public function parse(array $sources)
    {
        /**
         * 1) tokenize sources
         */
        foreach ($sources as $source) {
            $source->parse($this);
        }

        /**
         * 2) aggregate tokens in one tree
         */
    }
}