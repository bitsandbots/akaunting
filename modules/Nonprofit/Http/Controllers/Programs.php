<?php

namespace Modules\Nonprofit\Http\Controllers;

use App\Abstracts\Http\Controller;
use Modules\Nonprofit\Http\Requests\ProgramRequest;
use Modules\Nonprofit\Models\Program;

class Programs extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $programs = Program::where('company_id', company_id())
            ->collect(['code' => 'asc']);

        return $this->response('nonprofit::programs.index', compact('programs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('nonprofit::programs.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ProgramRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProgramRequest $request)
    {
        Program::create([
            'company_id'  => company_id(),
            'code'        => $request->code,
            'name'        => $request->name,
            'description' => $request->description,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'enabled'     => $request->boolean('enabled', true),
        ]);

        $message = trans('nonprofit::general.created', ['type' => trans_choice('nonprofit::general.program', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.programs.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Program  $program
     * @return \Illuminate\Http\Response
     */
    public function edit(Program $program)
    {
        return view('nonprofit::programs.edit', compact('program'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ProgramRequest  $request
     * @param  Program  $program
     * @return \Illuminate\Http\Response
     */
    public function update(ProgramRequest $request, Program $program)
    {
        $program->update([
            'code'        => $request->code,
            'name'        => $request->name,
            'description' => $request->description,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'enabled'     => $request->boolean('enabled', true),
        ]);

        $message = trans('nonprofit::general.updated', ['type' => trans_choice('nonprofit::general.program', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.programs.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Program  $program
     * @return \Illuminate\Http\Response
     */
    public function destroy(Program $program)
    {
        if ($program->journalEntryLines()->exists()) {
            flash(trans('nonprofit::general.cannot_delete_system_row'))->error();
            return redirect()->route('nonprofit.programs.index');
        }

        $program->delete();

        $message = trans('nonprofit::general.deleted', ['type' => trans_choice('nonprofit::general.program', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.programs.index');
    }
}
