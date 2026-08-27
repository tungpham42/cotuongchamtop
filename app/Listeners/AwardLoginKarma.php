<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Actions\User\AwardLoginKarmaAction;

class AwardLoginKarma
{
    public function __construct(private AwardLoginKarmaAction $action) {}

    public function handle(Login $event): void
    {
        $this->action->execute($event->user);
    }
}
