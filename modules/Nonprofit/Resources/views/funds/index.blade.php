<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('nonprofit::general.fund', 2) }}
    </x-slot>

    <x-slot name="buttons">
        @can('create-nonprofit-funds')
            <x-link href="{{ route('nonprofit.funds.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('nonprofit::general.fund', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="content">
        <x-index.search
            search-string="Modules\Nonprofit\Models\Fund"
        />

        @if ($funds->count())
            <x-table>
                <x-table.thead>
                    <x-table.tr>
                        <x-table.th class="w-2/12">
                            <x-sortablelink column="code" title="{{ trans('nonprofit::general.fund_code') }}" />
                        </x-table.th>
                        <x-table.th class="w-3/12">
                            <x-sortablelink column="name" title="{{ trans('nonprofit::general.fund_name') }}" />
                        </x-table.th>
                        <x-table.th class="w-2/12">
                            <x-sortablelink column="type" title="{{ trans('nonprofit::general.fund_type') }}" />
                        </x-table.th>
                        <x-table.th class="w-3/12">
                            {{ trans('nonprofit::general.parent_fund') }}
                        </x-table.th>
                        <x-table.th class="w-2/12 ltr:text-right rtl:text-left">
                            {{ trans('nonprofit::general.actions') }}
                        </x-table.th>
                    </x-table.tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($funds as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->code }}</x-table.td>
                            <x-table.td>{{ $item->name }}</x-table.td>
                            <x-table.td>{{ trans('nonprofit::general.' . $item->type) }}</x-table.td>
                            <x-table.td>{{ $item->parent?->name ?: '--' }}</x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">
                                <x-dropdown id="dropdown-{{ $item->id }}">
                                    <x-slot name="trigger">
                                        <span class="material-icons pointer-events-none">more_horiz</span>
                                    </x-slot>
                                    @can('update-nonprofit-funds')
                                        <x-dropdown.link href="{{ route('nonprofit.funds.edit', $item->id) }}">
                                            {{ trans('general.edit') }}
                                        </x-dropdown.link>
                                    @endcan
                                    @can('delete-nonprofit-funds')
                                        <x-delete-link
                                            model="Modules\Nonprofit\Models\Fund"
                                            :model-id="$item->id"
                                            route="nonprofit.funds.destroy"
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
                group="{{ trans_choice('nonprofit::general.fund', 2) }}"
                page="nonprofit.funds.create"
                :image="asset('public/img/no-data.svg')"
            >
                <x-slot name="title">
                    {{ trans('general.search.no_results') }}
                </x-slot>
            </x-empty-page>
        @endif
    </x-slot>
</x-layouts.admin>
