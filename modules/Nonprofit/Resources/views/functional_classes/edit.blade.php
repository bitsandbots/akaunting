<x-layouts.admin>
    <x-slot name="title">{{ trans('general.title.edit', ['type' => trans_choice('nonprofit::general.functional_class', 1)]) }}</x-slot>
    <x-slot name="content">
        <x-form.container>
            <x-form id="functional-class-form" route="nonprofit.functional-classes.update" :model="$functionalClass">
                <x-form.section>
                    <x-slot name="head"><x-form.section.head title="{{ trans('nonprofit::general.basic') }}" description="{{ $functionalClass->code }} - {{ $functionalClass->name }}" /></x-slot>
                    <x-slot name="body">
                        <x-form.group.text name="code" label="{{ trans('nonprofit::general.functional_class_code') }}" value="{{ old('code', $functionalClass->code) }}" required />
                        <x-form.group.text name="name" label="{{ trans('nonprofit::general.functional_class_name') }}" value="{{ old('name', $functionalClass->name) }}" required />
                        <x-form.group.select name="parent_class" label="{{ trans('nonprofit::general.parent_class') }}" :options="$parentClasses" selected="{{ old('parent_class', $functionalClass->parent_class) }}" required />
                        <x-form.group.toggle name="is_system" label="{{ trans('nonprofit::general.is_system') }}" checked="{{ old('is_system', $functionalClass->is_system) }}" />
                        <x-form.group.toggle name="enabled" label="{{ trans('general.enabled') }}" checked="{{ old('enabled', $functionalClass->enabled) }}" />
                    </x-slot>
                </x-form.section>
                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons>
                            <x-form.buttons.cancel route="nonprofit.functional-classes.index" />
                            <x-form.buttons.save />
                        </x-form.buttons>
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>
</x-layouts.admin>
