@php
    $customFields = $customFields ?? collect();
    $customFieldValues = $customFieldValues ?? [];
    $initialFields = $customFields->map->toDefinitionArray()->values();
    $fieldsUrl = route('vendor.classify.listings.category-fields');
@endphp
<div class="col-md-12">
    <div id="classify-custom-fields" class="row"></div>
</div>

@once
@push('script_2')
<script>
(function () {
    const container = document.getElementById('classify-custom-fields');
    const categorySelect = document.querySelector('select[name="category_id"]');
    const subSelect = document.getElementById('classify_sub_category_id');
    if (!container || !categorySelect) return;

    const fieldsUrl = @json($fieldsUrl);
    let values = @json(old('custom_fields', $customFieldValues));
    let initialFields = @json($initialFields);

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
        });
    }

    function valueFor(field) {
        const key = field.id;
        if (values && (values[key] !== undefined || values[String(key)] !== undefined)) {
            return values[key] !== undefined ? values[key] : values[String(key)];
        }
        return field.default_value ?? '';
    }

    function renderField(field) {
        const required = field.is_required ? 'required' : '';
        const star = field.is_required ? ' *' : '';
        const name = `custom_fields[${field.id}]`;
        const val = valueFor(field);
        const placeholder = escapeHtml(field.placeholder || '');
        let control = '';

        if (field.type === 'textarea') {
            control = `<textarea name="${name}" class="form-control" rows="3" placeholder="${placeholder}" ${required}>${escapeHtml(val)}</textarea>`;
        } else if (field.type === 'select') {
            const opts = (field.options || []).map(o => {
                const selected = String(val) === String(o.value) ? 'selected' : '';
                return `<option value="${escapeHtml(o.value)}" ${selected}>${escapeHtml(o.label)}</option>`;
            }).join('');
            control = `<select name="${name}" class="form-control" ${required}><option value="">{{ translate('Select') }}</option>${opts}</select>`;
        } else if (field.type === 'radio') {
            control = (field.options || []).map((o, idx) => {
                const checked = String(val) === String(o.value) ? 'checked' : '';
                return `<div class="form-check"><input class="form-check-input" type="radio" name="${name}" id="cf_${field.id}_${idx}" value="${escapeHtml(o.value)}" ${checked} ${required}><label class="form-check-label" for="cf_${field.id}_${idx}">${escapeHtml(o.label)}</label></div>`;
            }).join('');
        } else if (field.type === 'checkbox') {
            if (field.options && field.options.length) {
                const selected = Array.isArray(val) ? val.map(String) : (val ? [String(val)] : []);
                control = (field.options || []).map((o, idx) => {
                    const checked = selected.includes(String(o.value)) ? 'checked' : '';
                    return `<div class="form-check"><input class="form-check-input" type="checkbox" name="${name}[]" id="cf_${field.id}_${idx}" value="${escapeHtml(o.value)}" ${checked}><label class="form-check-label" for="cf_${field.id}_${idx}">${escapeHtml(o.label)}</label></div>`;
                }).join('');
            } else {
                const checked = (val === '1' || val === 1 || val === true || val === 'true') ? 'checked' : '';
                control = `<div class="form-check"><input class="form-check-input" type="checkbox" name="${name}" id="cf_${field.id}" value="1" ${checked} ${required}><label class="form-check-label" for="cf_${field.id}">${escapeHtml(field.label)}</label></div>`;
            }
        } else if (field.type === 'file') {
            control = `<input type="file" name="${name}" class="form-control" ${required}>`;
            if (typeof val === 'string' && val) {
                control += `<small class="text-muted d-block mt-1">{{ translate('Existing file') ?: 'Existing file' }}: ${escapeHtml(val)}</small>`;
            }
        } else {
            const inputType = field.type === 'number' ? 'number' : (field.type === 'date' ? 'date' : 'text');
            control = `<input type="${inputType}" name="${name}" class="form-control" value="${escapeHtml(val)}" placeholder="${placeholder}" ${required}>`;
        }

        if (field.type === 'checkbox' && !(field.options && field.options.length)) {
            return `<div class="col-md-12 form-group">${control}</div>`;
        }

        return `<div class="col-md-6 form-group"><label>${escapeHtml(field.label)}${star}</label>${control}</div>`;
    }

    function renderFields(fields) {
        if (!fields || !fields.length) {
            container.innerHTML = '';
            return;
        }
        container.innerHTML = `<div class="col-12"><h5 class="mt-2 mb-3">{{ translate('Category details') ?: 'Category details' }}</h5></div>` +
            fields.map(renderField).join('');
    }

    async function loadFields() {
        const categoryId = categorySelect.value;
        const subId = subSelect ? subSelect.value : '';
        if (!categoryId) {
            renderFields([]);
            return;
        }
        try {
            const url = new URL(fieldsUrl, window.location.origin);
            url.searchParams.set('category_id', categoryId);
            if (subId) url.searchParams.set('sub_category_id', subId);
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            renderFields(data.fields || []);
        } catch (e) {
            renderFields([]);
        }
    }

    categorySelect.addEventListener('change', function () {
        values = {};
        loadFields();
    });
    if (subSelect) {
        subSelect.addEventListener('change', function () {
            values = {};
            loadFields();
        });
    }

    if (initialFields && initialFields.length) {
        renderFields(initialFields);
    } else if (categorySelect.value) {
        loadFields();
    }
})();
</script>
@endpush
@endonce
