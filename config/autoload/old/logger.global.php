<?php

use rollun\logger\Logger;
use rollun\logger\LoggerFactory;
use \rollun\logger\LogWriter\FileLogWriter;
use \rollun\logger\LogWriter\FileLogWriterFactory;
use \rollun\installer\Command;
use \rollun\logger\Installer as LoggerInstaller;

return [
    'services' => [
        'factories' => [
            FileLogWriter::class => FileLogWriterFactory::class,
            Logger::class => LoggerFactory::class,
        ],
        'aliases' => [
            \rollun\logger\LogWriter\LogWriterInterface::DEFAULT_LOG_WRITER_SERVICE => FileLogWriter::class,
        ]
    ]
];
