<?php

namespace Tests\Unit\Intelligence;

use PHPUnit\Framework\TestCase;

class ArchitectureTest extends TestCase
{
    public function test_domain_is_pure_php_and_application_does_not_import_adapters(): void
    {
        $root = dirname(__DIR__, 3).'/app/Modules/Intelligence';
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            $path = str_replace('\\', '/', $file->getPathname());
            if (str_contains($path, '/Domain/')) {
                $this->assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|Infrastructure|Application|Facades)\\\\/', $source, $path);
                $this->assertDoesNotMatchRegularExpression('/\\b(?:app|config|now|request|response)\\(/', $source, $path);
            }
            if (str_contains($path, '/Application/')) {
                $this->assertDoesNotMatchRegularExpression('/^use .*\\\\(?:Infrastructure|Presentation)\\\\/m', $source, $path);
                preg_match_all('/^use App\\\\Modules\\\\(?!Intelligence\\\\)([^;]+);/m', $source, $imports);
                foreach ($imports[1] as $import) {
                    $this->assertMatchesRegularExpression('/^[^\\\\]+\\\\Application\\\\(?:Contracts|DTO|Exception)\\\\/', $import, $path);
                }
            }
        }
    }
}
