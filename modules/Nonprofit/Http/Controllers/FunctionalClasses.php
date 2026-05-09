<?php

namespace Modules\Nonprofit\Http\Controllers;

use App\Abstracts\Http\Controller;
use Modules\Nonprofit\Http\Requests\FunctionalClassRequest;
use Modules\Nonprofit\Models\FunctionalClass;

class FunctionalClasses extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $classes = FunctionalClass::where('company_id', company_id())
            ->collect(['code' => 'asc']);

        return $this->response('nonprofit::functional_classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parentClasses = $this->getParentClasses();

        return view('nonprofit::functional_classes.create', compact('parentClasses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  FunctionalClassRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FunctionalClassRequest $request)
    {
        FunctionalClass::create([
            'company_id'   => company_id(),
            'code'         => $request->code,
            'name'         => $request->name,
            'parent_class'  => $request->parent_class,
            'is_system'    => $request->boolean('is_system', false),
            'enabled'      => $request->boolean('enabled', true),
        ]);

        $message = trans('nonprofit::general.created', ['type' => trans_choice('nonprofit::general.functional_class', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.functional-classes.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  FunctionalClass  $functionalClass
     * @return \Illuminate\Http\Response
     */
    public function edit(FunctionalClass $functionalClass)
    {
        $parentClasses = $this->getParentClasses();

        return view('nonprofit::functional_classes.edit', compact('functionalClass', 'parentClasses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  FunctionalClassRequest  $request
     * @param  FunctionalClass  $functionalClass
     * @return \Illuminate\Http\Response
     */
    public function update(FunctionalClassRequest $request, FunctionalClass $functionalClass)
    {
        $functionalClass->update([
            'code'         => $request->code,
            'name'         => $request->name,
            'parent_class'  => $request->parent_class,
            'is_system'    => $request->boolean('is_system', false),
            'enabled'      => $request->boolean('enabled', true),
        ]);

        $message = trans('nonprofit::general.updated', ['type' => trans_choice('nonprofit::general.functional_class', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.functional-classes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  FunctionalClass  $functionalClass
     * @return \Illuminate\Http\Response
     */
    public function destroy(FunctionalClass $functionalClass)
    {
        if ($functionalClass->is_system) {
            flash(trans('nonprofit::general.cannot_delete_system_row'))->error();
            return redirect()->route('nonprofit.functional-classes.index');
        }

        if ($functionalClass->journalEntryLines()->exists()) {
            flash(trans('nonprofit::general.cannot_delete_system_row'))->error();
            return redirect()->route('nonprofit.functional-classes.index');
        }

        $functionalClass->delete();

        $message = trans('nonprofit::general.deleted', ['type' => trans_choice('nonprofit::general.functional_class', 1)]);

        flash($message)->success();

        return redirect()->route('nonprofit.functional-classes.index');
    }

    /**
     * Get parent class options for dropdowns.
     */
    protected function getParentClasses(): array
    {
        return [
            'program_services'   => trans('nonprofit::general.program_services'),
            'management_general' => trans('nonprofit::general.management_general'),
            'fundraising'        => trans('nonprofit::general.fundraising'),
        ];
    }
}
