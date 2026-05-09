<?php

namespace Modules\Nonprofit\Http\Controllers\Settings;

use App\Abstracts\Http\Controller;
use Modules\Nonprofit\Models\FunctionalClass;
use Modules\Nonprofit\Models\Fund;
use Modules\Nonprofit\Models\Program;

class DimensionDefaults extends Controller
{
    /**
     * Show the form for editing dimension defaults.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $funds = Fund::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->pluck('name', 'id');

        $programs = Program::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->pluck('name', 'id');

        $functionalClasses = FunctionalClass::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->pluck('name', 'id');

        $defaults = setting('nonprofit.dimension_defaults', []);

        return view('nonprofit::settings.dimension_defaults', compact(
            'funds', 'programs', 'functionalClasses', 'defaults'
        ));
    }

    /**
     * Update the dimension defaults.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $company_id = company_id();

        $data = request()->validate([
            'default_fund_id'            => 'nullable|exists:funds,id,company_id,' . $company_id,
            'default_program_id'         => 'nullable|exists:programs,id,company_id,' . $company_id,
            'default_functional_class_id' => 'nullable|exists:functional_classes,id,company_id,' . $company_id,
        ]);

        setting(['nonprofit.dimension_defaults' => $data]);

        setting()->save();

        flash(trans('nonprofit::general.updated', ['type' => trans('nonprofit::general.dimension_defaults')]))->success();

        return redirect()->route('nonprofit.settings.dimension-defaults.edit');
    }
}
