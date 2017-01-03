<?php

// see https://github.com/FriendsOfPHP/PHP-CS-Fixer

$finder = PhpCsFixer\Finder::create()
    ->in(array(__DIR__ . '/src', __DIR__ . '/tests'))
;

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@PSR1' => true,
        '@PSR2' => true,
        '@Symfony' => true,
        'array_syntax' => array('syntax' => 'long'),
        'phpdoc_order' => true,
        'no_trailing_whitespace' => true,
        'concat_space' => array('spacing' => 'one'),
    ))
    ->setFinder($finder)
;
