<?php

namespace TFD\Redirects\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\CP\Column;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use TFD\Redirects\Modules\Redirect\Redirect;
use TFD\Redirects\Modules\Redirect\RedirectBlueprint;
use TFD\Redirects\Modules\Redirect\RedirectRepository;
use Statamic\Support\Str;

class RedirectsController extends CpController
{
    protected $repository;

    public function __construct(RedirectRepository $redirectRepository)
    {
        $this->repository = $redirectRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('view redirects');

        $columns = $this->getColumns();
        $redirects = $this->repository->all()->map(function ($redirect) {
            $edit_url = cp_route('statamic-redirects.edit', ['id' => $redirect['id']]);
            $redirect['edit_url'] = $edit_url;

            $delete_url = cp_route('statamic-redirects.delete', ['id' => $redirect['id']]);
            $redirect['delete_url'] = $delete_url;

            $red = new Redirect($redirect, $this->repository);
            $redirect['target'] = $red->generateTargetUrl();

            if (isset($redirect['notes'])) {
                $redirect['notes'] = Str::substr($redirect['notes'], 0, 120);
            }

            return $redirect;
        });

        $user = User::current();

        return view('statamic-redirects::redirects.index', [
            'title' => __('statamic-redirects::default.headlines.index'),
            'redirects' => $redirects,
            'columns' => $columns,
            'canCreate' => $user->can('create redirects'),
            'canDelete' => $user->can('delete redirects'),
        ]);
    }

    // Create View
    public function create()
    {
        $this->authorize('create redirects');

        $data = [];

        $blueprint = RedirectBlueprint::get();
        $fields = $blueprint->fields()->addValues($data)->preProcess();

        return view('statamic-redirects::redirects.create', [
            'title' => __('statamic-redirects::default.headlines.create'),
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => $fields->meta(),
            'values' => $fields->values(),
        ]);
    }

    // Store new redirect
    public function store(Request $request)
    {
        $this->authorize('create redirects');

        $fields = RedirectBlueprint::get()->fields()->addValues($request->all());
        $fields->validate();
        $values = $fields->process()->values()->toArray();
        
        $id = $this->repository->create($values);

        return response()->json([
            'id' => $id,
        ]);
    }

    // Edit view
    public function edit(Request $request, string $id)
    {
        $this->authorize('create redirects');

        if (!$this->repository->exists($id)) {
            return redirect(route('statamic.cp.statamic-redirects.redirects.index'));
        }

        $redirect = $this->repository->get($id);

        $blueprint = RedirectBlueprint::get();
        $fields = $blueprint->fields()->addValues($redirect)->preProcess();

        return view('statamic-redirects::redirects.edit', [
            'title' => __('statamic-redirects::default.headlines.edit'),
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => $fields->meta(),
            'values' => $fields->values(),
            'id' => $id,
        ]);
    }

    // Update existing redirect
    public function update(Request $request, string $id)
    {
        $this->authorize('create redirects');
        
        if (!$this->repository->exists($id)) {
            return redirect(route('statamic.cp.statamic-redirects.redirects.index'));
        }
        
        $fields = RedirectBlueprint::get()->fields()->addValues($request->all());
        $fields->validate();
        $values = $fields->process()->values()->toArray();

        $this->repository->update($id, $values);
    }

    public function delete(Request $request, string $id)
    {
        $this->authorize('delete redirects');

        $this->repository->delete($id);
    }

    public function getColumns()
    {
        return [
            Column::make('source')->label(__('statamic-redirects::default.columns.source')),
            Column::make('target')->label(__('statamic-redirects::default.columns.target')),
            Column::make('active')->label(__('statamic-redirects::default.columns.active')),
            Column::make('hits')->label(__('statamic-redirects::default.columns.hits')),
            Column::make('notes')->label(__('statamic-redirects::default.columns.notes')),
        ];
    }
}
