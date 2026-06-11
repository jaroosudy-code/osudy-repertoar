@extends('layouts.app')
@section('title', 'Správa rolí')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-600 text-sm">Používatelia</a>
            <span class="text-slate-300">/</span>
            <span class="text-sm text-slate-600">Roly</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Roly a oprávnenia</h1>
    </div>
    <a href="{{ route('admin.roles.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
        + Nová rola
    </a>
</div>

<div class="space-y-3">
    @foreach($roles as $role)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-2">
                    <h2 class="font-bold text-slate-800">{{ $role->name }}</h2>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $role->badgeClass() }}">{{ $role->slug }}</span>
                    <span class="text-xs text-slate-400">{{ $role->users_count }} {{ $role->users_count === 1 ? 'používateľ' : ($role->users_count < 5 ? 'používatelia' : 'používateľov') }}</span>
                </div>

                @if($role->isAdmin())
                    <p class="text-sm text-slate-500 italic">Plný prístup – všetky oprávnenia sú povolené.</p>
                @elseif(empty($role->permissions))
                    <p class="text-sm text-slate-400 italic">Iba čítanie – žiadne oprávnenia na úpravy.</p>
                @else
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($role->permissions as $perm)
                            @if(isset(\App\Models\Role::PERMISSION_LABELS[$perm]))
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs">
                                {{ \App\Models\Role::PERMISSION_LABELS[$perm] }}
                            </span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex gap-2 shrink-0">
                @if(!$role->isAdmin())
                <a href="{{ route('admin.roles.edit', $role) }}"
                   class="px-3 py-1.5 border border-slate-300 text-slate-600 hover:bg-slate-50 rounded-lg text-xs font-medium transition-colors">
                    Upraviť
                </a>
                @if($role->users_count === 0)
                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline delete-confirm-form" data-name="{{ $role->name }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1.5 border border-slate-300 text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-300 rounded-lg text-xs font-medium transition-colors">
                        Zmazať
                    </button>
                </form>
                @endif
                @else
                <span class="px-3 py-1.5 text-slate-300 text-xs font-medium">chránená</span>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
<script>
document.querySelectorAll('.delete-confirm-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm('Zmazať rolu „' + form.dataset.name + '"?')) form.submit();
    });
});
</script>
@endsection
