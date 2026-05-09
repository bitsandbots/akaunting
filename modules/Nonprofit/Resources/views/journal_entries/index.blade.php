<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('nonprofit::general.journal_entry', 2) }}</x-slot>
    <x-slot name="buttons">
        @can('create-nonprofit-journal-entries')
            <x-link href="{{ route('nonprofit.journal-entries.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('nonprofit::general.journal_entry', 1)]) }}
            </x-link>
        @endcan
    </x-slot>
    <x-slot name="content">
        <x-index.search search-string="Modules\Nonprofit\Models\JournalEntry" />
        @if ($entries->count())
            <x-table>
                <x-table.thead>
                    <x-table.tr>
                        <x-table.th class="w-2/12"><x-sortablelink column="entry_number" title="{{ trans('nonprofit::general.entry_number') }}" /></x-table.th>
                        <x-table.th class="w-2/12"><x-sortablelink column="entry_date" title="{{ trans('nonprofit::general.entry_date') }}" /></x-table.th>
                        <x-table.th class="w-3/12"><x-sortablelink column="description" title="{{ trans('general.description') }}" /></x-table.th>
                        <x-table.th class="w-1/12"><x-sortablelink column="status" title="{{ trans('nonprofit::general.status') }}" /></x-table.th>
                        <x-table.th class="w-2/12">{{ trans('nonprofit::general.debit_amount') }}</x-table.th>
                        <x-table.th class="w-2/12">{{ trans('nonprofit::general.credit_amount') }}</x-table.th>
                        <x-table.th class="w-2/12 ltr:text-right rtl:text-left">{{ trans('nonprofit::general.actions') }}</x-table.th>
                    </x-table.tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($entries as $item)
                        <x-table.tr>
                            <x-table.td>
                                <a href="{{ route('nonprofit.journal-entries.show', $item->id) }}" class="text-blue-500 hover:underline">
                                    {{ $item->entry_number }}
                                </a>
                            </x-table.td>
                            <x-table.td>{{ $item->entry_date->format('Y-m-d') }}</x-table.td>
                            <x-table.td>{{ Str::limit($item->description, 60) }}</x-table.td>
                            <x-table.td>
                                @php
                                    $badgeColors = ['draft' => 'bg-yellow-100 text-yellow-800', 'posted' => 'bg-green-100 text-green-800', 'reversed' => 'bg-red-100 text-red-800', 'void' => 'bg-gray-100 text-gray-800'];
                                @endphp
                                <span class="badge {{ $badgeColors[$item->status] ?? 'bg-gray-100' }}">
                                    {{ trans('nonprofit::general.' . $item->status) }}
                                </span>
                            </x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">
                                {{ number_format($item->lines->sum('debit_amount'), 2) }}
                            </x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">
                                {{ number_format($item->lines->sum('credit_amount'), 2) }}
                            </x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">
                                <x-dropdown id="dropdown-{{ $item->id }}">
                                    <x-slot name="trigger"><span class="material-icons pointer-events-none">more_horiz</span></x-slot>
                                    <x-dropdown.link href="{{ route('nonprofit.journal-entries.show', $item->id) }}">
                                        {{ trans('general.show') }}
                                    </x-dropdown.link>
                                    @if ($item->isDraft())
                                        @can('update-nonprofit-journal-entries')
                                            <x-dropdown.link href="{{ route('nonprofit.journal-entries.edit', $item->id) }}">{{ trans('general.edit') }}</x-dropdown.link>
                                        @endcan
                                        @can('delete-nonprofit-journal-entries')
                                            <x-delete-link model="Modules\Nonprofit\Models\JournalEntry" :model-id="$item->id" route="nonprofit.journal-entries.destroy" :parameter="$item->id" />
                                        @endcan
                                    @endif
                                </x-dropdown>
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-table.tbody>
            </x-table>
        @else
            <x-empty-page group="{{ trans_choice('nonprofit::general.journal_entry', 2) }}" page="nonprofit.journal-entries.create" :image="asset('public/img/no-data.svg')">
                <x-slot name="title">{{ trans('general.search.no_results') }}</x-slot>
            </x-empty-page>
        @endif
    </x-slot>
</x-layouts.admin>
