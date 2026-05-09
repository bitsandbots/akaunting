<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.new', ['type' => trans_choice('nonprofit::general.fund', 1)]) }}
    </x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="fund-form" route="nonprofit.funds.store">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('nonprofit::general.basic') }}" description="{{ trans_choice('nonprofit::general.fund', 1) }}" />
                    </x-slot>
                    <x-slot name="body">
                        <x-form.group.text name="code" label="{{ trans('nonprofit::general.fund_code') }}" value="{{ old('code') }}" required />
                        <x-form.group.text name="name" label="{{ trans('nonprofit::general.fund_name') }}" value="{{ old('name') }}" required />
                        <x-form.group.select name="type" label="{{ trans('nonprofit::general.fund_type') }}" :options="$types" selected="{{ old('type') }}" required />
                        <x-form.group.select name="parent_id" label="{{ trans('nonprofit::general.parent_fund') }}" :options="['' => '-- ' . trans('general.form.select.field', ['field' => trans('nonprofit::general.parent_fund')]) . ' --'] + $parents->toArray()" selected="{{ old('parent_id') }}" />
                        <x-form.group.textarea name="restriction_detail" label="{{ trans('nonprofit::general.fund_restriction') }}" value="{{ old('restriction_detail') }}" />
                        <x-form.group.textarea name="description" label="{{ trans('general.description') }}" value="{{ old('description') }}" />
                        <x-form.group.toggle name="enabled" label="{{ trans('general.enabled') }}" checked="{{ old('enabled', true) }}" />
                    </x-slot>
                </x-form.section>
                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons>
                            <x-form.buttons.cancel route="nonprofit.funds.index" />
                            <x-form.buttons.save />
                        </x-form.buttons>
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>
</x-layouts.admin>
