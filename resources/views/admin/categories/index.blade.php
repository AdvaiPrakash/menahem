@extends('admin.layouts.app')

@section('title', 'Category & Subcategory Management')
@section('header_title', 'Category & Subcategory Management')

@section('header_actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.categories.create') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider transition-all shadow-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Main Category</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- View Switcher Tabs & Search Filter -->
    <div class="bg-white p-4 rounded-none border border-neutral-200 shadow-none flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1 p-1 bg-neutral-100 rounded-none border border-neutral-200">
            <a href="{{ route('admin.categories.index', ['view' => 'tree']) }}" 
               class="px-3.5 py-1.5 text-xs font-bold transition-all {{ ($viewMode ?? 'tree') === 'tree' ? 'bg-neutral-900 text-white' : 'text-neutral-600 hover:text-neutral-900' }}">
                Expandable Tree View
            </a>
            <a href="{{ route('admin.categories.index', ['view' => 'main']) }}" 
               class="px-3.5 py-1.5 text-xs font-bold transition-all {{ ($viewMode ?? '') === 'main' ? 'bg-neutral-900 text-white' : 'text-neutral-600 hover:text-neutral-900' }}">
                Main Categories
            </a>
            @if(($viewMode ?? '') === 'subcategories' && !empty($activeParent))
                <span class="px-3.5 py-1.5 text-xs font-bold bg-neutral-900 text-white">
                    Subcategories: {{ $activeParent->name }}
                </span>
            @endif
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex items-center gap-2">
            @if(($viewMode ?? 'tree') !== 'subcategories')
                <input type="hidden" name="view" value="{{ $viewMode ?? 'tree' }}">
            @else
                <input type="hidden" name="parent_id" value="{{ $activeParent->id }}">
            @endif
            <div class="relative flex-1 sm:w-64">
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Search category name..." 
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                <svg class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-neutral-900 text-white hover:bg-black">Search</button>
            @if(!empty($search))
                <a href="{{ route('admin.categories.index', ['view' => $viewMode ?? 'tree']) }}" class="px-2 py-1.5 text-xs text-neutral-600 hover:text-neutral-900 border border-neutral-300 bg-white">Clear</a>
            @endif
        </form>
    </div>

    <!-- VIEW A: Subcategories Drilldown Mode -->
    @if(($viewMode ?? '') === 'subcategories' && !empty($activeParent))
        <div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
            <!-- Breadcrumbs Navigation Bar -->
            <div class="p-4 bg-neutral-50 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs font-medium text-neutral-600 flex-wrap">
                    <a href="{{ route('admin.categories.index', ['view' => 'main']) }}" class="hover:underline text-neutral-900">Main Categories</a>
                    @foreach($activeParent->ancestors as $anc)
                        <span>&gt;</span>
                        <a href="{{ route('admin.categories.index', ['parent_id' => $anc->id]) }}" class="hover:underline text-neutral-900">{{ $anc->name }}</a>
                    @endforeach
                    <span>&gt;</span>
                    <span class="font-bold text-neutral-900">{{ $activeParent->name }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.categories.create', ['parent_id' => $activeParent->id]) }}" 
                       class="px-3 py-1.5 text-xs font-bold bg-neutral-900 hover:bg-black text-white">
                        + Add Subcategory under {{ $activeParent->name }}
                    </a>
                    <a href="{{ route('admin.categories.index', ['view' => 'tree']) }}" 
                       class="px-3 py-1.5 text-xs font-semibold border border-neutral-300 text-neutral-700 bg-white hover:bg-neutral-100">
                        Back to Tree View
                    </a>
                </div>
            </div>

            <!-- Active Parent Info Header -->
            <div class="p-5 border-b border-neutral-200 bg-white flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-neutral-100 border border-neutral-300 flex items-center justify-center shrink-0">
                        @if($activeParent->image)
                            <img src="{{ $activeParent->image }}" alt="{{ $activeParent->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="font-bold text-neutral-600 text-sm uppercase">{{ substr($activeParent->name, 0, 2) }}</span>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-neutral-900">{{ $activeParent->name }}</h2>
                        <p class="text-xs text-neutral-500 font-mono">{{ $activeParent->path ?: $activeParent->slug }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-neutral-900">{{ $subcategories->count() }} direct subcategories</span>
                </div>
            </div>

            <!-- Subcategories Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-neutral-600">
                    <thead class="bg-neutral-50 border-b border-neutral-200 text-[11px] uppercase font-bold tracking-wider text-neutral-500">
                        <tr>
                            <th class="px-5 py-3.5">Subcategory Name</th>
                            <th class="px-5 py-3.5">URL Path</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Child Subcategories</th>
                            <th class="px-5 py-3.5">Products</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        @forelse($subcategories as $sub)
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-neutral-100 border border-neutral-300 shrink-0 flex items-center justify-center overflow-hidden">
                                            @if($sub->image)
                                                <img src="{{ $sub->image }}" alt="{{ $sub->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-xs font-bold text-neutral-500 uppercase">{{ substr($sub->name, 0, 2) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-bold text-neutral-900 block">{{ $sub->name }}</span>
                                            @if($sub->badge)
                                                <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300">{{ $sub->badge }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-neutral-500">
                                    {{ $sub->path ?: $sub->slug }}
                                </td>
                                <td class="px-5 py-4">
                                    <form action="{{ route('admin.categories.toggle-status', $sub) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-0.5 text-xs font-bold border cursor-pointer {{ $sub->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-rose-50 text-rose-800 border-rose-300' }}">
                                            {{ $sub->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold text-neutral-800">
                                    <a href="{{ route('admin.categories.index', ['parent_id' => $sub->id]) }}" class="underline hover:text-black">
                                        {{ $sub->subcategories_count }} Subcategories &rarr;
                                    </a>
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-neutral-900">
                                    {{ $sub->products_count }} items
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.categories.create', ['parent_id' => $sub->id]) }}" 
                                           class="px-2.5 py-1 text-xs font-semibold bg-neutral-900 hover:bg-black text-white">
                                            + Sub
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $sub) }}" 
                                           class="px-2.5 py-1 text-xs font-semibold bg-white hover:bg-neutral-100 text-neutral-800 border border-neutral-300">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $sub) }}" method="POST" onsubmit="return confirm('Delete this subcategory?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold bg-white hover:bg-rose-50 text-rose-700 border border-rose-300">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-neutral-400 text-sm">
                                    No subcategories under {{ $activeParent->name }} yet. Click "+ Add Subcategory" above to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    <!-- VIEW B: Main Categories Flat Listing -->
    @elseif(($viewMode ?? '') === 'main')
        <div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
            <div class="p-4 border-b border-neutral-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-neutral-900">Top-Level Main Categories</h2>
                    <p class="text-xs text-neutral-500">Root catalog divisions. Click Manage to access subcategories.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-neutral-100 text-neutral-700 border border-neutral-200">
                    Total: {{ $mainCategories->total() }} Main Categories
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-neutral-600">
                    <thead class="bg-neutral-50 border-b border-neutral-200 text-[11px] uppercase font-bold tracking-wider text-neutral-500">
                        <tr>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Slug</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Subcategories</th>
                            <th class="px-5 py-3.5">Products</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        @forelse($mainCategories as $cat)
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-neutral-100 border border-neutral-300 shrink-0 flex items-center justify-center overflow-hidden">
                                            @if($cat->image)
                                                <img src="{{ $cat->image }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-xs font-bold text-neutral-500 uppercase">{{ substr($cat->name, 0, 2) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-bold text-neutral-900 text-sm block">{{ $cat->name }}</span>
                                            @if($cat->badge)
                                                <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300">{{ $cat->badge }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-neutral-500">{{ $cat->slug }}</td>
                                <td class="px-5 py-4">
                                    <form action="{{ route('admin.categories.toggle-status', $cat) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-0.5 text-xs font-bold border cursor-pointer {{ $cat->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-rose-50 text-rose-800 border-rose-300' }}">
                                            {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.categories.index', ['parent_id' => $cat->id]) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1 bg-neutral-100 hover:bg-neutral-200 text-neutral-900 font-bold text-xs border border-neutral-300">
                                        <span>Manage {{ $cat->subcategories_count }} Subcategories &rarr;</span>
                                    </a>
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-neutral-900">
                                    {{ $cat->products_count }} items
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.categories.create', ['parent_id' => $cat->id]) }}" 
                                           class="px-2.5 py-1 text-xs font-semibold bg-neutral-900 hover:bg-black text-white" 
                                           title="Add Subcategory">
                                            + Subcategory
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $cat) }}" 
                                           class="px-2.5 py-1 text-xs font-semibold bg-white hover:bg-neutral-100 text-neutral-800 border border-neutral-300">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category? Subcategories must be empty.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold bg-white hover:bg-rose-50 text-rose-700 border border-rose-300">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-neutral-400 text-sm">No main categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($mainCategories->hasPages())
                <div class="p-4 border-t border-neutral-200 bg-neutral-50">
                    {{ $mainCategories->links() }}
                </div>
            @endif
        </div>

    <!-- VIEW C: Expandable Hierarchical Tree View (Default) -->
    @else
        <div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
            <div class="p-4 bg-neutral-50 border-b border-neutral-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-neutral-900">Interactive Category Tree View</h2>
                    <p class="text-xs text-neutral-500">Expand and collapse folders to explore all subcategories and add deeper levels.</p>
                </div>
                <div class="text-xs text-neutral-500 flex items-center gap-3">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-neutral-900 inline-block"></span> Main</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-neutral-200 inline-block"></span> Subcategory</span>
                </div>
            </div>

            <div class="divide-y divide-neutral-200">
                @forelse($rootCategories as $root)
                    @include('admin.categories._tree-node', ['category' => $root, 'depth' => 0])
                @empty
                    <div class="p-8 text-center text-neutral-400 text-sm">
                        No categories found. Click "+ Add Main Category" to begin.
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection
