<?php

namespace parse;
use objmdl\Program;
use objmdl\OPackage;
use objmdl\OClass;
use objmdl\OMethod;
use objmdl\OEnum;

class Semantic
{
    public $root = null ;
    public $parser = null ;

    public function run()
    {
        $this->check_structure() ;
        $this->parse_instructions() ;
    }

    public function check_structure()
    {
        \Msg::debug(__class__.'::'.__function__) ;

        $browse_callbacks = [
            'down' => array($this, 'push_stack'),
            'up' => array($this, 'pop_stack')
            ] ;

        $prog = $this->parser->get_program() ;
        $scope_stack = $prog->get_scope_stack() ;

        $level = 0 ;
        $current = $this->root->next ;
        Token::skip_non_code($current) ;
        while ($current)
        {
            // Token::skip_non_code($current) ;

            $current_scope_type = $scope_stack->get_current_type() ;
            $current_scope_object = $scope_stack->get_current()->get_ref() ;

            \Msg::info("current_scope_type = $current_scope_type (".$scope_stack->get_depth().") | Token = $current") ;

            /**
             * ==============( PACKAGE )==============
             */
            if ($current_scope_type == "OPackage")
            {
                $result = Validator::validate_schema($current, Java_Schema::package_validation_schema()) ;
                if ($result !== false)
                {
                    $name_idx = 1 ;
                    $name_token = $result['schema'][$name_idx]['token'] ;
                    $name_token->type = Token::TYPE_SYMBOL_NAME ;

                    $package = $current_scope_object->get_package($name_token->data) ;
                    if (!$package)
                    {
                        $package = new OPackage($current, $current_scope_object) ;
                        $package->set_name($name_token->data) ;
                        $prog->add_symbol($package) ;

                        $current->prog_ref = $package ;
                        $current_scope_object->add_package($package) ;
                    }

                    Validator::apply_object_model($result['schema'], $package) ;
                    \Msg::info("Package found : " . $name_token) ;
                    Token::browse($current, $level, $browse_callbacks) ;
                    continue ;
                }
            }

            /**
             * ==============( CLASS )==============
             */
            if ($current_scope_type == "OPackage")
            {
                $result = Validator::validate_schema($current, Java_Schema::class_validation_schema()) ;
                if ($result !== false) {

                    $name_idx = 2 ;
                    $name_token = $result['schema'][$name_idx]['token'] ;
                    $name_token->type = Token::TYPE_SYMBOL_NAME ;

                    $type_class = $result['schema'][1]['token']->data ;
                    $access = isset($result['schema'][0]['token']) ? $result['schema'][0]['token']->data : 'internal' ;

                    $class = new OClass($current, $current_scope_object) ;
                    $class->set_name($name_token->data) ;
                    $class->set_class_type($type_class) ;
                    $class->set_access($access) ;

                    $current->prog_ref = $class ;
                    $prog->add_symbol($class) ;
                    $current_scope_object->add_class($class) ;
                    Validator::apply_object_model($result['schema'], $class) ;
                    // $scope_stack->push_new_scope($class) ;

                    \Msg::info("Class found : ". $name_token) ;
                    Token::browse($current, $level, $browse_callbacks) ;
                    continue ;
                }
            }

            /**
             * ==============( ENUM )==============
             */
            if ($current_scope_type == "OClass")
            {
                $result = Validator::validate_schema($current, Java_Schema::enum_validation_schema()) ;
                if ($result !== false) {

                    $name_idx = 2 ;
                    $name_token = $result['schema'][$name_idx]['token'] ;
                    $name_token->type = Token::TYPE_SYMBOL_NAME ;

                    $access = isset($result['schema'][0]['token']) ? $result['schema'][0]['token']->data : 'private' ;

                    $enum = new OEnum($current, $current_scope_object) ;
                    $enum->name = $name_token->data ;
                    $enum->set_access($access) ;

                    $current->prog_ref = $enum ;
                    $prog->add_symbol($enum) ;

                    $current_scope_object->add_enum($enum) ;
                    Validator::apply_object_model($result['schema'], $enum) ;
                    // $scope_stack->push_new_scope($enum) ;

                    \Msg::info("Enum found : " . $name_token) ;
                    Token::browse($current, $level, $browse_callbacks) ;
                    continue ;
                }
            }

            /**
             * ==============( METHOD )==============
             */
            if ($current_scope_type == "OClass")
            {
                $result = Validator::validate_schema($current, Java_Schema::method_validation_schema()) ;
                if ($result !== false)
                {
                    $name_idx = 3 ;
                    $name_token = $result['schema'][$name_idx]['token'] ;
                    $name_token->type = Token::TYPE_SYMBOL_NAME ;

                    $access = isset($result['schema'][1]['token']) ? $result['schema'][1]['token']->data : 'private' ;
                    $is_const = isset($result['schema'][6]['token']) ;
                    $return = $result['schema'][2]['token'] ;

                    $method = new OMethod($current, $current_scope_object) ;
                    $method->set_name($name_token->data) ;
                    $method->set_access($access) ;
                    $method->set_const($is_const) ;
                    $method->set_return($return) ;

                    $current->prog_ref = $method ;
                    $prog->add_symbol($method) ;
                    $current_scope_object->add_method($method) ;
                    Validator::apply_object_model($result['schema'], $method) ;

                     \Msg::info("Method found : " . $name_token) ;
                    Token::browse($current, $level, $browse_callbacks) ;
                    continue ;
                }
            }

            /**
             * ==============( PROPERTY )==============
             */
            if ($current_scope_type == "OClass")
            {
                $result = Validator::validate_schema($current, Java_Schema::property_validation_schema()) ;
                if ($result !== false)
                {
                    $name_idx = 4 ;
                    $name_token = $result['schema'][$name_idx]['token'] ;
                    $name_token->type = Token::TYPE_SYMBOL_NAME ;

                    $access = isset($result['schema'][1]['token']) ? $result['schema'][1]['token']->data : 'private' ;

                    $property = new OProperty($current, $current_scope_object) ;
                    $property->set_name($name_token->data) ;
                    $property->schema = $result['schema'] ;
                    $property->set_access($access) ;

                    $current->prog_ref = $property ;
                    $prog->add_symbol($property) ;
                    $current_scope_object->add_property($property) ;
                    Validator::apply_object_model($result['schema'], $property) ;

                    \Msg::info("Property found : $name_token") ;
                    Token::browse($current, $level, $browse_callbacks) ;
                    continue ;
                }
            }

            if (is_subclass_of($current_scope_type, 'Instruction'))
            {
                /**
                 * Identifying CALL FUNC.
                 */
                $result = Validator::validate_schema($current, CodeVal::call_func()) ;
                if ($result !== false) {
                    $code = new Call_Func() ;
                    $current->prog_ref = $code ;
                    Validator::apply_object_model($result['schema'], $code) ;
                    $current_scope_object->add_code($code) ;
                    \Msg::info("Call_Func found : " . $code) ;
                    Token::browse($current, $level, $browse_callbacks) ;
                    continue ;
                }

                /**
                 * Identifying FOR LOOP.
                 */
                $result = Validator::validate_schema($current, CodeVal::cond_block('for')) ;
                if ($result !== false) {
                    $code = new For_Loop() ;
                    $current->prog_ref = $code ;
                    Validator::apply_object_model($result['schema'], $code) ;
                    $current_scope_object->add_code($code) ;
                    \Msg::info("For_Loop found : " . $code) ;
                    Token::browse($current, $level, $browse_callbacks) ;
                    continue ;
                }

                /**
                 * Identifying WHILE LOOP.
                 */
                $result = Validator::validate_schema($current, CodeVal::cond_block('while')) ;
                if ($result !== false) {
                    $code = new While_Loop() ;
                    $current->prog_ref = $code ;
                    Validator::apply_object_model($result['schema'], $code) ;
                    $current_scope_object->add_code($code) ;
                    \Msg::info("While_Loop found : " . $code) ;
                    Token::browse($current, $level, $browse_callbacks) ;
                    continue ;
                }

                /**
                 * Identifying IF CONDITION.
                 */
                $result = Validator::validate_schema($current, CodeVal::cond_block('if')) ;
                if ($result !== false) {
                    $code = new If_Cond() ;
                    $current->prog_ref = $code ;
                    Validator::apply_object_model($result['schema'], $code) ;
                    $current_scope_object->add_code($code) ;
                    \Msg::info("If_Cond found : " . $code) ;
                    Token::browse($current, $level, $browse_callbacks) ;
                    continue ;
                }
            }


            if ($current->type == Token::TYPE_WHITE) {
            }
            elseif ($current->type == Token::TYPE_COMMENT) {
            }
            elseif ($current->prog_ref) {
            }
            else
            {
                $part = 'body' ;
                if ($current->parent && $current->parent->data == '(')
                    $part = 'head' ;

                \Msg::info("Checking for Add instruction : parent? " . $current->parent) ;
                if ($current->parent && is_subclass_of($current->parent->prog_ref, 'Instruction'))
                {
                    \Msg::info("Add instruction") ;
                    $code = new Instruction() ;
                    $code->set_name($current->data) ;
                    $code->set_root_token($current) ;

                    $current->prog_ref = $code ;
                    $current->parent->prog_ref->add_code($code, $part) ;
                }
            }
            Token::browse($current, $level, $browse_callbacks) ;
        }
    }

    public function push_stack($current_save, $current)
    {
        if (is_null($current_save->prog_ref)) return ;
        $prog = $this->parser->get_program() ;
        $scope_stack = $prog->get_scope_stack() ;
        $scope_stack->push_new_scope($current_save->prog_ref) ;
    }

    public function pop_stack($current_save, $current)
    {
        if (is_null($current_save->prog_ref)) return ;
        // if (!in_array(get_class($current_save->prog_ref), ['OPackage','OClass','OMethod'])) return ;
        $prog = $this->parser->get_program() ;
        $scope_stack = $prog->get_scope_stack() ;
        $scope_stack->pop($current_save->prog_ref) ;
    }

    public function parse_instructions()
    {
        $prog = $this->parser->get_program() ;
        $symbols = $prog->get_symbols_names() ;

        foreach ($symbols as $symbol_name) {
            $symbol = $prog->get_symbol($symbol_name) ;
            if ($symbol && get_class($symbol) == 'OEnum') {
                $this->parse_enum($symbol) ;
            }
        }
    }
    public function parse_enum(OEnum $symbol)
    {
        if ($symbol->get_is_parsed()) return ;

        echo __function__."()\n";
        $codes = $symbol->get_codes() ;
        if (!isset($codes['body'])) return ;
        $body = $codes['body'] ;

        $current = ['word' => null, 'value' => null] ;
        $num_instr = count($body) ;
        for ($i=0;$i<$num_instr;$i++)
        {
            $instruction = $body[$i] ;
            $token = $instruction->get_root_token() ;
            if ($token->check(Token::TYPE_WORD)) {
                $current['word'] = $token->data ;
            }
            if ($token->check(Token::TYPE_PUNCT,'=')) {
                $current['value'] = $body[$i+1]->get_root_token()->data ;
            }
            if ($token->check(Token::TYPE_PUNCT,',') || ($i+1) == $num_instr) {
                $entry = $symbol->add_item($current['word'],$current['value']);
                $this->parser->get_program()->add_symbol($entry) ;
                $current['word'] = null ;
                $current['value'] = null ;
            }
        }

        $symbol->set_parsed() ;
    }
}