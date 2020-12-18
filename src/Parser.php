<?php
declare(strict_types = 1);

namespace App;

use Symfony\Component\Yaml\Yaml;
use Michelf\Markdown;

class Parser
{
    public function parse(string $content): array
    {
        if (preg_match('/^\s*(?:---[\s]*[\r\n]+)(.*?)(?:---[\s]*[\r\n]+)(.*?)$/s', $content, $matches)) {
            $parameters = $matches[1];
            $content = $matches[2];
            $parameters = Yaml::parse($parameters);
            return [
                'content' => Markdown::defaultTransform($content),
            ]+$parameters;
        }
    }
}
