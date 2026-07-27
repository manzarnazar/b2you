@php
    $selectedCategoryId = old('category_id', $listing->category_id ?? '');
    $selectedSubCategoryId = old('sub_category_id', $listing->sub_category_id ?? '');
    $subCategoryMap = ($categories ?? collect())->mapWithKeys(function ($category) {
        return [
            $category->id => $category->childes->map(fn ($child) => [
                'id' => $child->id,
                'name' => $child->name,
            ])->values(),
        ];
    });
    $currentSubCategories = $selectedCategoryId
        ? (($categories ?? collect())->firstWhere('id', (int) $selectedCategoryId)?->childes ?? collect())
        : collect();
@endphp
<div class="{{ $wrapperClass ?? 'col-md-3' }} form-group">
    <label>{{ translate('Sub Category') }}</label>
    <select name="sub_category_id" id="classify_sub_category_id" class="form-control">
        <option value="">{{ translate('messages.select_sub_category') ?: 'Optional' }}</option>
        @foreach($currentSubCategories as $sub)
            <option value="{{ $sub->id }}" @selected((string) $selectedSubCategoryId === (string) $sub->id)>{{ $sub->name }}</option>
        @endforeach
    </select>
</div>

@once
    @push('script_2')
        <script>
            (function () {
                const subCategoryMap = @json($subCategoryMap);
                const categorySelect = document.querySelector('select[name="category_id"]');
                const subSelect = document.getElementById('classify_sub_category_id');
                if (!categorySelect || !subSelect) {
                    return;
                }

                const placeholder = @json(translate('messages.select_sub_category') ?: 'Optional');
                let selectedSubId = @json($selectedSubCategoryId);

                function fillSubCategories(keepSelection) {
                    const categoryId = categorySelect.value;
                    const wantId = keepSelection ? String(selectedSubId || '') : '';
                    subSelect.innerHTML = '';
                    const empty = document.createElement('option');
                    empty.value = '';
                    empty.textContent = placeholder;
                    subSelect.appendChild(empty);

                    const children = subCategoryMap[categoryId] || subCategoryMap[Number(categoryId)] || [];
                    children.forEach(function (child) {
                        const option = document.createElement('option');
                        option.value = child.id;
                        option.textContent = child.name;
                        if (wantId && String(child.id) === wantId) {
                            option.selected = true;
                        }
                        subSelect.appendChild(option);
                    });
                }

                categorySelect.addEventListener('change', function () {
                    selectedSubId = '';
                    fillSubCategories(false);
                });

                fillSubCategories(true);
            })();
        </script>
    @endpush
@endonce
