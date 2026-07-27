<?php

namespace App\View\Components;

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

        $navigationMilieus = $user instanceof User
            ? Milieu::query()
                ->where(fn (Builder $query) => $query
                    ->whereBelongsTo($user, 'owner')
                    ->orWhereHas('memberships', fn (Builder $query) => $query->whereBelongsTo($user)))
                ->with(['memberships' => fn ($query) => $query->whereBelongsTo($user)])
                ->orderBy('name')
                ->get()
            : collect();

        return view('layouts.app.sidebar', ['navigationMilieus' => $navigationMilieus]);
    }
}
