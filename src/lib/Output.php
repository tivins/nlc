<?php

class Output_HTML implements IOutput
{
    public function write($type, string $message):string
    {
        return '<span class="'. \Tivins\Abstract\Util::html($type).'">' . \Tivins\Abstract\Util::html($message) . '</span>' ;
    }
}
