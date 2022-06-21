<?php

// flexible CS rules
$finder = PhpCsFixer\Finder::create()
        ->in(__DIR__)
        ->exclude('vendor')
        ->exclude('var')
//->name('*.php')
;
$config = new PhpCsFixer\Config();
$config->setRules([
            '@PSR12' => true,
            'align_multiline_comment' => true,
            'array_indentation' => true,
            'array_push' => true,
            'array_syntax' => ['syntax' => 'short'],
            'backtick_to_shell_exec' => true,
            'binary_operator_spaces' => [
                'operators' => [
                    '=' => 'align_single_space_minimal',
                    '=>' => 'align_single_space_minimal',
                ],
            ],
            'blank_line_after_namespace' => true,
            'blank_line_before_statement' => [
                'statements' => [
                    'break',
                    'continue',
                    'declare',
                    'default',
                    'do',
                    'exit',
                    'for',
                    'foreach',
                    'if',
                    'include',
                    'include_once',
                    'require',
                    'require_once',
                    'return',
                    'switch',
                    'throw',
                    'try',
                    'while',
                    'yield',
                ],
            ],
            'cast_spaces' => true,
            'class_attributes_separation' => [
                'elements' => [
                    'const' => 'one',
                    'method' => 'one',
                    'property' => 'only_if_meta'
                ]
            ],
            'clean_namespace' => true,
            'combine_consecutive_issets' => true,
            'combine_consecutive_unsets' => true,
            'combine_nested_dirname' => true,
            'concat_space' => ['spacing' => 'one'],
            'constant_case' => true,
            'declare_strict_types' => true,
            'dir_constant' => true,
            'echo_tag_syntax' => true,
            'elseif' => true,
            'encoding' => true,
            'ereg_to_preg' => true,
            'explicit_indirect_variable' => true,
            'explicit_string_variable' => true,
            'fopen_flag_order' => true,
            'full_opening_tag' => true,
            'fully_qualified_strict_types' => true,
            'function_declaration' => true,
            'function_to_constant' => true,
            'function_typehint_space' => true,
            'global_namespace_import' => [
                'import_classes' => true,
                'import_constants' => true,
                'import_functions' => true,
            ],
            'is_null' => true,
            'lambda_not_used_import' => true,
            'line_ending' => true,
            'list_syntax' => ['syntax' => 'short'],
            'logical_operators' => true,
            'lowercase_keywords' => true,
            'magic_constant_casing' => true,
            'magic_method_casing' => true,
            'method_argument_space' => [
                'on_multiline' => 'ensure_fully_multiline',
            ],
            'method_chaining_indentation' => true,
            'modernize_types_casting' => true,
            'multiline_comment_opening_closing' => true,
            'multiline_whitespace_before_semicolons' => [
                'strategy' => 'new_line_for_chained_calls'
            ],
            'native_constant_invocation' => [
                'fix_built_in' => false,
                'include' => ['DIRECTORY_SEPARATOR', 'PHP_INT_SIZE', 'PHP_VERSION_ID'],
                'scope' => 'namespaced',
                'strict' => true
            ],
            'native_function_casing' => true,
            'native_function_invocation' => [
                'include' => ['@internal'],
                'scope' => 'namespaced',
                'strict' => true
            ],
            'native_function_type_declaration_casing' => true,
            'no_alternative_syntax' => true,
            'no_binary_string' => true,
            'no_blank_lines_after_phpdoc' => true,
            'no_break_comment' => true,
            'no_closing_tag' => true,
            'no_empty_statement' => true,
            'no_extra_blank_lines' => true,
            'no_homoglyph_names' => true,
            'no_leading_namespace_whitespace' => true,
            'no_multiline_whitespace_around_double_arrow' => true,
            'no_null_property_initialization' => true,
            'no_php4_constructor' => true,
            'no_short_bool_cast' => true,
            'no_singleline_whitespace_before_semicolons' => true,
            'no_spaces_after_function_name' => true,
            'no_trailing_whitespace' => true,
            'no_trailing_whitespace_in_comment' => true,
            'no_trailing_whitespace_in_string' => true,
            'no_unneeded_control_parentheses' => true,
            'no_unneeded_curly_braces' => true,
            'no_unneeded_final_method' => true,
            'no_unreachable_default_argument_value' => true,
            'no_unset_cast' => true,
            'no_unset_on_property' => true,
            'no_unused_imports' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'no_useless_sprintf' => true,
            'no_whitespace_before_comma_in_array' => true,
            'non_printable_character' => true,
            'ordered_imports' => [
                'sort_algorithm' => 'alpha',
                'imports_order' => ['const', 'class', 'function']
            ],
            'php_unit_no_expectation_annotation' => true,
            'return_assignment' => true,
            'self_accessor' => true,
            'self_static_accessor' => true,
            'semicolon_after_instruction' => true,
            'set_type_to_cast' => true,
            'single_blank_line_at_eof' => true,
            'single_class_element_per_statement' => true,
            'single_import_per_statement' => true,
            'single_line_after_imports' => true,
            'single_quote' => true,
            'single_space_after_construct' => true,
            'space_after_semicolon' => true,
            'standardize_increment' => true,
            'standardize_not_equals' => true,
            'string_line_ending' => true,
            'switch_case_semicolon_to_colon' => true,
            'switch_case_space' => true,
            'switch_continue_to_break' => true,
            'ternary_to_elvis_operator' => true,
            'ternary_to_null_coalescing' => true,
            'trailing_comma_in_multiline' => [
                'elements' => [
                    'arrays'
                ]
            ],
            'trim_array_spaces' => true,
            'types_spaces' => [
                'space' => 'none',
            ],
            'unary_operator_spaces' => true,
            'whitespace_after_comma_in_array' => true,
        ])
        ->setRiskyAllowed(true)
        ->setFinder($finder)
;
return $config->setUsingCache(false);
