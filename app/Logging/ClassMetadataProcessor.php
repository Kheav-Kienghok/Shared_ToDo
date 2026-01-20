<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class ClassMetadataProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $originClass = 'Unknown';

        foreach ($stack as $trace) {
            if (isset($trace['class'])) {
                $class = $trace['class'];

                if (
                    str_contains($class, 'Monolog\\') ||
                    str_contains($class, 'Illuminate\\Log') ||
                    str_contains($class, 'Illuminate\\Support\\Facades') ||
                    $class === self::class
                ) {
                    continue;
                }

                $originClass = class_basename($class);
                break;
            }
        }

        // Add Metadata to the 'extra' array
        $record->extra['class'] = $originClass;
        $record->extra['ip'] = request()->ip() ?? 'N/A';
        $record->extra['agent'] = request()->userAgent() ?? 'N/A';

        return $record;
    }
}
