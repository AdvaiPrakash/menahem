@extends('admin.layouts.app')

@section('title', $isEdit ? 'Edit Category' : 'Create Category')
@section('header_title', $isEdit ? 'Edit Category: ' . $category->name : 'Create New Category')

@section('header_actions')
    <a href="{{ $category->parent_id ? route('admin.categories.index', ['parent_id' => $category->parent_id]) : route('admin.categories.index', ['view' => 'main']) }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-none border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-semibold uppercase tracking-wider transition-colors">
        ← Back to Categories
    </a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto" x-data="{
    name: '{{ old('name', addslashes($category->name ?? '')) }}',
    slug: '{{ old('slug', addslashes($category->slug ?? '')) }}',
    customSlug: {{ old('slug') && old('slug') !== Str::slug(old('name', '')) ? 'true' : 'false' }},
    imagePreview: '{{ $category->image ? asset($category->image) : '' }}',
    updateSlug() {
        if (!this.customSlug) {
            this.slug = this.name.toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    },
    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
}">
    <div class="bg-white rounded-none border border-neutral-200 shadow-none p-6 sm:p-8">
        <form action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- 1. Category Hierarchy Placement -->
            <div class="p-4 bg-neutral-50 border border-neutral-200">
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-800 mb-1.5">
                    Parent Category
                </label>
                <select name="parent_id" 
                        class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                    <option value="">-- None (Create as Top-Level Main Category) --</option>
                    @foreach($allCategories as $opt)
                        <option value="{{ $opt['id'] }}" 
                            {{ old('parent_id', $category->parent_id ?? '') == $opt['id'] ? 'selected' : '' }}>
                            {{ str_repeat('— ', $opt['depth']) }}{{ $opt['name'] }} {{ $opt['depth'] === 0 ? '(Main)' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-neutral-500 mt-1">
                    Select "None" for a brand new Main Category, or choose an existing category to create a child Subcategory under it.
                </p>
            </div>

            <!-- 2. Category Title & Dynamic Slug -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Category Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           x-model="name"
                           @input="updateSlug()"
                           value="{{ old('name', $category->name) }}" 
                           required 
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="e.g. Cotton Bedsheets">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700">
                            URL Slug
                        </label>
                        <button type="button" 
                                @click="customSlug = !customSlug" 
                                class="text-[11px] text-neutral-500 hover:text-neutral-900 underline">
                            <span x-text="customSlug ? 'Auto-Generate' : 'Customize Slug'"></span>
                        </button>
                    </div>
                    <input type="text" 
                           name="slug" 
                           x-model="slug"
                           :readonly="!customSlug"
                           :class="customSlug ? 'bg-white' : 'bg-neutral-50 text-neutral-600'"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm font-mono focus:outline-none focus:border-neutral-900"
                           placeholder="cotton-bedsheets">
                    <p class="text-[11px] text-neutral-400 mt-1">Used in clean web addresses. Generated automatically.</p>
                </div>
            </div>

            <!-- 3. Category Image Upload & Preview System -->
            <div class="space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700">
                    Category Image
                </label>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                    <!-- Image Preview Box -->
                    <div class="w-full h-36 bg-neutral-100 border border-neutral-300 flex items-center justify-center overflow-hidden relative group">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Category Preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="text-center p-4 text-neutral-400">
                                <svg class="w-8 h-8 mx-auto mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-xs">No image selected</span>
                            </div>
                        </template>
                    </div>

                    <!-- Upload Controls -->
                    <div class="sm:col-span-2 space-y-3">
                        <div>
                            <input type="file" 
                                   name="image_file" 
                                   accept="image/*"
                                   @change="handleFileSelect($event)"
                                   class="w-full text-xs text-neutral-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-none file:border file:border-neutral-300 file:text-xs file:font-semibold file:bg-neutral-900 file:text-white hover:file:bg-black file:cursor-pointer">
                            <p class="text-[11px] text-neutral-500 mt-1">Upload JPEG, WebP, PNG image (max 5MB).</p>
                        </div>

                        @if($category->image)
                            <div class="flex items-center gap-2 pt-2 border-t border-neutral-200">
                                <label class="flex items-center gap-2 text-xs font-semibold text-rose-700 cursor-pointer">
                                    <input type="checkbox" name="remove_image" value="1" class="rounded-none text-rose-600 border-neutral-300">
                                    <span>Remove current image on save</span>
                                </label>
                            </div>
                        @endif

                        <details class="text-xs text-neutral-500 pt-1">
                            <summary class="cursor-pointer hover:text-neutral-800">Or use an existing image URL</summary>
                            <div class="mt-2">
                                <input type="text" 
                                       name="image_url" 
                                       value="{{ old('image_url', $category->image) }}" 
                                       placeholder="/images/categories/bedsheets.webp" 
                                       class="w-full px-3 py-1.5 text-xs rounded-none border border-neutral-300 font-mono bg-white">
                            </div>
                        </details>
                    </div>
                </div>
            </div>

            <!-- 4. Badge & Display Order -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Badge Text (Optional)
                    </label>
                    <input type="text" 
                           name="badge" 
                           value="{{ old('badge', $category->badge) }}" 
                           placeholder="e.g. 100% Cotton, Trending, New" 
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Sort Order Position
                    </label>
                    <input type="number" 
                           name="sort_order" 
                           value="{{ old('sort_order', $category->sort_order ?? 0) }}" 
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                </div>
            </div>

            <!-- 5. Description -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                    Description (Optional)
                </label>
                <textarea name="description" 
                          rows="3" 
                          placeholder="Brief description for category collection headers..." 
                          class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">{{ old('description', $category->description) }}</textarea>
            </div>

            <!-- 6. Active Status Toggle -->
            <div class="flex items-center gap-3 p-4 bg-neutral-50 rounded-none border border-neutral-200">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" 
                       id="is_active" 
                       name="is_active" 
                       value="1" 
                       {{ old('is_active', $category->exists ? $category->is_active : true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded-none text-neutral-900 focus:ring-neutral-900 border-neutral-300">
                <label for="is_active" class="text-sm font-medium text-neutral-800 cursor-pointer">
                    Enable this category on website navigation and filters (Active)
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
                <a href="{{ $category->parent_id ? route('admin.categories.index', ['parent_id' => $category->parent_id]) : route('admin.categories.index', ['view' => 'main']) }}" 
                   class="px-5 py-2.5 rounded-none border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-semibold uppercase tracking-wider transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider transition-all shadow-none">
                    {{ $isEdit ? 'Save Category Changes' : 'Create Category' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
