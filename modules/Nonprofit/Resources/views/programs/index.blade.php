<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('nonprofit::general.program', 2) }}</x-slot>
    <x-slot name="buttons">
        @can('create-nonprofit-programs')
            <x-link href="{{ route('nonprofit.programs.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('nonprofit::general.program', 1)]) }}
            </x-link>
        @endcan
    </x-slot>
    <x-slot name="content">
        <x-index.search search-string="Modules\Nonprofit\Models\Program" />
        @if ($programs->count())
            <x-table>
                <x-table.thead>
                    <x-table.tr>
                        <x-table.th class="w-2/12"><x-sortablelink column="code" title="{{ trans('nonprofit::general.program_code') }}" /></x-table.th>
                        <x-table.th class="w-3/12"><x-sortablelink column="name" title="{{ trans('nonprofit::general.program_name') }}" /></x-table.th>
                        <x-table.th class="w-2/12">{{ trans('nonprofit::general.start_date') }}</x-table.th>
                        <x-table.th class="w-2/12">{{ trans('nonprofit::general.end_date') }}</x-table.th>
                        <x-table.th class="w-2/12"><x-sortablelink column="enabled" title="{{ trans('general.enabled') }}" /></x-table.th>
                        <x-table.th class="w-1/12 ltr:text-right rtl:text-left">{{ trans('nonprofit::general.actions') }}</x-table.th>
                    </x-table.tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($programs as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->code }}</x-table.td>
                            <x-table.td>{{ $item->name }}</x-table.td>
                            <x-table.td>{{ $item->start_date?->format('Y-m-d') ?: '--' }}</x-table.td>
                            <x-table.td>{{ $item->end_date?->format('Y-m-d') ?: '--' }}</x-table.td>
                            <x-table.td>{{ $item->enabled ? trans('nonprofit::general.yes') : trans('nonprofit::general.no') }}</x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">
                                <x-dropdown id="dropdown-{{ $item->id }}">
                                    <x-slot name="trigger"><span class="material-icons pointer-events-none">more_horiz</span></x-slot>
                                    @can('update-nonprofit-programs')
                                        <x-dropdown.link href="{{ route('nonprofit.programs.edit', $item->id) }}">{{ trans('general.edit') }}</x-dropdown.link>
                                    @endcan
                                    @can('delete-nonprofit-programs')
                                        <x-delete-link model="Modules\Nonprofit\Models\Program" :model-id="$item->id" route="nonprofit.programs.destroy" :parameter="$item->id" />
                                    @endcan
                                </x-dropdown>
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-table.tbody>
            </x-table>
        @else
            <x-empty-page group="{{ trans_choice('nonprofit::general.program', 2) }}" page="nonprofit.programs.create" :image="asset('public/img/no-data.svg')">
                <x-slot name="title">{{ trans('general.search.no_results') }}</x-slot>
            </x-empty-page>
        @endif
    </x-slot>
</x-layouts.admin>
