@extends('admin.layouts.app')

@section('title', 'Manage Categories')
@section('header_title', 'Categories Taxonomy')

@section('header_actions')
    @if(auth()->user()->canEditContent())
    <a href="{{ route('admin.categories.create') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-semibold uppercase tracking-wider transition-all shadow-xs">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Add Category</span>
    </a>
    @endif
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Category Hierarchy</h2>
            <p class="text-xs text-slate-500">Root collections, subcategories & product distribution</p>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
            Total: {{ $categories->total() }} Categories
        </span>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-bold tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3.5">Category Name</th>
                    <th class="px-5 py-3.5">Hierarchy Path</th>
                    <th class="px-5 py-3.5">Parent Category</th>
                    <th class="px-5 py-3.5">Products</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($categories as $cat)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($cat->image)
                                <img src="{{ $cat->image }}" alt="{{ $cat->name }}" class="w-10 h-10 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-200/60">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-[#74826B]/20 text-[#74826B] font-bold flex items-center justify-center text-xs">
                                    {{ substr($cat->name, 0, 2) }}
                                </div>
                            @endif
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $cat->name }}</span>
                                <span class="text-xs text-slate-400 font-mono">{{ $cat->slug }}</span>
                                @if($cat->badge)
                                    <span class="inline-block ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-amber-100 text-amber-800">{{ $cat->badge }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 font-mono text-xs text-slate-600">
                        {{ $cat->path ?: $cat->slug }}
                    </td>
                    <td class="px-5 py-4 text-xs font-semibold text-slate-700">
                        @if($cat->parent)
                            <span class="px-2 py-1 rounded bg-slate-100 text-slate-700">{{ $cat->parent->name }}</span>
                        @else
                            <span class="px-2 py-1 rounded bg-emerald-50 text-emerald-700 font-bold">Root Collection</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-xs font-bold text-slate-900">
                        {{ $cat->products_count }} items
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ url('/categories/' . ($cat->path ?: $cat->slug)) }}" target="_blank" 
                               class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition-colors" title="View category page">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            @if(auth()->user()->canEditContent())
                            <a href="{{ route('admin.categories.edit', $cat) }}" 
                               class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-[#74826B] hover:text-white text-slate-700 text-xs font-semibold transition-colors">
                                Edit
                            </a>
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category? Subcategories must be empty.');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 rounded-md bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 text-xs font-semibold transition-colors">
                                    Delete
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                        No categories found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Stacked Card View -->
    <div class="block md:hidden divide-y divide-slate-100">
        @forelse($categories as $cat)
        <div class="p-4 space-y-2.5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 text-sm">{{ $cat->name }}</h3>
                    <p class="text-xs text-slate-500 font-mono">{{ $cat->path ?: $cat->slug }}</p>
                </div>
                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-slate-100 text-slate-700 shrink-0">
                    {{ $cat->products_count }} items
                </span>
            </div>

            <div class="text-xs text-slate-600 flex items-center justify-between">
                <span>Parent: {{ $cat->parent?->name ?? 'Root Collection' }}</span>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <a href="{{ url('/categories/' . ($cat->path ?: $cat->slug)) }}" target="_blank" class="text-xs font-semibold text-slate-600">
                    Preview ↗
                </a>
                @if(auth()->user()->canEditContent())
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.categories.edit', $cat) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-800 text-xs font-semibold">
                        Edit
                    </a>
                    <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-semibold">
                            Delete
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-sm text-slate-400">
            No categories found.
        </div>
        @endforelse
    </div>

    @if($categories->hasPages())
    <div class="p-4 border-t border-slate-200/80 bg-slate-50">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection
