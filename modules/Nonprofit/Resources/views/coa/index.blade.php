<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('nonprofit::general.coa', 2) }}
    </x-slot>

    <x-slot name="buttons">
        @can('create-nonprofit-coa')
            <x-link href="{{ route('nonprofit.coa.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('nonprofit::general.coa', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="content">
        <x-index.search
            search-string="Modules\Nonprofit\Models\ChartOfAccount"
        />

        @if ($accounts->count())
            <x-table>
                <x-table.thead>
                    <x-table.tr>
                        <x-table.th class="w-2/12">
                            <x-sortablelink column="code" title="{{ trans('nonprofit::general.coa_code') }}" />
                        </x-table.th>
                        <x-table.th class="w-4/12">
                            <x-sortablelink column="name" title="{{ trans('nonprofit::general.coa_name') }}" />
                        </x-table.th>
                        <x-table.th class="w-2/12">
                            <x-sortablelink column="type" title="{{ trans('nonprofit::general.coa_type') }}" />
                        </x-table.th>
                        <x-table.th class="w-2/12">
                            {{ trans('nonprofit::general.parent_coa') }}
                        </x-table.th>
                        <x-table.th class="w-2/12 ltr:text-right rtl:text-left">
                            {{ trans('nonprofit::general.actions') }}
                        </x-table.th>
                    </x-table.tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($accounts as $item)
                        <x-table.tr>
                            <x-table.td>
                                {{ $item->code }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->name }}
                            </x-table.td>
                            <x-table.td>
                                {{ trans('nonprofit::general.' . $item->type) }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->parent?->name ?: '--' }}
                            </x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">
                                <x-dropdown id="dropdown-{{ $item->id }}">
                                    <x-slot name="trigger">
                                        <span class="material-icons pointer-events-none">more_horiz</span>
                                    </x-slot>
                                    @can('update-nonprofit-coa')
                                        <x-dropdown.link href="{{ route('nonprofit.coa.edit', $item->id) }}">
                                            {{ trans('general.edit') }}
                                        </x-dropdown.link>
                                    @endcan
                                    @can('delete-nonprofit-coa')
                                        <x-delete-link
                                            model="Modules\Nonprofit\Models\ChartOfAccount"
                                            :model-id="$item->id"
                                            route="nonprofit.coa.destroy"
                                            :parameter="$item->id"
                                        />
                                    @endcan
                                </x-dropdown>
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-table.tbody>
            </x-table>
        @else
            <x-empty-page
                group="{{ trans_choice('nonprofit::general.coa', 2) }}"
                page="nonprofit.coa.create"
                :image="asset('public/img/no-data.svg')"
            >
                <x-slot name="title">
                    {{ trans('general.search.no_results') }}
                </x-slot>
            </x-empty-page>
        @endif
    </x-slot>
</x-layouts.admin>
