<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Yaml\Yaml;
use Michelf\MarkdownExtra;

class Parser
{
    public function parse(string $content): array
    {
        if (preg_match('/^\s*(?:---[\s]*[\r\n]+)(.*?)(?:---[\s]*[\r\n]+)(.*?)$/s', $content, $matches)) {
            $parameters = $matches[1];
            $content = $matches[2];
            $parameters = Yaml::parse($parameters);

            $parser = new MarkdownExtra();
            $parser->hard_wrap = true;

            return [
                'content' => $parser->transform($content),
            ] + $parameters;
        }

        return [];
    }
}
