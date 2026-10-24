<?php

$autoloaders = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../../vendor/autoload.php',
];

foreach ($autoloaders as $autoloader) {
    if (file_exists($autoloader)) {
        require $autoloader;

        return;
    }
}

fwrite(STDERR, "Unable to locate Composer's autoloader.\n");

exit(1);
