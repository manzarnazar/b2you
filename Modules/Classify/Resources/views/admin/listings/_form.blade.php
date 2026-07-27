<div class="row">
    <div class="col-md-6 form-group">
        <label>{{ translate('Title') }}</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $listing->title ?? '') }}" required>
    </div>
    <div class="col-md-3 form-group">
        <label>{{ translate('Price') }}</label>
        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $listing->price ?? 0) }}" required>
    </div>
    <div class="col-md-3 form-group">
        <label>{{ translate('Status') }}</label>
        <select name="status" class="form-control">
            @foreach(['draft','pending','published','rejected','sold','expired','archived'] as $st)
                <option value="{{ $st }}" {{ old('status', $listing->status ?? 'pending') == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 form-group">
        <label>{{ translate('Condition') }}</label>
        <select name="condition" class="form-control" required>
            @foreach(['new','used','refurbished'] as $c)
                <option value="{{ $c }}" {{ old('condition', $listing->condition ?? 'used') == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 form-group">
        <label>{{ translate('Category') }}</label>
        <select name="category_id" class="form-control" required>
            <option value="">{{ translate('Select') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $listing->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 form-group">
        <label>{{ translate('Sub Category') }}</label>
        <input type="number" name="sub_category_id" class="form-control" value="{{ old('sub_category_id', $listing->sub_category_id ?? '') }}" placeholder="Optional">
    </div>
    <div class="col-md-6 form-group">
        <label>{{ translate('Phone') }}</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $listing->phone ?? '') }}">
    </div>
    <div class="col-md-6 form-group">
        <label>{{ translate('Address') }}</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $listing->address ?? '') }}">
    </div>
    <div class="col-md-3 form-group">
        <label>{{ translate('Latitude') }}</label>
        <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $listing->latitude ?? '') }}">
    </div>
    <div class="col-md-3 form-group">
        <label>{{ translate('Longitude') }}</label>
        <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $listing->longitude ?? '') }}">
    </div>
    <div class="col-md-12 form-group">
        <label>{{ translate('Description') }}</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', $listing->description ?? '') }}</textarea>
    </div>
    <div class="col-md-12 form-group">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="is_negotiable" value="1" {{ old('is_negotiable', $listing->is_negotiable ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">{{ translate('Negotiable') }}</label>
        </div>
    </div>
    <div class="col-md-12 form-group">
        <label>{{ translate('Images') }}</label>
        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
        @if(isset($listing) && $listing->images?->count())
            <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach($listing->images as $img)
                    <img src="{{ \App\CentralLogics\Helpers::get_full_url('classify', $img->image, $img->storage) }}" style="height:80px;object-fit:cover;border-radius:8px;" alt="">
                @endforeach
            </div>
        @endif
    </div>
</div>
