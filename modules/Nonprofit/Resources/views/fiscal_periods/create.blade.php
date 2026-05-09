<x-layouts.admin>
    <x-slot name="title">{{ trans('general.title.new', ['type' => trans_choice('nonprofit::general.fiscal_period', 1)]) }}</x-slot>
    <x-slot name="content">
        <x-form.container>
            <x-form id="fiscal-period-form" route="nonprofit.fiscal-periods.store">
                <x-form.section>
                    <x-slot name="head"><x-form.section.head title="{{ trans('nonprofit::general.basic') }}" description="{{ trans_choice('nonprofit::general.fiscal_period', 1) }}" /></x-slot>
                    <x-slot name="body">
                        <x-form.group.text name="name" label="{{ trans('nonprofit::general.fiscal_period_name') }}" value="{{ old('name') }}" required />
                        <x-form.group.date name="start_date" label="{{ trans('nonprofit::general.start_date') }}" value="{{ old('start_date') }}" required />
                        <x-form.group.date name="end_date" label="{{ trans('nonprofit::general.end_date') }}" value="{{ old('end_date') }}" required />
                        <x-form.group.select name="status" label="{{ trans('nonprofit::general.period_status') }}" :options="$statuses" selected="{{ old('status', 'open') }}" />
                    </x-slot>
                </x-form.section>
                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons>
                            <x-form.buttons.cancel route="nonprofit.fiscal-periods.index" />
                            <x-form.buttons.save />
                        </x-form.buttons>
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>
</x-layouts.admin>
