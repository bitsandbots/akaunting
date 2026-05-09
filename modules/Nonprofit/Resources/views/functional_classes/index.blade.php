<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('nonprofit::general.functional_class', 2) }}</x-slot>
    <x-slot name="buttons">
        @can('create-nonprofit-functional-classes')
            <x-link href="{{ route('nonprofit.functional-classes.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('nonprofit::general.functional_class', 1)]) }}
            </x-link>
        @endcan
    </x-slot>
    <x-slot name="content">
        <x-index.search search-string="Modules\Nonprofit\Models\FunctionalClass" />
        @if ($classes->count())
            <x-table>
                <x-table.thead>
                    <x-table.tr>
                        <x-table.th class="w-2/12"><x-sortablelink column="code" title="{{ trans('nonprofit::general.functional_class_code') }}" /></x-table.th>
                        <x-table.th class="w-3/12"><x-sortablelink column="name" title="{{ trans('nonprofit::general.functional_class_name') }}" /></x-table.th>
                        <x-table.th class="w-3/12"><x-sortablelink column="parent_class" title="{{ trans('nonprofit::general.parent_class') }}" /></x-table.th>
                        <x-table.th class="w-2/12"><x-sortablelink column="is_system" title="{{ trans('nonprofit::general.is_system') }}" /></x-table.th>
                        <x-table.th class="w-2/12 ltr:text-right rtl:text-left">{{ trans('nonprofit::general.actions') }}</x-table.th>
                    </x-table.tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($classes as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->code }}</x-table.td>
                            <x-table.td>{{ $item->name }}</x-table.td>
                            <x-table.td>{{ trans('nonprofit::general.' . $item->parent_class) }}</x-table.td>
                            <x-table.td>{{ $item->is_system ? trans('nonprofit::general.yes') : trans('nonprofit::general.no') }}</x-table.td>
                            <x-table.td class="ltr:text-right rtl:text-left">
                                <x-dropdown id="dropdown-{{ $item->id }}">
                                    <x-slot name="trigger"><span class="material-icons pointer-events-none">more_horiz</span></x-slot>
                                    @can('update-nonprofit-functional-classes')
                                        <x-dropdown.link href="{{ route('nonprofit.functional-classes.edit', $item->id) }}">{{ trans('general.edit') }}</x-dropdown.link>
                                    @endcan
                                    @unless($item->is_system)
                                        @can('delete-nonprofit-functional-classes')
                                            <x-delete-link model="Modules\Nonprofit\Models\FunctionalClass" :model-id="$item->id" route="nonprofit.functional-classes.destroy" :parameter="$item->id" />
                                        @endcan
                                    @endunless
                                </x-dropdown>
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-table.tbody>
            </x-table>
        @else
            <x-empty-page group="{{ trans_choice('nonprofit::general.functional_class', 2) }}" page="nonprofit.functional-classes.create" :image="asset('public/img/no-data.svg')">
                <x-slot name="title">{{ trans('general.search.no_results') }}</x-slot>
            </x-empty-page>
        @endif
    </x-slot>
</x-layouts.admin>
