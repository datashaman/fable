<?php

namespace App\Livewire\Concerns;

use App\Models\Milieu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

trait ListensForStateChanges
{
    /** @var array<string, mixed>|null */
    public ?array $lastChange = null;

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            'echo-private:users.'.Auth::id().'.fable,.fable.state-changed' => 'handleStateChanged',
        ];
    }

    /** @param array<string, mixed> $event */
    public function handleStateChanged(array $event): void
    {
        $this->lastChange = $event;

        if (isset($this->milieu) && $this->milieu instanceof Milieu) {
            if ((int) $event['milieu_id'] !== $this->milieu->id) {
                return;
            }

            $this->milieu->refresh();

            if ($event['access_change'] === 'revoked' && Gate::denies('view', $this->milieu)) {
                $this->redirectRoute('dashboard', navigate: true);

                return;
            }
        }

        $this->refreshState($event);
    }

    /** @param array<string, mixed> $event */
    abstract protected function refreshState(array $event): void;
}
