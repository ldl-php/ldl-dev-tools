<?php declare(strict_types=1);

namespace LDL\DevTools\Helper;

abstract class ConsoleInputHelper
{

    public static function readInput(string $prompt=null) : string
    {
        if(null !== $prompt) {
            echo $prompt;
        }
        $fp = fopen('php://stdin', 'rb');
        $input = trim(fgets($fp));
        fclose($fp);

        return $input;
    }

}
