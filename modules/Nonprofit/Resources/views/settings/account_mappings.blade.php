<x-layouts.admin>
    <x-slot name="title">{{ trans('nonprofit::general.account_mappings') }}</x-slot>
    <x-slot name="content">
        <x-form.container>
            <x-form id="account-mappings-form" route="nonprofit.settings.account-mappings.update">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('nonprofit::general.account_mappings') }}" description="{{ trans('nonprofit::general.account_mappings_description') }}" />
                    </x-slot>
                    <x-slot name="body">
                        @if ($incomeCategories->isNotEmpty())
                            <h4 class="text-md font-semibold mb-3">{{ trans_choice('general.incomes', 2) }}</h4>
                            @foreach ($incomeCategories as $category)
                                <div class="flex items-center gap-4 mb-2">
                                    <span class="w-1/3 text-sm">{{ $category->name }}</span>
                                    <select name="mappings[{{ $category->id }}]" class="form-select w-2/3">
                                        <option value="">-- {{ trans('nonprofit::general.select_type') }} --</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" {{ ($mappings[$category->id] ?? '') == $account->id ? 'selected' : '' }}>
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        @endif

                        @if ($expenseCategories->isNotEmpty())
                            <h4 class="text-md font-semibold mt-6 mb-3">{{ trans_choice('general.expenses', 2) }}</h4>
                            @foreach ($expenseCategories as $category)
                                <div class="flex items-center gap-4 mb-2">
                                    <span class="w-1/3 text-sm">{{ $category->name }}</span>
                                    <select name="mappings[{{ $category->id }}]" class="form-select w-2/3">
                                        <option value="">-- {{ trans('nonprofit::general.select_type') }} --</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" {{ ($mappings[$category->id] ?? '') == $account->id ? 'selected' : '' }}>
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        @endif

                        @if ($incomeCategories->isEmpty() && $expenseCategories->isEmpty())
                            <x-empty-data>
                                <x-slot name="title">No categories found. Create income/expense categories first.</x-slot>
                            </x-empty-data>
                        @endif
                    </x-slot>
                </x-form.section>
                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons>
                            <x-form.buttons.save />
                        </x-form.buttons>
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>
</x-layouts.admin>
