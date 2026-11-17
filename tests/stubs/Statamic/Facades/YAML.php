<?php

namespace Statamic\Facades;

use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Test stub mirroring Statamic\Yaml\Yaml::parse()/dump() (Statamic 4).
 */
class YAML
{
    public static function parse($str = null)
    {
        if (empty($str)) {
            return [];
        }

        return SymfonyYaml::parse($str);
    }

    public static function dump($data)
    {
        return SymfonyYaml::dump($data, 100, 2, SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }
}
