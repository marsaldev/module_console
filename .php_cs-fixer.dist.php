<?php

declare(strict_types=1);

$PsConfig = new PrestaShop\CodingStandards\CsFixer\Config();

$mcmPsConfig = new class extends PrestaShop\CodingStandards\CsFixer\Config {
    public function getRules(): array
    {
        return parent::getRules() +
            ['header_comment' => [
                'comment_type' => 'PHPDoc',
                'header' => file_get_contents(__DIR__ . '/.devtools/license_header.txt'),
                'location' => 'after_open',
                'separate' => 'bottom'
            ]];
    }
};

$mcmPsConfig
    ->setUsingCache(true)
    ->setCacheFile('.php_cs.cache')
    ->getFinder()
    ->in(__DIR__)
    ->exclude('vendor')
    ->notPath('tests/Resources');

return $mcmPsConfig;
