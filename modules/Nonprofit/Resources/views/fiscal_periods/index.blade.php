<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('nonprofit::general.fiscal_period', 2) }}</x-slot>
    <x-slot name="buttons">
        @can('create-nonprofit-fiscal-periods')
            <x-link href="{{ route('nonprofit.fiscal-periods.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('nonprofit::general.fiscal_period', 1)]) }}
            </x-link>
        @endcan
    </x-slot>
    <x-slot name="content">
        <x-index.search search-string="Modules\Nonprofit\Models\FiscalPeriod" />
        @if ($periods->count())
            <x-table>
                <x-table.thead>
                    <x-table.tr>
                        <x-table.th class="w-2/12"><x-sortablelink column="name" title="{{ trans('nonprofit::general.fiscal_period_name') }}" /></x-table.th>
                        <x-table.th class="w-2/12"><x-sortablelink column="start_date" title="{{ trans('nonprofit::general.start_date') }}" /></x-table.th>
                        <x-table.th class="w-2/12"><x-sortablelink column="end_date" title="{{ trans('nonprofit::general.end_date') }}" /></x-table.th>
                        <x-table.th class="w-2/12"><x-sortablelink column="status" title="{{ trans('nonprofit::general.period_status') }}" /></x-table.th>
                        <x-table.th class="w-2/12">{{ trans('nonprofit::general.period_closed_at') }}</x-table.th>
                        <x-table.th class="w-2/12 ltr:text-right rtl:text-left">{{ trans('nonprofit::general.actions') }}</x-table.th>
                    </x-table.tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($periods as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->name }}</x-table.td>
                            <x-table.td>{{ $item->start_date->format('Y-m-d') }}</x-table.td>
                            <x-table.td>{{ $item->end_date->format('Y-m-d') }}</x-table.td>
                            <x-table.td><span class="badge {{ $item->status === 'open' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ trans('nonprofit::general.' . $item->status) }}</span></x-table.td>
                            <x-table.td>{{ $item->closed_at?->format('Y-m-d H:i') ?: '--' }}</x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">
                                <x-dropdown id="dropdown-{{ $item->id }}">
                                    <x-slot name="trigger"><span class="material-icons pointer-events-none">more_horiz</span></x-slot>
                                    @can('update-nonprofit-fiscal-periods')
                                        <x-dropdown.link href="{{ route('nonprofit.fiscal-periods.edit', $item->id) }}">{{ trans('general.edit') }}</x-dropdown.link>
                                    @endcan
                                    @can('delete-nonprofit-fiscal-periods')
                                        <x-delete-link model="Modules\Nonprofit\Models\FiscalPeriod" :model-id="$item->id" route="nonprofit.fiscal-periods.destroy" :parameter="$item->id" />
                                    @endcan
                                </x-dropdown>
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-table.tbody>
            </x-table>
        @else
            <x-empty-page group="{{ trans_choice('nonprofit::general.fiscal_period', 2) }}" page="nonprofit.fiscal-periods.create" :image="asset('public/img/no-data.svg')">
                <x-slot name="title">{{ trans('general.search.no_results') }}</x-slot>
            </x-empty-page>
        @endif
    </x-slot>
</x-layouts.admin>
