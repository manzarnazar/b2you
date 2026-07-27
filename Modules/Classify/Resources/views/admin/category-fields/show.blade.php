@extends('layouts.admin.app')

@section('title', (translate('Category Fields') ?: 'Category Fields') . ' — ' . $category->name)

@section('content')
@php
    $formField = $editing ?? (old('_edit_id') ? $fields->firstWhere('id', (int) old('_edit_id')) : null);
@endphp
<div class="content container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-header-title mb-1">{{ $category->name }}</h1>
            <p class="text-muted mb-0">
                {{ $category->position == 0 ? (translate('Category') ?: 'Category') : (translate('Sub Category') ?: 'Sub Category') }}
                · {{ translate('Custom fields') ?: 'Custom fields' }}
            </p>
        </div>
        <a href="{{ route('admin.classify.category-fields.index') }}" class="btn btn-secondary">{{ translate('Back') }}</a>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $formField ? (translate('Edit field') ?: 'Edit field') : (translate('Add field') ?: 'Add field') }}</h5>
                </div>
                <div class="card-body">
                    <form method="post"
                          action="{{ $formField
                            ? route('admin.classify.category-fields.update', [$category->id, $formField->id])
                            : route('admin.classify.category-fields.store', $category->id) }}">
                        @csrf
                        @if($formField)
                            @method('PUT')
                            <input type="hidden" name="_edit_id" value="{{ $formField->id }}">
                        @endif

                        <div class="form-group">
                            <label>{{ translate('Field Label') ?: 'Field Label' }} *</label>
                            <input type="text" name="label" class="form-control" required
                                   value="{{ old('label', $formField->label ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Field Name (slug)') ?: 'Field Name (slug)' }}</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $formField->name ?? '') }}"
                                   placeholder="{{ translate('Auto from label if empty') ?: 'Auto from label if empty' }}">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Field Type') ?: 'Field Type' }} *</label>
                            <select name="type" id="classify_field_type" class="form-control" required>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" @selected(old('type', $formField->type ?? 'text') === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Placeholder') }}</label>
                            <input type="text" name="placeholder" class="form-control"
                                   value="{{ old('placeholder', $formField->placeholder ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Default Value') ?: 'Default Value' }}</label>
                            <input type="text" name="default_value" class="form-control"
                                   value="{{ old('default_value', $formField->default_value ?? '') }}">
                        </div>
                        <div class="form-group" id="classify_field_options_wrap">
                            <label>{{ translate('Options') ?: 'Options' }}</label>
                            <textarea name="options" class="form-control" rows="4"
                                      placeholder="{{ translate('One option per line (label or label|value)') ?: 'One option per line (label or label|value)' }}">@php
$opts = old('options');
if ($opts === null && $formField) {
    $opts = collect($formField->options ?: [])->map(function ($o) {
        $label = $o['label'] ?? '';
        $value = $o['value'] ?? '';
        return ($value && $value !== \Illuminate\Support\Str::slug($label, '_')) ? ($label.'|'.$value) : $label;
    })->implode("\n");
}
echo e($opts);
@endphp</textarea>
                            <small class="text-muted">{{ translate('Required for Select, Radio, and Checkbox with choices') ?: 'Required for Select, Radio, and Checkbox with choices' }}</small>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Sort Order') ?: 'Sort Order' }}</label>
                            <input type="number" name="sort_order" class="form-control"
                                   value="{{ old('sort_order', $formField->sort_order ?? '') }}">
                        </div>
                        <div class="form-check mb-2">
                            <input type="hidden" name="is_required" value="0">
                            <input type="checkbox" class="form-check-input" name="is_required" value="1" id="is_required"
                                {{ old('is_required', $formField->is_required ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required">{{ translate('Required') }}</label>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active"
                                {{ old('is_active', $formField->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ translate('Active') }}</label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            {{ $formField ? (translate('Update') ?: 'Update') : (translate('Add field') ?: 'Add field') }}
                        </button>
                        @if($formField)
                            <a href="{{ route('admin.classify.category-fields.show', $category->id) }}" class="btn btn-white">{{ translate('Cancel') }}</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('Fields') ?: 'Fields' }} ({{ $fields->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th>{{ translate('Order') }}</th>
                                <th>{{ translate('Label') }}</th>
                                <th>{{ translate('Type') }}</th>
                                <th>{{ translate('Required') }}</th>
                                <th>{{ translate('Active') }}</th>
                                <th class="text-center">{{ translate('Action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($fields as $field)
                                <tr>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            <a class="btn btn-outline-secondary btn-sm py-0"
                                               href="{{ route('admin.classify.category-fields.move', [$category->id, $field->id, 'direction' => 'up']) }}">↑</a>
                                            <a class="btn btn-outline-secondary btn-sm py-0"
                                               href="{{ route('admin.classify.category-fields.move', [$category->id, $field->id, 'direction' => 'down']) }}">↓</a>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $field->label }}</strong>
                                        <div class="text-muted small">{{ $field->name }}</div>
                                    </td>
                                    <td>{{ ucfirst($field->type) }}</td>
                                    <td>{{ $field->is_required ? translate('Yes') : translate('No') }}</td>
                                    <td>
                                        <a href="{{ route('admin.classify.category-fields.toggle', [$category->id, $field->id]) }}"
                                           class="badge badge-{{ $field->is_active ? 'success' : 'secondary' }}">
                                            {{ $field->is_active ? translate('Active') : translate('Inactive') }}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('admin.classify.category-fields.show', [$category->id, 'edit' => $field->id]) }}">
                                            {{ translate('Edit') }}
                                        </a>
                                        <form class="d-inline" method="post"
                                              action="{{ route('admin.classify.category-fields.destroy', [$category->id, $field->id]) }}"
                                              onsubmit="return confirm('{{ translate('Delete this field?') ?: 'Delete this field?' }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">{{ translate('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">{{ translate('No custom fields yet') ?: 'No custom fields yet' }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
(function () {
    const typeSelect = document.getElementById('classify_field_type');
    const optionsWrap = document.getElementById('classify_field_options_wrap');
    if (!typeSelect || !optionsWrap) return;
    function toggle() {
        const needs = ['select', 'radio', 'checkbox'].includes(typeSelect.value);
        optionsWrap.style.display = needs ? '' : 'none';
    }
    typeSelect.addEventListener('change', toggle);
    toggle();
})();
</script>
@endpush
