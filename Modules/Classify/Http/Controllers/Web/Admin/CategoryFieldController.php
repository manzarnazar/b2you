<?php

namespace Modules\Classify\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Modules\Classify\Entities\ClassifyCategoryField;
use Modules\Classify\Services\CategoryFieldService;

class CategoryFieldController extends Controller
{
    public function __construct(protected CategoryFieldService $fieldService) {}

    protected function moduleId()
    {
        return Config::get('module.current_module_id')
            ?? (Config::get('module.current_module_data')['id'] ?? null);
    }

    protected function findModuleCategory($id): Category
    {
        return Category::where('id', $id)
            ->where('module_id', $this->moduleId())
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $moduleId = $this->moduleId();
        $categories = Category::where('module_id', $moduleId)
            ->withCount('classifyFields')
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('position')
            ->orderBy('name')
            ->paginate(config('default_pagination'))
            ->withQueryString();

        return view('classify::admin.category-fields.index', compact('categories'));
    }

    public function show(Request $request, $categoryId)
    {
        $category = $this->findModuleCategory($categoryId);
        $fields = ClassifyCategoryField::where('category_id', $category->id)->ordered()->get();
        $types = ClassifyCategoryField::TYPES;
        $editingId = (int) ($request->get('edit') ?: old('_edit_id'));
        $editing = $editingId ? $fields->firstWhere('id', $editingId) : null;

        return view('classify::admin.category-fields.show', compact('category', 'fields', 'types', 'editing'));
    }

    public function store(Request $request, $categoryId)
    {
        $category = $this->findModuleCategory($categoryId);
        $request->validate([
            'label' => 'required|string|max:191',
            'name' => 'nullable|string|max:191',
            'type' => 'required|in:' . implode(',', ClassifyCategoryField::TYPES),
            'placeholder' => 'nullable|string|max:191',
            'default_value' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0',
            'options' => 'nullable',
        ]);

        $name = $this->fieldService->normalizeSlug($request->name ?: '', $request->label);
        if (ClassifyCategoryField::where('category_id', $category->id)->where('name', $name)->exists()) {
            Toastr::error(translate('Field name already exists for this category') ?: 'Field name already exists for this category');
            return back()->withInput();
        }

        if (in_array($request->type, ClassifyCategoryField::OPTION_TYPES, true)) {
            $options = $this->fieldService->normalizeOptions($request->options, $request->type);
            if (!$options || !count($options)) {
                Toastr::error(translate('Please add at least one option') ?: 'Please add at least one option');
                return back()->withInput();
            }
        }

        $this->fieldService->createField($category->id, $request->all());
        Toastr::success(translate('Field created') ?: 'Field created');
        return back();
    }

    public function update(Request $request, $categoryId, $fieldId)
    {
        $category = $this->findModuleCategory($categoryId);
        $field = ClassifyCategoryField::where('category_id', $category->id)->findOrFail($fieldId);

        $request->validate([
            'label' => 'required|string|max:191',
            'name' => 'nullable|string|max:191',
            'type' => 'required|in:' . implode(',', ClassifyCategoryField::TYPES),
            'placeholder' => 'nullable|string|max:191',
            'default_value' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0',
            'options' => 'nullable',
        ]);

        $name = $this->fieldService->normalizeSlug($request->name ?: $field->name, $request->label);
        if (
            ClassifyCategoryField::where('category_id', $category->id)
                ->where('name', $name)
                ->where('id', '!=', $field->id)
                ->exists()
        ) {
            Toastr::error(translate('Field name already exists for this category') ?: 'Field name already exists for this category');
            return back()->withInput();
        }

        if (in_array($request->type, ClassifyCategoryField::OPTION_TYPES, true)) {
            $options = $this->fieldService->normalizeOptions($request->options, $request->type);
            if (!$options || !count($options)) {
                Toastr::error(translate('Please add at least one option') ?: 'Please add at least one option');
                return back()->withInput();
            }
        }

        $this->fieldService->updateField($field, $request->all());
        Toastr::success(translate('Field updated') ?: 'Field updated');
        return back();
    }

    public function destroy($categoryId, $fieldId)
    {
        $category = $this->findModuleCategory($categoryId);
        $field = ClassifyCategoryField::where('category_id', $category->id)->findOrFail($fieldId);
        $field->delete();
        Toastr::success(translate('Field deleted') ?: 'Field deleted');
        return back();
    }

    public function toggle($categoryId, $fieldId)
    {
        $category = $this->findModuleCategory($categoryId);
        $field = ClassifyCategoryField::where('category_id', $category->id)->findOrFail($fieldId);
        $field->update(['is_active' => !$field->is_active]);
        Toastr::success(translate('Status updated') ?: 'Status updated');
        return back();
    }

    public function move(Request $request, $categoryId, $fieldId)
    {
        $category = $this->findModuleCategory($categoryId);
        $fields = ClassifyCategoryField::where('category_id', $category->id)->ordered()->get()->values();
        $index = $fields->search(fn ($f) => (int) $f->id === (int) $fieldId);
        if ($index === false) {
            return back();
        }

        $direction = $request->get('direction', 'up');
        $swapWith = $direction === 'down' ? $index + 1 : $index - 1;
        if ($swapWith < 0 || $swapWith >= $fields->count()) {
            return back();
        }

        $ordered = $fields->pluck('id')->all();
        [$ordered[$index], $ordered[$swapWith]] = [$ordered[$swapWith], $ordered[$index]];
        $this->fieldService->reorder($category->id, $ordered);

        Toastr::success(translate('Order updated') ?: 'Order updated');
        return back();
    }
}
