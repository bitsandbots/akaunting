<?php

namespace Modules\Nonprofit\Http\Controllers;

use App\Abstracts\Http\Controller;
use Modules\Nonprofit\Http\Requests\CoaRequest;
use Modules\Nonprofit\Models\ChartOfAccount;

class Coa extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accounts = ChartOfAccount::with('parent')
            ->where('company_id', company_id())
            ->collect(['code' => 'asc']);

        return $this->response('nonprofit::coa.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parents = ChartOfAccount::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->pluck('name', 'id');

        $types = $this->getAccountTypes();

        return view('nonprofit::coa.create', compact('parents', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CoaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CoaRequest $request)
    {
        ChartOfAccount::create([
            'company_id'  => company_id(),
            'code'        => $request->code,
            'name'        => $request->name,
            'type'        => $request->type,
            'parent_id'   => $request->parent_id,
            'description' => $request->description,
            'enabled'     => $request->boolean('enabled', true),
            'created_by'  => user_id(),
        ]);

        $message = trans('nonprofit::general.created', ['type' => trans_choice('nonprofit::general.coa', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.coa.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ChartOfAccount  $coa
     * @return \Illuminate\Http\Response
     */
    public function edit(ChartOfAccount $coa)
    {
        $parents = ChartOfAccount::where('company_id', company_id())
            ->where('id', '!=', $coa->id)
            ->enabled()
            ->orderBy('code')
            ->pluck('name', 'id');

        $types = $this->getAccountTypes();

        return view('nonprofit::coa.edit', compact('coa', 'parents', 'types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CoaRequest  $request
     * @param  ChartOfAccount  $coa
     * @return \Illuminate\Http\Response
     */
    public function update(CoaRequest $request, ChartOfAccount $coa)
    {
        $coa->update([
            'code'        => $request->code,
            'name'        => $request->name,
            'type'        => $request->type,
            'parent_id'   => $request->parent_id,
            'description' => $request->description,
            'enabled'     => $request->boolean('enabled', true),
            'updated_by'  => user_id(),
        ]);

        $message = trans('nonprofit::general.updated', ['type' => trans_choice('nonprofit::general.coa', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.coa.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ChartOfAccount  $coa
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChartOfAccount $coa)
    {
        if ($coa->journalEntryLines()->exists()) {
            flash(trans('nonprofit::general.cannot_delete_system_row'))->error();
            return redirect()->route('nonprofit.coa.index');
        }

        $coa->delete();

        $message = trans('nonprofit::general.deleted', ['type' => trans_choice('nonprofit::general.coa', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.coa.index');
    }

    /**
     * Get account types for dropdowns.
     */
    protected function getAccountTypes(): array
    {
        return [
            'asset'    => trans('nonprofit::general.asset'),
            'liability' => trans('nonprofit::general.liability'),
            'equity'   => trans('nonprofit::general.equity'),
            'revenue'  => trans('nonprofit::general.revenue'),
            'expense'  => trans('nonprofit::general.expense'),
        ];
    }
}
