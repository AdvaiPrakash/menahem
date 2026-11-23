@props(['category', 'depth' => 0])

<div x-data="{ expanded: true }" class="border-b border-neutral-200 last:border-b-0">
    <!-- Category Row Item -->
    <div class="flex items-center justify-between p-3.5 hover:bg-neutral-50 transition-colors gap-3" 
         style="padding-left: {{ max(12, $depth * 28 + 12) }}px;">
        
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <!-- Expand / Collapse chevron if has children -->
            @if($category->subcategories->count() > 0)
                <button type="button" 
                        @click="expanded = !expanded" 
                        class="w-6 h-6 flex items-center justify-center text-neutral-500 hover:text-neutral-900 border border-neutral-300 bg-white hover:bg-neutral-100 shrink-0">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @else
                <span class="w-6 h-6 flex items-center justify-center text-neutral-300 shrink-0">•</span>
            @endif

            <!-- Category Thumbnail Image -->
            <div class="w-9 h-9 shrink-0 bg-neutral-100 border border-neutral-300 overflow-hidden flex items-center justify-center">
                @if($category->image)
                    <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-[10px] font-bold text-neutral-500 uppercase">{{ substr($category->name, 0, 2) }}</span>
                @endif
            </div>

            <!-- Category Name, Slug & Badges -->
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-bold text-neutral-900 text-sm truncate">{{ $category->name }}</span>
                    
                    @if($category->depth === 0)
                        <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-neutral-900 text-white">Main</span>
                    @elseif($category->depth === 1)
                        <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-neutral-100 text-neutral-800 border border-neutral-300">Sub</span>
                    @else
                        <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-neutral-50 text-neutral-600 border border-neutral-200">Level {{ $category->depth + 1 }}</span>
                    @endif

                    @if($category->badge)
                        <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-50 text-amber-900 border border-amber-300">{{ $category->badge }}</span>
                    @endif

                    <!-- Active / Inactive Badge -->
                    <form action="{{ route('admin.categories.toggle-status', $category) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                title="Click to toggle status" 
                                class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider border cursor-pointer {{ $category->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-rose-50 text-rose-800 border-rose-300' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                </div>
                <p class="text-[11px] text-neutral-400 font-mono mt-0.5 truncate">{{ $category->path ?: $category->slug }}</p>
            </div>
        </div>

        <!-- Meta Counts & Action Buttons -->
        <div class="flex items-center gap-2 shrink-0">
            <!-- Count of subcategories -->
            <a href="{{ route('admin.categories.index', ['parent_id' => $category->id]) }}" 
               class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-neutral-700 bg-neutral-100 hover:bg-neutral-200 border border-neutral-300">
                <span>{{ $category->subcategories->count() }} sub</span>
            </a>

            <!-- Products count -->
            <span class="hidden md:inline-block px-2 py-1 text-xs text-neutral-500">
                {{ $category->products_count ?? 0 }} products
            </span>

            <!-- Add Subcategory under this category -->
            <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}" 
               class="px-2.5 py-1 text-xs font-semibold bg-neutral-900 hover:bg-black text-white" 
               title="Add subcategory directly under {{ $category->name }}">
                + Subcategory
            </a>

            <!-- Edit -->
            <a href="{{ route('admin.categories.edit', $category) }}" 
               class="px-2 py-1 text-xs font-semibold text-neutral-800 bg-white hover:bg-neutral-100 border border-neutral-300">
                Edit
            </a>

            <!-- Delete -->
            <form action="{{ route('admin.categories.destroy', $category) }}" 
                  method="POST" 
                  onsubmit="return confirm('Delete category \'{{ addslashes($category->name) }}\'? (Subcategories must be deleted first)');" 
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-2 py-1 text-xs font-semibold text-rose-700 bg-white hover:bg-rose-50 border border-rose-300">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Recursive Children Container -->
    @if($category->subcategories->count() > 0)
        <div x-show="expanded" x-collapse>
            @foreach($category->subcategories as $child)
                @include('admin.categories._tree-node', ['category' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
