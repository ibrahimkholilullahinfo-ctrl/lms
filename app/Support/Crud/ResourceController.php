<?php

namespace App\Support\Crud;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Schema driven CRUD engine.
 *
 * Every resource declares its fields once; the listing table, create/edit
 * forms, validation and persistence are derived from that definition.
 * Tenant isolation comes from the model global scope, so a record from
 * another institute resolves to 404.
 */
abstract class ResourceController extends \App\Http\Controllers\Controller
{
    /** @var class-string<Model> */
    protected string $model;

    protected string $route;

    protected string $title;

    protected string $description = '';

    protected array $with = [];

    protected array $searchable = [];

    protected string $orderBy = 'created_at';

    protected string $orderDir = 'desc';

    protected int $perPage = 15;

    protected string $viewPrefix = 'crud';

    protected bool $allowCreate = true;

    protected bool $allowDelete = true;

    /** @return array<int, array<string, mixed>> */
    abstract public function fields(?Model $record = null): array;

    /** @return array<int, array<string, mixed>> */
    public function columns(): array
    {
        return collect($this->fields())
            ->reject(fn ($f) => ($f['table'] ?? true) === false)
            ->map(fn ($f) => [
                'name' => $f['name'],
                'label' => $f['label'],
                'type' => $f['type'] ?? 'text',
                'options' => $f['options'] ?? [],
                'relation' => $f['relation'] ?? null,
                'attribute' => $f['attribute'] ?? null,
                'money' => $f['money'] ?? false,
            ])->values()->all();
    }

    protected function baseQuery(): Builder
    {
        return $this->model::query()->with($this->with);
    }

    /** Extra where clauses from the query string. @return array<string, mixed> */
    protected function filters(Request $request): array
    {
        return [];
    }

    /** Additional per-row actions: [['label'=>, 'icon'=>, 'url'=>, 'class'=>]]. */
    protected function rowActions(Model $record): array
    {
        return [];
    }

    public function index(Request $request): View
    {
        $query = $this->baseQuery();
        $search = trim((string) $request->query('q', ''));

        if ($search !== '' && $this->searchable !== []) {
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->searchable as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        foreach ($this->filters($request) as $column => $value) {
            if ($value !== null && $value !== '') {
                $query->where($column, $value);
            }
        }

        $records = $query->orderBy($this->orderBy, $this->orderDir)
            ->paginate($this->perPage)->withQueryString();

        return view("{$this->viewPrefix}.index", [
            'title' => $this->title,
            'description' => $this->description,
            'route' => $this->route,
            'columns' => $this->columns(),
            'records' => $records,
            'search' => $search,
            'allowCreate' => $this->allowCreate,
            'allowDelete' => $this->allowDelete,
            'filters' => $this->filterOptions($request),
            'rowActions' => fn (Model $record) => $this->rowActions($record),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    protected function filterOptions(Request $request): array
    {
        return [];
    }

    public function create(): View
    {
        abort_unless($this->allowCreate, 403);

        $record = new $this->model;

        return view("{$this->viewPrefix}.form", [
            'title' => $this->title,
            'description' => $this->description,
            'route' => $this->route,
            'record' => $record,
            'fields' => $this->fields($record),
            'action' => route("{$this->route}.store"),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->allowCreate, 403);

        $data = $this->validated($request);
        $record = new $this->model;
        $this->fill($record, $data);
        $record->save();
        $this->afterSave($record, $request);

        ActivityLog::record('created', $record, __('Created :name', ['name' => __($this->title)]).': '.$this->labelFor($record));

        return redirect()->route("{$this->route}.index")
            ->with('success', __(':name has been created.', ['name' => __($this->title)]));
    }

    public function edit(string $record): View
    {
        $model = $this->findRecord($record);

        return view("{$this->viewPrefix}.form", [
            'title' => $this->title,
            'description' => $this->description,
            'route' => $this->route,
            'record' => $model,
            'fields' => $this->fields($model),
            'action' => route("{$this->route}.update", $model),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, string $record): RedirectResponse
    {
        $model = $this->findRecord($record);
        $data = $this->validated($request, $model);
        $this->fill($model, $data);
        $model->save();
        $this->afterSave($model, $request);

        ActivityLog::record('updated', $model, __('Updated :name', ['name' => __($this->title)]).': '.$this->labelFor($model));

        return redirect()->route("{$this->route}.index")
            ->with('success', __(':name has been updated.', ['name' => __($this->title)]));
    }

    public function destroy(string $record): RedirectResponse
    {
        abort_unless($this->allowDelete, 403);

        $model = $this->findRecord($record);
        ActivityLog::record('deleted', $model, __('Deleted :name', ['name' => __($this->title)]).': '.$this->labelFor($model));
        $model->delete();

        return back()->with('success', __(':name has been moved to the recycle bin.', ['name' => __($this->title)]));
    }

    protected function findRecord(string $record): Model
    {
        /** @var Model $model */
        $model = $this->model::query()->with($this->with)->findOrFail($record);

        return $model;
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?Model $record = null): array
    {
        $rules = [];
        $attributes = [];

        foreach ($this->fields($record) as $field) {
            if (($field['type'] ?? 'text') === 'hidden' && empty($field['rules'])) {
                continue;
            }

            $rules[$field['name']] = $field['rules'] ?? (($field['required'] ?? false) ? ['required'] : ['nullable']);
            $attributes[$field['name']] = __($field['label']);
        }

        $validator = validator($request->all(), $rules, [], $attributes);
        $validator->validate();

        return $validator->validated();
    }

    protected function fill(Model $record, array $data): void
    {
        foreach ($this->fields($record) as $field) {
            $name = $field['name'];
            $type = $field['type'] ?? 'text';

            if ($type === 'file') {
                if (request()->hasFile($name)) {
                    $record->{$name} = request()->file($name)->store($field['directory'] ?? 'uploads', 'public');
                }

                continue;
            }

            if ($type === 'checkbox' && ! array_key_exists($name, $data)) {
                $record->{$name} = false;

                continue;
            }

            if (! array_key_exists($name, $data)) {
                continue;
            }

            $value = $data[$name];

            if ($type === 'checkbox') {
                $value = (bool) $value;
            }

            if ($type === 'password') {
                if (blank($value)) {
                    continue;
                }

                $value = bcrypt($value);
            }

            if ($type === 'number' && $value !== null && $value !== '') {
                $value = $value + 0;
            }

            $record->{$name} = is_string($value) ? (trim($value) === '' ? null : $value) : $value;
        }
    }

    /** Hook for resource specific persistence after save. */
    protected function afterSave(Model $record, Request $request): void
    {
        //
    }

    protected function labelFor(Model $record): string
    {
        foreach (['title', 'name', 'label_en', 'certificate_no', 'slug'] as $attribute) {
            if (! empty($record->{$attribute})) {
                return Str::limit((string) $record->{$attribute}, 60);
            }
        }

        return '#'.$record->getKey();
    }
}
