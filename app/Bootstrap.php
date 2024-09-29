<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;


class Bootstrap
{
	public static function boot(): Configurator
	{
        $configurator = new Configurator;
        $rootDir = dirname(__DIR__);

        $logDir = $rootDir . '/log';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $configurator->enableTracy($logDir);

        $tempDir = $rootDir . '/temp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $configurator->setTempDirectory($tempDir);

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();

        // Načítajte konfigurácie z NEON súborov
        $configurator->addConfig($rootDir . '/config/common.neon');
        $configurator->addConfig($rootDir . '/config/services.neon');

        return $configurator;
	}
}
