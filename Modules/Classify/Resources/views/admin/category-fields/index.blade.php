@extends('layouts.admin.app')

@section('title', translate('Category Fields') ?: 'Category Fields')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">{{ translate('Category Fields') ?: 'Category Fields' }}</h1>
        <p class="text-muted mb-0">{{ translate('Manage custom fields for each classify category') ?: 'Manage custom fields for each classify category' }}</p>
    </div>

    <div class="card">
        <div class="card-header">
            <form class="d-flex gap-2" method="get">
                <input type="text" name="search" class="form-control" style="max-width:260px"
                       value="{{ request('search') }}"
                       placeholder="{{ translate('Search category') ?: 'Search category' }}">
                <button class="btn btn-primary">{{ translate('Search') }}</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('Category') }}</th>
                        <th>{{ translate('Type') }}</th>
                        <th>{{ translate('Fields') }}</th>
                        <th class="text-center">{{ translate('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->position == 0 ? (translate('Category') ?: 'Category') : (translate('Sub Category') ?: 'Sub Category') }}</td>
                            <td>{{ $category->classify_fields_count }}</td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('admin.classify.category-fields.show', $category->id) }}">
                                    {{ translate('Manage fields') ?: 'Manage fields' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">{{ translate('no_data_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())
            <div class="card-footer">{{ $categories->links() }}</div>
        @endif
    </div>
</div>
@endsection
