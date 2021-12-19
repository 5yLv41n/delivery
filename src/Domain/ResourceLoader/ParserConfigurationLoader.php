<?php

namespace App\Domain\ResourceLoader;

use Symfony\Component\Yaml\Yaml;

class ParserConfigurationLoader
{
    /**
    * @param array<string, mixed> $configs
    */
    public function __construct(
        private string $resourcePath,
        private array $configs = [],
    ) {
    }

    /**
    * @return array<string, mixed>
    */
    public function getConfig(string $parser): array
    {
        if (true === empty($this->configs)) {
            $this->configs = Yaml::parseFile($this->getResourceFile());
        }

        return $this->configs[$parser] ?? [];
    }

    private function getResourceFile(): string
    {
        return $this->resourcePath.'/parser/config.yaml';
    }
}
