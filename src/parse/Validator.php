<?php
namespace parse;
/**
 * Validateur de schémas.
 */
class Validator
{
    /**
     * Checks if the given schema match to the given token tree.
     *
     * @param start_token
     *      The first token from which to start to check.
     *
     * @param schema
     *      The schema to validate. The schema is an array (which could be an
     *      associative array, and/or multidimensional array). Each items are
     *      associatives array with the possible following keys:
     *
     *      - qty (integer): The number of element to search.
     *      - optional (integer):
     *      - type (const | "schema"), ex: `Token::TYPE_NUMBER`
     *      - subtype (const), ex: `Token::SUBTYPE_INTEGER`
     *      - data (array values) or another schema if "type" is "schema".
     *
     * @return mixed: (bool) false OR (array) results details.
     *
     * @todo Change return `false` to `null` and  return type to `:?array`.
     *
     * @see class `Schema_Java`, class `Schema_Common`.
     */
    static public function validate_schema(Token $start_token, array $schema)
    {
        /**
         * Nous allons stocker dans ce tableau, les résultats
         * qui nous trouverons, réussis ou non.
         */
        $new_schema = [] ;

        /**
         * Jeton itérateur.
         * Nous traversons les jetons linéairement, c'est-à-dire,
         * de frère en frère. C'est la source qui se charge de
         * gérer le déplacement en profondeurs dans les jetons.
         */
        $current = $start_token ;

        /**
         * Récupération de la dernière clé pour stopper la boucle.
         */
        $last_key = \Util::get_last_key($schema);

        /**
         * Nous allons parcourir l'ensemble des éléments à valider, sortir si
         * il n'y a plus de jetons, ou si une des conditions requise n'est pas
         * validée.
         *
         * Si une condition optionnelle n'est pas trouvée, nous n'avançons pas
         * le jeton iterateur. En revanche, si nous validons un élement, nous
         * passons au jeton suivant.
         */
        foreach ($schema as $key => $iter)
        {
            if (!$current) { return false ; }
            /*^
                [todo]
                Si nous n'avons plus de jetons à parser c'est que... humm.
                Si. Il reste un espoir. Vérifier si les éléments du schéma
                sont optionnels !
            */

            Token::skip_non_code($current) ;
            if (!$current) { return false ; }
            /*^
                [todo]
                Same as above...
            */

            if ($current->prog_ref != null)
            /*^
                Si nous sommes déjà passé dessus,
                et que nous avons déjà analysé ce jeton
                comme étant associé à un objet de
                programmation, on passe.
            */
            {
                return false ;
            }

            // Msg::debug("- Testing schema[$key]...") ;
            // Msg::debug("Current token ID is {$current->id}, type of ".$current->type.' ('.Token::type_name($current->type)
            //     ."), data(".\Util::inline_str($current->data)." | ".gettype($current->data).")") ;


            $optional = isset($iter['optional']) && $iter['optional'] ;
            $qty = $iter['qty'] ;
            $type = $iter['type'] ;
            $data = isset($iter['data']) ? $iter['data'] : [] ;
            $subtype = isset($iter['subtype']) ? $iter['subtype'] : 0 ;
            $block_name = isset($iter['block_name']) ? $iter['block_name'] : "" ;


            // Msg::debug("Requirement are : type should be $type(".Token::type_name($type).") (".($optional?"optional":"required").").");
            if ($type === 'schema')
            {
                $sub_res = self::validate_schema($current, $data) ;
                if ($sub_res !== false)
                {
                    // OK !
                    $new_schema[$key]['result'] = $sub_res ;
                    $current = $sub_res['next_token'] ;
                    continue ;
                }
                else
                {
                    if ($optional)
                    {
                        // No worries. It's optional !
                        $new_schema[$key]['result'] = false ;
                        continue;
                    }
                    else
                    {
                        // Validation failed.
                        // Msg::debug("failed") ;
                        return false ;
                    }
                }
            }
            else // Tous les autres types (Token::Type)
            {
                $checks = null ;
                if ($current->check($type))
                {
                    $checks = true ;
                    if (!empty($data))
                    {
                        $checks = null ;
                        // Msg::debug("data have to match with one of : " . implode(', ', $data)) ;
                        foreach ($data as $data_item)
                        {
                            // Msg::debug(" - [{$current->type}/$type] $data_item: " . gettype($data_item).' vs '.gettype($current->data).' -- '.var_export($current->data,1)) ;
                            if ($current->check($type, $data_item))
                            {
                                // Msg::debug("match");
                                $checks = $data_item ;
                            }else{
                                // Msg::debug("dont match");
                            }
                        }
                    }
                }
                if (!is_null($checks))
                {
                    // OK
                    // Msg::debug("Match !") ;
                    $new_schema[$key]['token'] = $current ;
                    $new_schema[$key]['checks'] = $checks ;
                }
                else
                {
                    // Log::debug("No matches...") ;
                    if ($optional)
                    {
                        // No worries, it's OK !
                        $new_schema[$key]['result'] = false ;
                        continue;
                    }
                    else
                    {
                        // Validation failed.
                        // Log::debug("failed") ;
                        return false ;
                    }
                }
            }
            // if ($key < count($schema) - 1)
            if ($key != $last_key)
            {
                $current = $current->next ;
            }
        }

        if ($current->check(Token::TYPE_PUNCT, '}'))
        {
            /*^
                Si nous avons une fermeture de bloc, il faut revenir à
                l'ouverture pour que la source puisse descendre dans
                l'arborescence si nécessaire.

            */
            $current = $current->previous ;
        }

        $results = [
            'match' => false,
            'next_token' => $current,
            'schema' => $new_schema,
        ] ;


        return $results ;
    }

    /*
     * /!\ DELETE BECAUSE OF THIS METHODS WAS NOT USED ANYMORE.
     *
     *  static public function get_first_defined_token($schema) {
     *      foreach ($schema as $item) {
     *          if (isset($item['token']) && $item['token']) {
     *              return $item['token']  ;
     *          }
     *      }
     *  }
     */

    /**
     * Apply the given object to all tokens defined in the given schema.
     *
     * @param schema
     *      A validation schema returned by `Validator::validate_schema()`.
     *
     * @param object
     *      An object (or a descendent) to to apply to all tokens in schema.
     *
     * @return void
     */
    static public function apply_object_model(array &$schema, \objmdl\OObject $object)
    {
        foreach ($schema as $item) {
            if (isset($item['token']) && $item['token']) {
                $item['token']->prog_ref = $object;
            }
        }
    }
}
