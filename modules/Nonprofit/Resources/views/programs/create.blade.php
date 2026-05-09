<x-layouts.admin>
    <x-slot name="title">{{ trans('general.title.new', ['type' => trans_choice('nonprofit::general.program', 1)]) }}</x-slot>
    <x-slot name="content">
        <x-form.container>
            <x-form id="program-form" route="nonprofit.programs.store">
                <x-form.section>
                    <x-slot name="head"><x-form.section.head title="{{ trans('nonprofit::general.basic') }}" description="{{ trans_choice('nonprofit::general.program', 1) }}" /></x-slot>
                    <x-slot name="body">
                        <x-form.group.text name="code" label="{{ trans('nonprofit::general.program_code') }}" value="{{ old('code') }}" required />
                        <x-form.group.text name="name" label="{{ trans('nonprofit::general.program_name') }}" value="{{ old('name') }}" required />
                        <x-form.group.date name="start_date" label="{{ trans('nonprofit::general.start_date') }}" value="{{ old('start_date') }}" />
                        <x-form.group.date name="end_date" label="{{ trans('nonprofit::general.end_date') }}" value="{{ old('end_date') }}" />
                        <x-form.group.textarea name="description" label="{{ trans('general.description') }}" value="{{ old('description') }}" />
                        <x-form.group.toggle name="enabled" label="{{ trans('general.enabled') }}" checked="{{ old('enabled', true) }}" />
                    </x-slot>
                </x-form.section>
                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons>
                            <x-form.buttons.cancel route="nonprofit.programs.index" />
                            <x-form.buttons.save />
                        </x-form.buttons>
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>
</x-layouts.admin>
