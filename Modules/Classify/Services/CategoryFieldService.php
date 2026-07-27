<?php

namespace Modules\Classify\Services;

use App\CentralLogics\Helpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Classify\Entities\ClassifyCategoryField;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyListingFieldValue;

class CategoryFieldService
{
    public function resolveFields(?int $categoryId, ?int $subCategoryId = null, bool $activeOnly = true): Collection
    {
        $fields = collect();

        if ($categoryId) {
            $fields = $fields->concat($this->fieldsForCategory($categoryId, $activeOnly));
        }

        if ($subCategoryId) {
            $fields = $fields->concat($this->fieldsForCategory($subCategoryId, $activeOnly));
        }

        return $fields->values();
    }

    public function fieldsForCategory(int $categoryId, bool $activeOnly = true): Collection
    {
        $query = ClassifyCategoryField::where('category_id', $categoryId)->ordered();
        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    public function normalizeSlug(string $name, string $label = ''): string
    {
        $slug = Str::slug($name ?: $label, '_');
        return $slug !== '' ? $slug : 'field_' . Str::random(6);
    }

    public function normalizeOptions($options, string $type): ?array
    {
        if (!in_array($type, ClassifyCategoryField::OPTION_TYPES, true)) {
            return null;
        }

        if (is_string($options)) {
            $lines = preg_split("/\r\n|\n|\r/", $options) ?: [];
            $parsed = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                if (str_contains($line, '|')) {
                    [$label, $value] = array_map('trim', explode('|', $line, 2));
                    $parsed[] = ['label' => $label, 'value' => $value];
                } else {
                    $parsed[] = $line;
                }
            }
            $options = $parsed;
        }

        if (!is_array($options)) {
            return [];
        }

        $normalized = [];
        foreach ($options as $option) {
            if (is_array($option)) {
                $label = trim((string) ($option['label'] ?? $option['value'] ?? ''));
                $value = trim((string) ($option['value'] ?? $option['label'] ?? ''));
            } else {
                $label = trim((string) $option);
                $value = Str::slug($label, '_') ?: $label;
            }

            if ($label === '' && $value === '') {
                continue;
            }

            $normalized[] = [
                'label' => $label !== '' ? $label : $value,
                'value' => $value !== '' ? $value : Str::slug($label, '_'),
            ];
        }

        return $normalized;
    }

    public function createField(int $categoryId, array $data): ClassifyCategoryField
    {
        $type = $data['type'] ?? 'text';
        $name = $this->normalizeSlug($data['name'] ?? '', $data['label'] ?? '');
        $maxSort = (int) ClassifyCategoryField::where('category_id', $categoryId)->max('sort_order');

        return ClassifyCategoryField::create([
            'category_id' => $categoryId,
            'label' => $data['label'],
            'name' => $name,
            'type' => $type,
            'placeholder' => $data['placeholder'] ?? null,
            'default_value' => $data['default_value'] ?? null,
            'is_required' => !empty($data['is_required']) && (string) $data['is_required'] !== '0',
            'is_active' => !array_key_exists('is_active', $data) || (!empty($data['is_active']) && (string) $data['is_active'] !== '0'),
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : ($maxSort + 1),
            'options' => $this->normalizeOptions($data['options'] ?? null, $type),
        ]);
    }

    public function updateField(ClassifyCategoryField $field, array $data): ClassifyCategoryField
    {
        $type = $data['type'] ?? $field->type;
        $name = $this->normalizeSlug($data['name'] ?? $field->name, $data['label'] ?? $field->label);

        $field->update([
            'label' => $data['label'] ?? $field->label,
            'name' => $name,
            'type' => $type,
            'placeholder' => $data['placeholder'] ?? null,
            'default_value' => $data['default_value'] ?? null,
            'is_required' => !empty($data['is_required']) && (string) $data['is_required'] !== '0',
            'is_active' => !array_key_exists('is_active', $data) || (!empty($data['is_active']) && (string) $data['is_active'] !== '0'),
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : $field->sort_order,
            'options' => $this->normalizeOptions($data['options'] ?? $field->options, $type),
        ]);

        return $field->fresh();
    }

    public function reorder(int $categoryId, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            ClassifyCategoryField::where('category_id', $categoryId)
                ->where('id', $id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Build and run validation for submitted custom field values.
     *
     * @param  array  $input  Request custom_fields map
     * @param  array  $files  Uploaded files map keyed by field id
     * @return array Normalized values keyed by field id (ready to persist)
     */
    public function validateAndNormalize(Collection $fields, array $input, array $files = [], array $existingValues = []): array
    {
        $rules = [];
        $attributes = [];
        $data = [];

        foreach ($fields as $field) {
            $key = 'field_' . $field->id;
            $attributes[$key] = $field->label;
            $raw = $input[$field->id] ?? $input[(string) $field->id] ?? null;
            $file = $files[$field->id] ?? $files[(string) $field->id] ?? null;
            $existing = $existingValues[$field->id] ?? $existingValues[(string) $field->id] ?? null;

            if ($field->type === 'file') {
                $data[$key] = $file;
                $needsFile = $field->is_required && empty($existing);
                $rule = [$needsFile ? 'required' : 'nullable', 'file', 'max:10240'];
                $rules[$key] = $rule;
                continue;
            }

            if ($field->type === 'checkbox' && is_array($field->options) && count($field->options)) {
                $data[$key] = is_array($raw) ? array_values($raw) : (($raw !== null && $raw !== '') ? [$raw] : []);
                $allowed = collect($field->options)->pluck('value')->filter()->values()->all();
                $rules[$key] = [$field->is_required ? 'required' : 'nullable', 'array'];
                $rules[$key . '.*'] = ['in:' . implode(',', $allowed)];
                continue;
            }

            if ($field->type === 'checkbox') {
                $data[$key] = !empty($raw) ? '1' : '0';
                $rules[$key] = [$field->is_required ? 'accepted' : 'nullable'];
                continue;
            }

            $data[$key] = is_array($raw) ? null : $raw;
            $rule = [$field->is_required ? 'required' : 'nullable'];

            switch ($field->type) {
                case 'number':
                    $rule[] = 'numeric';
                    break;
                case 'date':
                    $rule[] = 'date';
                    break;
                case 'textarea':
                case 'text':
                    $rule[] = 'string';
                    $rule[] = 'max:5000';
                    break;
                case 'select':
                case 'radio':
                    $allowed = collect($field->options ?: [])->pluck('value')->filter()->values()->all();
                    if ($allowed) {
                        $rule[] = 'in:' . implode(',', $allowed);
                    }
                    break;
            }

            $rules[$key] = $rule;
        }

        $validator = Validator::make($data, $rules, [], $attributes);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $normalized = [];
        foreach ($fields as $field) {
            $key = 'field_' . $field->id;
            if ($field->type === 'file') {
                $normalized[$field->id] = $files[$field->id] ?? $files[(string) $field->id] ?? null;
                continue;
            }

            $value = $data[$key] ?? null;
            if ($field->type === 'checkbox' && is_array($field->options) && count($field->options)) {
                $normalized[$field->id] = json_encode(array_values($value ?: []));
                continue;
            }

            $normalized[$field->id] = $value === null ? null : (string) $value;
        }

        return $normalized;
    }

    public function syncFieldValues(ClassifyListing $listing, Collection $fields, array $normalizedValues): void
    {
        $allowedIds = $fields->pluck('id')->map(fn ($id) => (int) $id)->all();

        // Remove values for fields no longer applicable
        $listing->fieldValues()
            ->whereNotIn('field_id', $allowedIds ?: [0])
            ->get()
            ->each(function (ClassifyListingFieldValue $row) {
                $this->deleteFileIfNeeded($row);
                $row->delete();
            });

        foreach ($fields as $field) {
            $incoming = $normalizedValues[$field->id] ?? null;
            $existing = $listing->fieldValues()->where('field_id', $field->id)->first();

            if ($field->type === 'file') {
                if ($incoming) {
                    if ($existing) {
                        $this->deleteFileIfNeeded($existing);
                    }
                    $name = Helpers::upload('classify/fields/', 'png', $incoming);
                    ClassifyListingFieldValue::updateOrCreate(
                        ['listing_id' => $listing->id, 'field_id' => $field->id],
                        ['value' => $name]
                    );
                }
                continue;
            }

            if ($incoming === null || $incoming === '') {
                if ($existing) {
                    $existing->delete();
                }
                continue;
            }

            ClassifyListingFieldValue::updateOrCreate(
                ['listing_id' => $listing->id, 'field_id' => $field->id],
                ['value' => $incoming]
            );
        }
    }

    public function deleteFileIfNeeded(ClassifyListingFieldValue $row): void
    {
        $row->loadMissing('field');
        if ($row->field && $row->field->type === 'file' && $row->value) {
            Helpers::check_and_delete('classify/fields/', $row->value);
        }
    }

    public function displayArray(ClassifyListing $listing): array
    {
        $listing->loadMissing(['fieldValues.field']);

        $items = [];
        foreach ($listing->fieldValues as $row) {
            $field = $row->field;
            if (!$field || !$field->is_active) {
                continue;
            }

            $raw = $row->decodedValue();
            if ($raw === null || $raw === '' || $raw === []) {
                continue;
            }

            $display = $this->formatDisplayValue($field, $raw);
            if ($display === null || $display === '') {
                continue;
            }

            $items[] = [
                'field_id' => $field->id,
                'label' => $field->label,
                'name' => $field->name,
                'type' => $field->type,
                'value' => $raw,
                'display_value' => $display,
                'file_full_url' => $field->type === 'file' ? $row->fileFullUrl() : null,
            ];
        }

        return $items;
    }

    protected function formatDisplayValue(ClassifyCategoryField $field, $raw): ?string
    {
        if ($field->type === 'file') {
            return is_string($raw) ? $raw : null;
        }

        if ($field->type === 'checkbox' && !is_array($field->options)) {
            return ((string) $raw === '1' || $raw === true) ? 'Yes' : 'No';
        }

        if (is_array($raw)) {
            $labels = [];
            $options = collect($field->options ?: []);
            foreach ($raw as $value) {
                $match = $options->firstWhere('value', $value);
                $labels[] = $match['label'] ?? (string) $value;
            }
            return implode(', ', $labels);
        }

        if (in_array($field->type, ['select', 'radio', 'checkbox'], true)) {
            $match = collect($field->options ?: [])->firstWhere('value', $raw);
            return $match['label'] ?? (string) $raw;
        }

        return (string) $raw;
    }

    public function extractRequestValues($request): array
    {
        $custom = $request->input('custom_fields', []);
        if (!is_array($custom)) {
            $custom = [];
        }

        $files = [];
        if ($request->hasFile('custom_fields')) {
            $uploaded = $request->file('custom_fields');
            if (is_array($uploaded)) {
                $files = $uploaded;
            }
        }

        return [$custom, $files];
    }
}
