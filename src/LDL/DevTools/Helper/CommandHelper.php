<?php

declare(strict_types=1);

namespace LDL\DevTools\Helper;

class CommandHelper
{
    public static function run(string $command)
    {
        exec("{$command} 2>&1 >/dev/null", $output, $resultCode);

        return (object) [
            'failed' => $resultCode > 0 ? true : false,
            'error' => @$output[0]
        ];
    }
}
