@extends('admin.layouts.app')

@section('title', $isEdit ? 'Edit Staff Account' : 'Add Staff Account')
@section('header_title', $isEdit ? 'Edit Staff Member: ' . $user->name : 'Add New Staff Member')

@section('header_actions')
    <a href="{{ route('admin.users.index') }}" 
       class="inline-flex items-center gap-2 px-3 py-2 rounded-none border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-semibold uppercase tracking-wider transition-colors">
        ← Back to Staff
    </a>
@endsection

@section('content')
<div class="max-w-2xl">
    <form action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" 
          method="POST" 
          class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                       placeholder="e.g. Sarah Jenkins">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Email Address *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                       placeholder="sarah@menahem.com">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                    Password {{ $isEdit ? '(Leave blank to keep unchanged)' : '*' }}
                </label>
                <input type="password" name="password" {{ $isEdit ? '' : 'required' }} minlength="8"
                       class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                       placeholder="••••••••">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Role & Access Privileges *</label>
                <select name="role" required class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" {{ old('role', $user->role?->value ?? 'viewer') === $role->value ? 'selected' : '' }}>
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </select>
                <div class="mt-3 p-3 bg-neutral-50 rounded-none border border-neutral-200 text-xs text-neutral-500 space-y-1">
                    <p><span class="font-bold text-neutral-800">Administrator:</span> Full access, managing products, categories, leads & staff.</p>
                    <p><span class="font-bold text-neutral-800">Editor:</span> Can create/edit products & categories, view and respond to leads.</p>
                    <p><span class="font-bold text-neutral-800">Viewer:</span> Read-only overview of catalog & inquiries.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.users.index') }}" 
               class="px-5 py-2.5 rounded-none border border-neutral-300 text-neutral-600 hover:bg-neutral-100 text-sm font-semibold transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-none bg-neutral-900 hover:bg-black text-white text-sm font-semibold transition-all shadow-none">
                {{ $isEdit ? 'Update Account' : 'Create Account' }}
            </button>
        </div>
    </form>
</div>
@endsection
