@extends('admin.layouts.app')

@section('title', 'Manage Team & Roles')
@section('header_title', 'Staff Accounts & RBAC')

@section('header_actions')
    <a href="{{ route('admin.users.create') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-semibold uppercase tracking-wider transition-all shadow-xs">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Add Staff Member</span>
    </a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Administrator & Staff Roster</h2>
            <p class="text-xs text-slate-500">Manage who has access to the Menahem management panel</p>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
            {{ $users->total() }} Active Users
        </span>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-bold tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3.5">Name</th>
                    <th class="px-5 py-3.5">Email</th>
                    <th class="px-5 py-3.5">Assigned Role</th>
                    <th class="px-5 py-3.5">Created Date</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#74826B]/20 text-[#74826B] font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span class="font-bold text-slate-900">{{ $user->name }}</span>
                            @if(auth()->id() === $user->id)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">You</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4 font-mono text-xs text-slate-700">
                        {{ $user->email }}
                    </td>
                    <td class="px-5 py-4">
                        @if($user->isAdmin())
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-purple-100 text-purple-900 border border-purple-200">Administrator</span>
                        @elseif($user->role?->value === 'editor')
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-blue-100 text-blue-900 border border-blue-200">Editor</span>
                        @else
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full bg-slate-100 text-slate-700 border border-slate-200">Viewer</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-xs text-slate-500">
                        {{ $user->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" 
                               class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-[#74826B] hover:text-white text-slate-700 text-xs font-semibold transition-colors">
                                Edit
                            </a>
                            @if(auth()->id() !== $user->id)
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Permanently remove this user?');" class="inline">
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
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Stacked Card View -->
    <div class="block md:hidden divide-y divide-slate-100">
        @foreach($users as $user)
        <div class="p-4 space-y-2">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span>{{ $user->name }}</span>
                        @if(auth()->id() === $user->id)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">You</span>
                        @endif
                    </h3>
                    <p class="text-xs font-mono text-slate-500">{{ $user->email }}</p>
                </div>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full bg-slate-100 text-slate-700">
                    {{ $user->role?->value ?? 'viewer' }}
                </span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-800 text-xs font-semibold">
                    Edit
                </a>
                @if(auth()->id() !== $user->id)
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-semibold">
                        Delete
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($users->hasPages())
    <div class="p-4 border-t border-slate-200/80 bg-slate-50">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
