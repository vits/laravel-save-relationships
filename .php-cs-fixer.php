<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$header = <<<'EOF'
    This file is part of PHP CS Fixer.

    (c) Fabien Potencier <fabien@symfony.com>
        Dariusz Rumiński <dariusz.ruminski@gmail.com>

    This source file is subject to the MIT license that is bundled
    with this source code in the file LICENSE.
    EOF;

$finder = PhpCsFixer\Finder::create()
    ->ignoreDotFiles(false)
    ->ignoreVCSIgnored(true)
    ->exclude(['dev-tools/phpstan', 'tests/Fixtures'])
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP82Migration' => true,
        // '@PHP80Migration:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => false,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'declare_strict_types' => false, // copy+paste problem in VS Code when formatting on paste
        'not_operator_with_successor_space' => true,
        // 'class_attributes_separation' => true,
        // 'general_phpdoc_annotation_remove' => ['annotations' => ['expectedDeprecation']], // one should use PHPUnit built-in method instead
        // 'header_comment' => ['header' => $header],
        // 'heredoc_indentation' => false, // TODO switch on when # of PR's is lower
        // 'modernize_strpos' => true, // needs PHP 8+ or polyfill
        // 'no_useless_concat_operator' => false, // TODO switch back on when the `src/Console/Application.php` no longer needs the concat
        // 'use_arrow_functions' => false, // TODO switch on when # of PR's is lower
    ])
    ->setFinder($finder);

return $config;
