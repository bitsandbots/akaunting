<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.edit', ['type' => trans_choice('nonprofit::general.journal_entry', 1)]) }}: {{ $journalEntry->entry_number }}
    </x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="journal-entry-form" route="nonprofit.journal-entries.update" :model="$journalEntry">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head
                            title="{{ trans('nonprofit::general.basic') }}"
                            description="{{ $journalEntry->entry_number }}"
                        />
                    </x-slot>
                    <x-slot name="body">
                        <x-form.group.date
                            name="entry_date"
                            label="{{ trans('nonprofit::general.entry_date') }}"
                            value="{{ old('entry_date', $journalEntry->entry_date->format('Y-m-d')) }}"
                            required
                        />
                        <x-form.group.text
                            name="description"
                            label="{{ trans('general.description') }}"
                            value="{{ old('description', $journalEntry->description) }}"
                        />
                        <x-form.group.text
                            name="reference"
                            label="{{ trans('nonprofit::general.reference') }}"
                            value="{{ old('reference', $journalEntry->reference) }}"
                        />
                        <x-form.group.select
                            name="currency_code"
                            label="{{ trans('nonprofit::general.currency_code') }}"
                            :options="$currencies"
                            selected="{{ old('currency_code', $journalEntry->currency_code) }}"
                            required
                        />
                        <x-form.group.text
                            name="currency_rate"
                            label="{{ trans('nonprofit::general.currency_rate') }}"
                            value="{{ old('currency_rate', $journalEntry->currency_rate) }}"
                        />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head
                            title="{{ trans('nonprofit::general.lines') }}"
                            description="{{ trans('nonprofit::general.min_two_lines') }}"
                        />
                    </x-slot>
                    <x-slot name="body">
                        <div id="lines-container"></div>
                        <button type="button" class="button button-blue mt-4" id="add-line-btn">
                            {{ trans('nonprofit::general.add_line') }}
                        </button>
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons>
                            <x-form.buttons.cancel route="nonprofit.journal-entries.show" :parameter="$journalEntry->id" />
                            <button type="submit" name="action" value="draft" class="button button-gray">
                                {{ trans('nonprofit::general.save') }}
                            </button>
                            <button type="submit" name="action" value="post" class="button button-green" id="save-post-btn">
                                {{ trans('nonprofit::general.save_post') }}
                            </button>
                        </x-form.buttons>
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>

    @push('scripts')
    <script>
    (function() {
        var lineIndex = 0;
        var accounts = {!! json_encode($accounts->map(fn($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name])) !!};
        var funds = {!! json_encode($funds->map(fn($f) => ['id' => $f->id, 'code' => $f->code, 'name' => $f->name])) !!};
        var programs = {!! json_encode($programs->map(fn($p) => ['id' => $p->id, 'code' => $p->code, 'name' => $p->name])) !!};
        var functionalClasses = {!! json_encode($functionalClasses->map(fn($f) => ['id' => $f->id, 'code' => $f->code, 'name' => $f->name])) !!};

        function buildOptions(items, selectedId) {
            return items.map(function(item) {
                var sel = selectedId == item.id ? ' selected' : '';
                return '<option value="' + item.id + '"' + sel + '>' + item.code + ' - ' + item.name + '</option>';
            }).join('');
        }

        window.addLine = function(data) {
            data = data || {};
            var idx = lineIndex++;
            var container = document.getElementById('lines-container');
            var row = document.createElement('div');
            row.className = 'line-row border rounded p-4 mb-3 bg-gray-50';
            row.id = 'line-' + idx;

            var html = '';
            html += '<div class="flex justify-between items-center mb-3">';
            html += '<span class="font-semibold text-sm">Line ' + (idx + 1) + '</span>';
            html += '<button type="button" class="text-red-500 hover:text-red-700" onclick="document.getElementById(\'line-' + idx + '\').remove()">&times; Remove</button>';
            html += '</div>';
            html += '<div class="grid grid-cols-1 md:grid-cols-4 gap-3">';
            html += '<div><label class="form-label required">Account</label><select name="lines[' + idx + '][chart_of_account_id]" class="form-select" required><option value="">-- Select --</option>' + buildOptions(accounts, data.chart_of_account_id) + '</select></div>';
            html += '<div><label class="form-label">Debit</label><input type="number" step="0.01" min="0" name="lines[' + idx + '][debit_amount]" value="' + (data.debit_amount || '') + '" class="form-input" /></div>';
            html += '<div><label class="form-label">Credit</label><input type="number" step="0.01" min="0" name="lines[' + idx + '][credit_amount]" value="' + (data.credit_amount || '') + '" class="form-input" /></div>';
            html += '<div><label class="form-label">Fund</label><select name="lines[' + idx + '][fund_id]" class="form-select"><option value="">-- Select --</option>' + buildOptions(funds, data.fund_id) + '</select></div>';
            html += '</div>';
            html += '<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">';
            html += '<div><label class="form-label">Program</label><select name="lines[' + idx + '][program_id]" class="form-select"><option value="">-- Select --</option>' + buildOptions(programs, data.program_id) + '</select></div>';
            html += '<div><label class="form-label">Functional Class</label><select name="lines[' + idx + '][functional_class_id]" class="form-select"><option value="">-- Select --</option>' + buildOptions(functionalClasses, data.functional_class_id) + '</select></div>';
            html += '<div><label class="form-label">Description</label><input type="text" name="lines[' + idx + '][description]" value="' + (data.description || '') + '" class="form-input" maxlength="500" /></div>';
            html += '</div>';

            row.innerHTML = html;
            container.appendChild(row);
        };

        document.getElementById('add-line-btn').addEventListener('click', function() { addLine(); });

        document.getElementById('save-post-btn').addEventListener('click', function(e) {
            if (!confirm('{{ trans('nonprofit::general.post_confirmation') }}')) {
                e.preventDefault();
            }
        });

        // Preload existing lines
        var existingLines = {!! json_encode($journalEntry->lines->map(fn($l) => [
            'chart_of_account_id' => $l->chart_of_account_id,
            'debit_amount' => $l->debit_amount,
            'credit_amount' => $l->credit_amount,
            'fund_id' => $l->fund_id,
            'program_id' => $l->program_id,
            'functional_class_id' => $l->functional_class_id,
            'description' => $l->description,
        ])) !!};

        existingLines.forEach(function(line) { addLine(line); });
    })();
    </script>
    @endpush
</x-layouts.admin>
