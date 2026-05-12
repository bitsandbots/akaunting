<?php

namespace Modules\Nonprofit\Tests\Unit;

use Modules\Nonprofit\Enums\FundType;
use Modules\Nonprofit\Tests\TestCase;

class FasbFundTypeTest extends TestCase
{
    public function test_fund_type_has_exactly_the_two_fasb_2016_14_cases(): void
    {
        $values = array_map(fn (FundType $c) => $c->value, FundType::cases());
        sort($values);

        $this->assertSame(
            ['with_donor_restrictions', 'without_donor_restrictions'],
            $values,
            'FASB ASU 2016-14 collapsed temporarily/permanently restricted into a single with_donor_restrictions class.'
        );
    }

    public function test_no_legacy_fasb_strings_remain_in_module_source(): void
    {
        $forbidden = ['temporarily_restricted', 'permanently_restricted'];

        foreach ($this->scanModulePhpFiles() as $file) {
            // Skip this test file itself — it intentionally names the forbidden
            // strings so the regression contract is self-documenting.
            if (realpath($file) === realpath(__FILE__)) {
                continue;
            }

            $contents = file_get_contents($file);
            foreach ($forbidden as $needle) {
                $this->assertStringNotContainsString(
                    $needle,
                    $contents,
                    "Legacy FASB string '{$needle}' found in {$file}"
                );
            }
        }
    }

    private function scanModulePhpFiles(): array
    {
        $base = realpath(__DIR__ . '/../../');
        $rii  = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base));
        $out  = [];

        foreach ($rii as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                $out[] = $file->getPathname();
            }
        }

        return $out;
    }
}
