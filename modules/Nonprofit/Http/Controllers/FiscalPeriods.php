<?php

namespace Modules\Nonprofit\Http\Controllers;

use App\Abstracts\Http\Controller;
use Modules\Nonprofit\Http\Requests\FiscalPeriodRequest;
use Modules\Nonprofit\Models\FiscalPeriod;

class FiscalPeriods extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $periods = FiscalPeriod::where('company_id', company_id())
            ->collect(['start_date' => 'desc']);

        return $this->response('nonprofit::fiscal_periods.index', compact('periods'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $statuses = [
            'open'   => trans('nonprofit::general.open'),
            'closed' => trans('nonprofit::general.closed'),
        ];

        return view('nonprofit::fiscal_periods.create', compact('statuses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  FiscalPeriodRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FiscalPeriodRequest $request)
    {
        FiscalPeriod::create([
            'company_id' => company_id(),
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'status'     => $request->status ?? 'open',
        ]);

        $message = trans('nonprofit::general.created', ['type' => trans_choice('nonprofit::general.fiscal_period', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.fiscal-periods.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  FiscalPeriod  $fiscalPeriod
     * @return \Illuminate\Http\Response
     */
    public function edit(FiscalPeriod $fiscalPeriod)
    {
        $statuses = [
            'open'   => trans('nonprofit::general.open'),
            'closed' => trans('nonprofit::general.closed'),
        ];

        return view('nonprofit::fiscal_periods.edit', compact('fiscalPeriod', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  FiscalPeriodRequest  $request
     * @param  FiscalPeriod  $fiscalPeriod
     * @return \Illuminate\Http\Response
     */
    public function update(FiscalPeriodRequest $request, FiscalPeriod $fiscalPeriod)
    {
        $data = [
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ];

        if ($request->has('status')) {
            $data['status'] = $request->status;

            if ($request->status === 'closed' && $fiscalPeriod->status === 'open') {
                $data['closed_at'] = now();
                $data['closed_by'] = user_id();
            }
        }

        $fiscalPeriod->update($data);

        $message = trans('nonprofit::general.updated', ['type' => trans_choice('nonprofit::general.fiscal_period', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.fiscal-periods.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  FiscalPeriod  $fiscalPeriod
     * @return \Illuminate\Http\Response
     */
    public function destroy(FiscalPeriod $fiscalPeriod)
    {
        $fiscalPeriod->delete();

        $message = trans('nonprofit::general.deleted', ['type' => trans_choice('nonprofit::general.fiscal_period', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.fiscal-periods.index');
    }
}
