<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('nonprofit::general.journal_entry', 1) }}: {{ $journalEntry->entry_number }}</x-slot>
    <x-slot name="buttons">
        @if ($journalEntry->isDraft())
            <x-link href="{{ route('nonprofit.journal-entries.edit', $journalEntry->id) }}" kind="secondary">
                {{ trans('general.edit') }}
            </x-link>
            <x-form id="post-entry" route="nonprofit.journal-entries.post" :model="$journalEntry" id="post-form" style="display:inline">
                <button type="submit" class="button button-green" onclick="return confirm('{{ trans('nonprofit::general.post_confirmation') }}')">
                    {{ trans('nonprofit::general.post') }}
                </button>
            </x-form>
        @endif
        @if ($journalEntry->isPosted())
            <button type="button" class="button button-orange" onclick="document.getElementById('reverse-modal').classList.remove('hidden')">
                {{ trans('nonprofit::general.reverse') }}
            </button>
        @endif
        @if ($journalEntry->isDraft())
            <x-form id="void-entry" route="nonprofit.journal-entries.void" :model="$journalEntry" style="display:inline">
                <button type="submit" class="button button-gray" onclick="return confirm('{{ trans('nonprofit::general.void_confirmation') }}')">
                    {{ trans('nonprofit::general.void_entry') }}
                </button>
            </x-form>
        @endif
    </x-slot>
    <x-slot name="content">
        <!-- Header Info -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="text-sm text-gray-500">{{ trans('nonprofit::general.entry_number') }}</span>
                    <p class="font-semibold">{{ $journalEntry->entry_number }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">{{ trans('nonprofit::general.entry_date') }}</span>
                    <p class="font-semibold">{{ $journalEntry->entry_date->format('Y-m-d') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">{{ trans('nonprofit::general.status') }}</span>
                    <p><span class="badge {{ $journalEntry->status === 'posted' ? 'bg-green-100 text-green-800' : ($journalEntry->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">{{ trans('nonprofit::general.' . $journalEntry->status) }}</span></p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">{{ trans('general.description') }}</span>
                    <p>{{ $journalEntry->description ?: '--' }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">{{ trans('nonprofit::general.reference') }}</span>
                    <p>{{ $journalEntry->reference ?: '--' }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">{{ trans('nonprofit::general.currency_code') }}</span>
                    <p>{{ $journalEntry->currency_code }}</p>
                </div>
            </div>
            @if ($journalEntry->posted_at)
                <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t">
                    <div>
                        <span class="text-sm text-gray-500">{{ trans('nonprofit::general.posted_at') }}</span>
                        <p>{{ $journalEntry->posted_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            @endif
            @if ($journalEntry->transaction)
                <div class="mt-4 pt-4 border-t">
                    <span class="text-sm text-gray-500">{{ trans('nonprofit::general.transaction_id') }}</span>
                    <p>#{{ $journalEntry->transaction->id }}</p>
                </div>
            @endif
        </div>

        <!-- Lines -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">{{ trans('nonprofit::general.lines') }}</h3>
            <x-table>
                <x-table.thead>
                    <x-table.tr>
                        <x-table.th class="w-3/12">{{ trans('nonprofit::general.account') }}</x-table.th>
                        <x-table.th class="w-2/12 ltr:text-right rtl:text-left">{{ trans('nonprofit::general.debit_amount') }}</x-table.th>
                        <x-table.th class="w-2/12 ltr:text-right rtl:text-left">{{ trans('nonprofit::general.credit_amount') }}</x-table.th>
                        <x-table.th class="w-1/12">{{ trans('nonprofit::general.fund_id') }}</x-table.th>
                        <x-table.th class="w-1/12">{{ trans('nonprofit::general.program_id') }}</x-table.th>
                        <x-table.th class="w-2/12">{{ trans('nonprofit::general.functional_class_id') }}</x-table.th>
                        <x-table.th class="w-2/12">{{ trans('general.description') }}</x-table.th>
                    </x-table.tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($journalEntry->lines as $line)
                        <x-table.tr>
                            <x-table.td>{{ $line->chartOfAccount->code }} - {{ $line->chartOfAccount->name }}</x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">{{ number_format($line->debit_amount, 2) }}</x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">{{ number_format($line->credit_amount, 2) }}</x-table.td>
                            <x-table.td>{{ $line->fund?->code ?: '--' }}</x-table.td>
                            <x-table.td>{{ $line->program?->code ?: '--' }}</x-table.td>
                            <x-table.td>{{ $line->functionalClass?->code ?: '--' }}</x-table.td>
                            <x-table.td>{{ Str::limit($line->description, 40) ?: '--' }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-table.tbody>
                <x-table.tfoot>
                    <x-table.tr class="font-bold bg-gray-50">
                        <x-table.td>{{ trans('general.total') }}</x-table.td>
                        <x-table.td class="ltr:text-right rtl:text-left">{{ number_format($journalEntry->lines->sum('debit_amount'), 2) }}</x-table.td>
                        <x-table.td class="ltr:text-right rtl:text-left">{{ number_format($journalEntry->lines->sum('credit_amount'), 2) }}</x-table.td>
                        <x-table.td colspan="4"></x-table.td>
                    </x-table.tr>
                </x-table.tfoot>
            </x-table>
        </div>

        <!-- Reverse Modal -->
        @if ($journalEntry->isPosted())
            <div id="reverse-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h3 class="text-lg font-semibold mb-4">{{ trans('nonprofit::general.reverse') }}</h3>
                    <x-form id="reverse-entry" route="nonprofit.journal-entries.reverse" :model="$journalEntry">
                        <x-form.group.textarea name="reason" label="{{ trans('nonprofit::general.reverse_reason') }}" value="{{ old('reason', 'Manual reversal') }}" required />
                        <x-form.buttons>
                            <button type="button" class="button button-gray" onclick="document.getElementById('reverse-modal').classList.add('hidden')">{{ trans('nonprofit::general.cancel') }}</button>
                            <x-form.buttons.save text="{{ trans('nonprofit::general.reverse') }}" />
                        </x-form.buttons>
                    </x-form>
                </div>
            </div>
        @endif
    </x-slot>
</x-layouts.admin>
