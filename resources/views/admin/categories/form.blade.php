@extends('admin.layouts.app')

@section('title', $isEdit ? 'Edit Category' : 'Add New Category')
@section('header_title', $isEdit ? 'Edit Category: ' . $category->name : 'Create New Category')

@section('header_actions')
    <a href="{{ route('admin.categories.index') }}" 
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-semibold uppercase tracking-wider transition-colors">
        ← Back to Categories
    </a>
@endsection

@section('content')
<div class="max-w-3xl">
    <form action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" 
          method="POST" 
          class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Category Name *</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="e.g. Bedsheets & Dohars">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">URL Slug (Optional)</label>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="bedsheets-dohars">
                    <p class="text-[11px] text-slate-400 mt-1">Leave blank to auto-generate from name</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Parent Category</label>
                    <select name="parent_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]">
                        <option value="">-- None (Top Level Collection) --</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Badge Text</label>
                    <input type="text" name="badge" value="{{ old('badge', $category->badge) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="e.g. 100% Cotton, Trending">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Image Asset URL</label>
                    <input type="text" name="image" value="{{ old('image', $category->image) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="/images/categories/bedsheets.webp">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                              placeholder="Describe this curated category...">{{ old('description', $category->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.categories.index') }}" 
               class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-sm font-semibold transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#74826B] hover:bg-[#636F5A] text-white text-sm font-semibold transition-all shadow-md">
                {{ $isEdit ? 'Update Category' : 'Create Category' }}
            </button>
        </div>
    </form>
</div>
@endsection
