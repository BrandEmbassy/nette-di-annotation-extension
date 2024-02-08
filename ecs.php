<?php declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

$defaultEcsConfigurationSetup = require 'vendor/brandembassy/coding-standard/default-ecs.php';

return static function (ECSConfig $ecsConfig) use ($defaultEcsConfigurationSetup): void {
    $defaultSkipList = $defaultEcsConfigurationSetup($ecsConfig, __DIR__);

    $ecsConfig->cacheDirectory('var/ecs');
    $ecsConfig->paths([
        'src',
        'tests',
    ]);

    $ecsConfig->skip(array_merge($defaultSkipList, ['tests/temp']));
};
