<?php

use Illuminate\Support\Facades\File;

/**
 * Read the imported names from a PHP file.
 *
 * Only `use` statements are collected, so docblocks and string literals cannot
 * produce false positives. Trait imports inside classes are included as well.
 *
 * @return array<int, string>
 */
function tabRepeaterImportedNamespaces(string $path): array
{
    $tokens = token_get_all(File::get($path));
    $tokenCount = count($tokens);
    $imports = [];

    for ($index = 0; $index < $tokenCount; $index++) {
        $token = $tokens[$index];

        if (! is_array($token) || $token[0] !== T_USE) {
            continue;
        }

        $statement = '';

        for ($cursor = $index + 1; $cursor < $tokenCount; $cursor++) {
            $currentToken = $tokens[$cursor];
            $text = is_array($currentToken) ? $currentToken[1] : $currentToken;

            if ($text === ';') {
                break;
            }

            $statement .= $text;
        }

        $statement = trim($statement);

        // Closure imports (`use ($value)`) are not namespace imports.
        if (($statement === '') || str_starts_with($statement, '(')) {
            continue;
        }

        $imports[] = $statement;
    }

    return $imports;
}

it('only imports namespaces that are safe to extract', function () {
    $sourcePath = dirname(__DIR__, 2).'/src';

    $offendingImports = [];

    foreach (File::allFiles($sourcePath) as $file) {
        foreach (tabRepeaterImportedNamespaces($file->getPathname()) as $import) {
            $isApplicationNamespace = str_starts_with($import, 'App\\')
                || str_starts_with($import, 'Modules\\');

            if ($isApplicationNamespace) {
                $offendingImports[] = $file->getRelativePathname().': '.$import;
            }
        }
    }

    expect($offendingImports)->toBe([]);
});

it('ships every view it references', function () {
    $viewPath = dirname(__DIR__, 2).'/resources/views';

    $views = [
        'filament/forms/components/tab-repeater.blade.php',
        'filament/forms/components/partials/tabs.blade.php',
    ];

    foreach ($views as $view) {
        expect(File::exists($viewPath.'/'.$view))->toBeTrue();
    }

    expect(view()->exists('filament-tab-repeater::filament.forms.components.tab-repeater'))->toBeTrue()
        ->and(view()->exists('filament-tab-repeater::filament.forms.components.partials.tabs'))->toBeTrue();
});
