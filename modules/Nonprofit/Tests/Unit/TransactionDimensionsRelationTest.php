<?php

namespace Modules\Nonprofit\Tests\Unit;

use App\Models\Banking\Transaction;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Nonprofit\Models\TransactionDimension;
use Modules\Nonprofit\Tests\TestCase;

class TransactionDimensionsRelationTest extends TestCase
{
    public function test_transaction_has_dimensions_relation(): void
    {
        $tx = new Transaction;

        $this->assertTrue($tx->isRelation('dimensions'));
        $this->assertInstanceOf(HasMany::class, $tx->dimensions());
        $this->assertSame(TransactionDimension::class, get_class($tx->dimensions()->getRelated()));
    }
}
