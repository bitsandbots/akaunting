<?php

namespace Modules\Nonprofit\Tests\Unit;

use Modules\Nonprofit\Enums\JournalEntryStatus;
use Modules\Nonprofit\Models\JournalEntry;
use Modules\Nonprofit\Tests\TestCase;
use RuntimeException;

class JournalEntryStatusTransitionTest extends TestCase
{
    /** @dataProvider transitions */
    public function test_status_transition(string $from, string $to, bool $sanctioned, bool $allowed): void
    {
        $entry = JournalEntry::factory()->create(['status' => $from]);

        $apply = fn () => $entry->update(['status' => $to]);

        if ($sanctioned) {
            $apply = fn () => JournalEntry::withSanctionedReversal(fn () => $entry->update(['status' => $to]));
        }

        if ($allowed) {
            $apply();
            $this->assertSame($to, $entry->fresh()->status);
        } else {
            $this->expectException(RuntimeException::class);
            $apply();
        }
    }

    public static function transitions(): array
    {
        $D = JournalEntryStatus::Draft->value;
        $P = JournalEntryStatus::Posted->value;
        $R = JournalEntryStatus::Reversed->value;
        $V = JournalEntryStatus::Void->value;

        return [
            'draft -> posted'                   => [$D, $P, false, true],
            'draft -> void'                     => [$D, $V, false, true],
            'draft -> reversed (illegal)'       => [$D, $R, false, false],
            'posted -> reversed (sanctioned)'   => [$P, $R, true,  true],
            'posted -> reversed (unsanctioned)' => [$P, $R, false, false],
            'posted -> draft (illegal)'         => [$P, $D, false, false],
            'posted -> void (illegal)'          => [$P, $V, false, false],
            'reversed -> draft (illegal)'       => [$R, $D, false, false],
            'void -> posted (illegal)'          => [$V, $P, false, false],
        ];
    }
}
