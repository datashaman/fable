<?php

namespace App\View\Components;

use App\Enums\MilieuStatus;
use App\Models\Milieu;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\Component;

class AppShell extends Component
{
    public function __construct(public ?string $title = null) {}

    public function render(): View|Closure|string
    {
        $user = auth()->user();
        $routeMilieu = request()->route('milieu');
        $activeMilieuId = $routeMilieu instanceof Milieu ? $routeMilieu->id : null;

        $navigationMilieus = $user instanceof User
            ? Milieu::query()
                ->where(fn (Builder $query) => $query
                    ->whereBelongsTo($user, 'owner')
                    ->orWhereHas('memberships', fn (Builder $query) => $query->whereBelongsTo($user)))
                ->where(fn (Builder $query) => $query
                    ->where('status', '!=', MilieuStatus::Archived)
                    ->when($activeMilieuId, fn (Builder $query) => $query->orWhere('id', $activeMilieuId)))
                ->with(['memberships' => fn ($query) => $query->whereBelongsTo($user)])
                ->orderBy('name')
                ->get()
            : collect();

        return view('layouts.app.sidebar', ['navigationMilieus' => $navigationMilieus]);
    }
}
