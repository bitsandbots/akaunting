<?php

namespace Modules\Nonprofit\Http\Controllers;

use App\Abstracts\Http\Controller;
use Modules\Nonprofit\Http\Requests\FundRequest;
use Modules\Nonprofit\Models\Fund;

class Funds extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $funds = Fund::with('parent')
            ->where('company_id', company_id())
            ->collect(['code' => 'asc']);

        return $this->response('nonprofit::funds.index', compact('funds'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parents = Fund::where('company_id', company_id())
            ->enabled()
            ->orderBy('code')
            ->pluck('name', 'id');

        $types = $this->getFundTypes();

        return view('nonprofit::funds.create', compact('parents', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  FundRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FundRequest $request)
    {
        Fund::create([
            'company_id'         => company_id(),
            'code'               => $request->code,
            'name'               => $request->name,
            'type'               => $request->type,
            'restriction_detail'  => $request->restriction_detail,
            'description'        => $request->description,
            'parent_id'          => $request->parent_id,
            'enabled'            => $request->boolean('enabled', true),
        ]);

        $message = trans('nonprofit::general.created', ['type' => trans_choice('nonprofit::general.fund', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.funds.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Fund  $fund
     * @return \Illuminate\Http\Response
     */
    public function edit(Fund $fund)
    {
        $parents = Fund::where('company_id', company_id())
            ->where('id', '!=', $fund->id)
            ->enabled()
            ->orderBy('code')
            ->pluck('name', 'id');

        $types = $this->getFundTypes();

        return view('nonprofit::funds.edit', compact('fund', 'parents', 'types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  FundRequest  $request
     * @param  Fund  $fund
     * @return \Illuminate\Http\Response
     */
    public function update(FundRequest $request, Fund $fund)
    {
        $fund->update([
            'code'               => $request->code,
            'name'               => $request->name,
            'type'               => $request->type,
            'restriction_detail'  => $request->restriction_detail,
            'description'        => $request->description,
            'parent_id'          => $request->parent_id,
            'enabled'            => $request->boolean('enabled', true),
        ]);

        $message = trans('nonprofit::general.updated', ['type' => trans_choice('nonprofit::general.fund', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.funds.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Fund  $fund
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fund $fund)
    {
        if ($fund->journalEntryLines()->exists()) {
            flash(trans('nonprofit::general.cannot_delete_system_row'))->error();
            return redirect()->route('nonprofit.funds.index');
        }

        $fund->delete();

        $message = trans('nonprofit::general.deleted', ['type' => trans_choice('nonprofit::general.fund', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.funds.index');
    }

    /**
     * Get fund types for dropdowns.
     */
    protected function getFundTypes(): array
    {
        return [
            'without_donor_restrictions' => trans('nonprofit::general.without_donor_restrictions'),
            'with_donor_restrictions'    => trans('nonprofit::general.with_donor_restrictions'),
        ];
    }
}
