<?php

namespace parse;

class Expression
{
    /**
     * Transform the token tree to compute [literal expression](#)¹,
     * Solve and replace mathematic expression.
     * Try to transform until an expression can be reduced or an unary was
     * detected.
     *
     * Example: `6 + (-2 + 3) * 2 + (-5 * 2 / 4 - 2) - +5`
     *
     * @param $root
     *      The entry token to start to browse.
     */
    static public function parse(Token $root)
    {
        \Msg::debug("Reducing expressions...");
        self::reduce_expressions($root);
        \Msg::funcline(__function__, "unary+ope...");
        // Unary first, operation next.
        while (self::parse_unary($root) || self::parse_num_ope($root));
    }

    static private function reduce_expressions(Token $root)
    {
        do
        {
            $remains = false;
            $level = 0 ;
            $current = $root ;
            while ($current) {
                // \Msg::debug("");
                // \Msg::funcline(__function__, $current);
                // \Msg::funcline(__function__, $current->next);
                if (self::check_value_expression($current, $root)) {
                    $remains = true;
                }
                // \Msg::funcline(__function__, $current);
                Token::browse($current, $level) ;
            }
            // \Msg::funcline(__function__, "Remains expression ? ".\Util::yesno($remains));
        } while ($remains) ;
    }

    static private function parse_num_ope(Token $root)
    {
        # Checks if the sequence contains [number operator number].
        $matches = self::has_number_operation($root);
        if (!$matches) return false;

        # Sorts the matches using the following order to perform operation
        # in the right order (mul/div first, sub then, and add finally).

        $order = \Schema_Common::get_operators_precedence();
        usort($matches, function($a, $b) use ($order) {
            $ra = -1;
            $rb = -1;
            foreach ($order as $index => $order_opertors) {
                if (in_array($a['schema'][1]['token']->data, $order_opertors)) $ra = $index;
                if (in_array($b['schema'][1]['token']->data, $order_opertors)) $rb = $index;
            }
            if ($ra < 0) { throw new Exception("Unknown A operator (".$a['schema'][1]['token']->data.")"); }
            if ($rb < 0) { throw new Exception("Unknown B operator (".$b['schema'][1]['token']->data.")"); }

            return $ra <=> $rb;
        });

        // \Msg::debug("Pour l'expression, ".count($matches)." résultats");

        $result  = reset($matches);

        $current   = $result['schema'][0]['token'];
        $openode   = $result['schema'][1]['token'];
        $rightnode = $result['schema'][2]['token'];

        $left      = $current->data;
        $opesign   = $openode->data;
        $right     = $rightnode->data;

            if ($opesign == '+') { $outvalue = $left + $right; }
        elseif ($opesign == '-') { $outvalue = $left - $right; }
        elseif ($opesign == '*') { $outvalue = $left * $right; }
        elseif ($opesign == '/') { $outvalue = $left / $right; }
        elseif ($opesign == '%') { $outvalue = $left % $right; }
        elseif ($opesign == '^') { $outvalue = pow($left, $right); }
        else {
            \Msg::warn("not implemented operator '{$opesign}'.");
            return false ;
        }
        // \Msg::debug("Operation solved: '$left $opesign $right' = $outvalue.");

        /// Group tokens.
        /// On vient rattache le jeton après l'expression à l'élément en cours.
        /// From: <Current> -- <op> -- <Right> -- <Next|null>
        /// To:   <Current> -- <Next|null>
        $current->set_next($rightnode->next);

        /// Modifie la valeur avec le résultat.
        $current->data = $outvalue ;

        //____
        // \Msg::debug('Display tree');
        // Token::display_tree($root);

        // self::check_value_expression($current, $root);
        self::reduce_expressions($root);
        return true ;
    }

    static private function has_number_operation(Token $root)
    {
        $matches = [];
        $schema = \Lang::get_schema('Common','number_operation') ;
        $level = 0 ;
        $current = $root ;
        while ($current) {
            Token::skip_non_code($current);
            if (!$current) break;

            $result = Validator::validate_schema($current, $schema) ;
            if ($result !== false) {
                $matches[] = $result;
            }
            Token::browse($current, $level) ;
        }
        return $matches ;
    }

    /**
     * Checks if a single-value exist in parentheses.
     * `(6)` must be evaluated to `6`.
     *
     * @return  Returns if a item was removed or not.
     */
    static public function check_value_expression(Token $token, Token $root):bool
    {
        if (is_null($token->get_next_non_code(Token::DIR_RIGHT)) &&
            is_null($token->get_next_non_code(Token::DIR_LEFT)) &&
            $token->parent &&
            $token->parent->check(Token::TYPE_PUNCT, '(') &&
            $token->parent->next &&
            $token->parent->next->check(Token::TYPE_PUNCT, ')')
        ) {
            // \Msg::debug("possible match: $token");

            $parent = $token->parent;
            $anch_left = $parent->previous;
            $anch_right = $parent->next->next;

            if ($anch_left)
            {
                // \Msg::debug("-> remove method 1");
                // \Msg::debug("  -> left = $anch_left");
                // \Msg::debug("  -> right = $anch_right");
                // Token::display_tree($root);


                //# "something" // (anch_left)
                //# "("
                //#     "value" // (token)
                //# ")"
                //# "something" // (anch_right)

                // <Anchor Left> -(a)- <Token> -(b)- <Anchor Right>

                // Liaison (a)
                $anch_left->set_next($token);

                // Liaison (b)
                if ($anch_right) $anch_right->previous = $token;
                $token->next = $anch_right;

                // Update parent
                $token->parent = $anch_left->parent;

            }
            else
            {
                // "something"
                // "("
                //     "("
                //         "value"
                //     ")"
                //     "*"
                //     "value"
                // ")"
                // "something"

                // \Msg::debug("Tree -BEFORE- moving nodes");
                // Token::display_tree($root);

                $tpp = $token->parent->parent;
                $tpp->remove_first_child();
                $tpp->remove_first_child();
                $tpp->set_first_child($token);
            }

            // \Msg::debug("Tree after moving nodes");
            // Token::display_tree($root);
            return true ;
        }
        return false ;
    }

    /**
     * Vérification des opérateurs "unary".
     *
     * * Si token is [-|+] && token.previous == null && token.real_next is [number?]
     * * Si token is [-|+] && token.previous is operator
     */
    static private function parse_unary(Token $root)
    {
        // \Msg::funcline(__function__,__line__);
        $found = false ;
        $current = $root ;
        $level = 0;
        while ($current) {
            Token::skip_non_code($current);
            // \Msg::funcline(__function__,__line__);
            if (!$current) break;

            if ($current->check(Token::TYPE_PUNCT) && in_array($current->data, \Punct::get_unary()))
            {
                $previous = $current->get_next_non_code(\Node::DIR_LEFT);
                $next = $current->get_next_non_code(\Node::DIR_RIGHT);
                if ((is_null($previous) || $previous->type == Token::TYPE_ROOT) &&
                    $next->check(Token::TYPE_NUMBER))
                {
                    if ($current->data == '-') $next->data *= -1;
                    $current->type = Token::TYPE_WHITE;
                    $current->data = '*removed*';
                    continue;
                }
            }


            $schema1 = [
                ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_NUMBER],
                ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => ['²']],
            ];
            $result = Validator::validate_schema($current, $schema1) ;
            if ($result !== false) {
                $found = true;

                $number    = $result['schema'][0]['token'];
                $square  = $result['schema'][1]['token'];

                $number->data *= $number->data ;
                $number->set_next($square->next);
            }

            //--

            $schema1 = [
                'good'  => ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => \Punct::get_operators()],
                'unary' => ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_PUNCT, 'data' => \Punct::get_unary()],
                'numb'  => ['qty' => 1, 'optional' => 0, 'type' => Token::TYPE_NUMBER],
            ] ;

            $result = Validator::validate_schema($current, $schema1) ;
            if ($result !== false) {
                $found = true;

                $good_ope  = $result['schema']['good']['token'];
                $unary_ope = $result['schema']['unary']['token'];
                $number    = $result['schema']['numb']['token'];

                if ($unary_ope->data == '-') $number->data *= -1 ;
                $good_ope->set_next($number);
            }

            Token::browse($current, $level) ;
        }


        return $found ;
    }
}