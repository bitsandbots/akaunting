<x-layouts.admin>
    <x-slot name="title">{{ trans('nonprofit::general.dimension_defaults') }}</x-slot>
    <x-slot name="content">
        <x-form.container>
            <x-form id="dimension-defaults-form" route="nonprofit.settings.dimension-defaults.update">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('nonprofit::general.dimension_defaults') }}" description="{{ trans('nonprofit::general.dimension_defaults_description') }}" />
                    </x-slot>
                    <x-slot name="body">
                        <x-form.group.select
                            name="default_fund_id"
                            label="{{ trans('nonprofit::general.default_fund') }}"
                            :options="['' => '-- ' . trans('nonprofit::general.select_fund') . ' --'] + $funds->toArray()"
                            selected="{{ old('default_fund_id', $defaults['default_fund_id'] ?? '') }}"
                        />

                        <x-form.group.select
                            name="default_program_id"
                            label="{{ trans('nonprofit::general.default_program') }}"
                            :options="['' => '-- ' . trans('nonprofit::general.select_program') . ' --'] + $programs->toArray()"
                            selected="{{ old('default_program_id', $defaults['default_program_id'] ?? '') }}"
                        />

                        <x-form.group.select
                            name="default_functional_class_id"
                            label="{{ trans('nonprofit::general.default_functional_class') }}"
                            :options="['' => '-- ' . trans('nonprofit::general.select_functional_class') . ' --'] + $functionalClasses->toArray()"
                            selected="{{ old('default_functional_class_id', $defaults['default_functional_class_id'] ?? '') }}"
                        />
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
