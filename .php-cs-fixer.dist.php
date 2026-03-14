<?php

$finder = PhpCsFixer\Finder::create()
    ->in( __DIR__ )
    ->exclude( [ 'vendor' ] );

return ( new PhpCsFixer\Config() )
    ->setIndent( '    ' )
    ->setLineEnding( "\n" )
    ->setRules( [
        // Start with PSR-2 as a base, then override for WordPress.
        '@PSR2' => true,

        // WordPress: spaces inside parentheses.
        'spaces_inside_parentheses' => [
            'space' => 'single',
        ],

        // WordPress: Yoda conditions.
        'yoda_style' => [
            'equal'            => true,
            'identical'        => true,
            'less_and_greater' => false,
        ],

        // WordPress: space around concatenation operator.
        'concat_space' => [
            'spacing' => 'one',
        ],

        // WordPress: space after type cast.
        'cast_spaces' => [
            'space' => 'single',
        ],

        // WordPress: use elseif, not else if.
        'elseif' => true,

        // WordPress: space after `!` operator.
        'not_operator_with_successor_space' => true,

        // No closing PHP tag.
        'no_closing_tag' => true,

        // Short array syntax.
        'array_syntax' => [
            'syntax' => 'short',
        ],

        // WordPress: braces on same line.
        'braces_position' => [
            'functions_opening_brace'                   => 'same_line',
            'classes_opening_brace'                     => 'same_line',
            'anonymous_classes_opening_brace'            => 'same_line',
            'control_structures_opening_brace'           => 'same_line',
            'anonymous_functions_opening_brace'          => 'same_line',
            'allow_single_line_empty_anonymous_classes'  => true,
        ],

        // Trailing comma in multiline.
        'trailing_comma_in_multiline' => [
            'elements' => [ 'arrays', 'arguments', 'parameters' ],
        ],

        // Blank line after namespace.
        'blank_line_after_namespace' => true,

        // No blank line after opening tag (WordPress style).
        'blank_line_after_opening_tag' => false,

        // Ordered imports.
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],

        // Single line after imports.
        'single_line_after_imports' => true,

        // Remove unused imports.
        'no_unused_imports' => true,

        // Full opening tag.
        'full_opening_tag' => true,

        // Visibility required on properties, methods, and constants.
        'visibility_required' => [
            'elements' => [ 'property', 'method', 'const' ],
        ],

        // Single blank line before namespace.
        'single_blank_line_before_namespace' => true,

        // No extra blank lines.
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'use',
            ],
        ],

        // WordPress: array indentation.
        'array_indentation' => true,

        // WordPress: method chaining indentation.
        'method_chaining_indentation' => true,

        // Ensure proper spacing in function declarations.
        'function_declaration' => [
            'closure_function_spacing' => 'one',
            'closure_fn_spacing'       => 'one',
        ],

        // Single space around binary operators.
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],

        // Trim trailing whitespace.
        'no_trailing_whitespace' => true,

        // Single blank line at end of file.
        'single_blank_line_at_eof' => true,
    ] )
    ->setFinder( $finder );
