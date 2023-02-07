<?php

namespace Tivins\Abstract;

interface IOutput
{
    public function write($type, string $message):string;
}
