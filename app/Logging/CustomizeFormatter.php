<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    /**
     * Customize the given Monolog logger instance.
     */
    public function __invoke($logger)
    {
        $logger->pushProcessor(new \App\Logging\ClassMetadataProcessor());

        foreach ($logger->getHandlers() as $handler) {
            // We add %extra.ip% and %extra.agent% to the format
            $format = "[%datetime%] %level_name% [%extra.class%] [%extra.ip%] [%extra.agent%]: %message% | %context%\n";

            $handler->setFormatter(new LineFormatter(
                $format,
                "Y-m-d H:i:s",
                true,
                true
            ));
        }
    }
}
