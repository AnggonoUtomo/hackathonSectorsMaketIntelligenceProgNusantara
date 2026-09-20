<?php

namespace Tests\Unit\Company;

use App\Modules\Company\Application\FakeCompanySnapshot;
use Tests\TestCase;

class FakeCompanySnapshotTest extends TestCase
{
    public function test_it_returns_fake_company_snapshot_by_symbol(): void
    {
        $snapshot = app(FakeCompanySnapshot::class)->find('bbca');

        $this->assertNotNull($snapshot);
        $this->assertSame('BBCA', $snapshot['symbol']);
        $this->assertSame('Bank Central Asia Tbk', $snapshot['name']);
        $this->assertSame('backend_fake', $snapshot['meta']['source']);
        $this->assertSame('Nilai Prioritas Riset', $snapshot['metrics'][0]['label']);
    }

    public function test_it_returns_null_for_unknown_fake_company(): void
    {
        $this->assertNull(app(FakeCompanySnapshot::class)->find('ZZZZ'));
    }
}
