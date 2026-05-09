<?php

namespace Modules\Nonprofit\Http\Controllers\Settings;

use App\Abstracts\Http\Controller;
use App\Models\Setting\Category;
use Modules\Nonprofit\Models\ChartOfAccount;

class AccountMappings extends Controller
{
    /**
     * Show the form for editing account mappings.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $incomeCategories = Category::where('company_id', company_id())
            ->type('income')
            ->enabled()
            ->orderBy('name')
            ->get();

        $expenseCategories = Category::where('company_id', company_id())
            ->type('expense')
            ->enabled()
            ->orderBy('name')
            ->get();

        $accounts = ChartOfAccount::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->get();

        // Load existing mappings from settings
        $mappings = setting('nonprofit.account_mappings', []);

        return view('nonprofit::settings.account_mappings', compact(
            'incomeCategories', 'expenseCategories', 'accounts', 'mappings'
        ));
    }

    /**
     * Update the account mappings.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $mappings = request()->input('mappings', []);

        setting(['nonprofit.account_mappings' => $mappings]);

        setting()->save();

        flash(trans('nonprofit::general.updated', ['type' => trans('nonprofit::general.account_mappings')]))->success();

        return redirect()->route('nonprofit.settings.account-mappings.edit');
    }
}
