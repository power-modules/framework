<?php

declare(strict_types=1);

namespace Modular\Framework\Config;

use Modular\Framework\Config\Contract\HasConfig;
use Modular\Framework\Config\Contract\PowerModuleConfig;
use Modular\Framework\Config\Exception\ConfigNotFoundException;
use Modular\Framework\Config\Exception\InvalidConfigException;
use Throwable;

class Loader
{
    public function __construct(
        private string $configDirectory,
    ) {
    }

    public function getConfig(HasConfig $hasConfig): PowerModuleConfig
    {
        $defaultConfig = $hasConfig->getConfig();

        try {
            $configPath = $this->getConfigFilepath($defaultConfig->getConfigFilename());

            return $this->getCustomConfig($configPath);
        } catch (ConfigNotFoundException $exception) {
            return $defaultConfig;
        } catch (InvalidConfigException $exception) {
            throw new InvalidConfigException($defaultConfig->getConfigFilename());
        } catch (Throwable $exception) {
            throw new \RuntimeException(
                sprintf(
                    'An error occurred while loading the configuration file "%s": %s',
                    $defaultConfig->getConfigFilename(),
                    $exception->getMessage(),
                ),
                0,
                $exception,
            );
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function getCustomConfig(string $configPath): PowerModuleConfig
    {
        $config = require $configPath;

        if (!$config instanceof PowerModuleConfig) {
            throw new InvalidConfigException($configPath);
        }

        return $config;
    }

    /**
     * @throws ConfigNotFoundException
     */
    private function getConfigFilepath(string $configName): string
    {
        $path = sprintf(
            '%s/%s.php',
            $this->configDirectory,
            str_replace('..', '', $configName),
        );

        $realpath = realpath($path);

        if ($realpath === false) {
            throw new ConfigNotFoundException(str_replace('//', '/', $path));
        }

        return $realpath;
    }
}
