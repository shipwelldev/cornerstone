<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Data\ExpeditionPlanData;
use App\Enums\Destination;
use App\Enums\MissionPurpose;
use Facades\App\Services\ExpeditionPlanningService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Cornerstone')]
class Home extends Component
{
    #[Validate('required|string|min:3|max:40', as: 'call sign')]
    public string $callSign = '';

    #[Validate(['required', new Enum(Destination::class)])]
    public string $destination = '';

    #[Validate('required|integer|min:1|max:12', as: 'crew size')]
    public ?int $crewSize = null;

    #[Validate('required|integer|min:1|max:180', as: 'duration in days')]
    public ?int $durationInDays = null;

    #[Validate(['required', new Enum(MissionPurpose::class)], as: 'mission purpose')]
    public string $missionPurpose = '';

    #[Locked]
    public bool $hasPlan = false;

    public function planExpedition(): void
    {
        $this->validate();

        $this->hasPlan = true;
        unset($this->expeditionPlan);

        $this->dispatch('expedition-planned');
    }

    public function reviseMissionBrief(): void
    {
        $this->hasPlan = false;
        unset($this->expeditionPlan);

        $this->dispatch('mission-brief-revised');
    }

    public function resetMissionBrief(): void
    {
        $this->reset();
        $this->resetValidation();
        unset($this->expeditionPlan);

        $this->dispatch('mission-brief-reset');
    }

    #[Computed]
    public function expeditionPlan(): ?ExpeditionPlanData
    {
        if ( ! $this->hasPlan || $this->crewSize === null || $this->durationInDays === null) {
            return null;
        }

        $destination = Destination::tryFrom($this->destination);
        $missionPurpose = MissionPurpose::tryFrom($this->missionPurpose);

        if ($destination === null || $missionPurpose === null) {
            return null;
        }

        // A real-time facade keeps the call terse while the underlying service remains container-resolved and mockable.
        return ExpeditionPlanningService::plan(
            callSign: $this->callSign,
            destination: $destination,
            crewSize: $this->crewSize,
            durationInDays: $this->durationInDays,
            missionPurpose: $missionPurpose,
        );
    }

    public function render(): View
    {
        return view('livewire.home', [
            'destinations' => Destination::cases(),
            'missionPurposes' => MissionPurpose::cases(),
        ]);
    }

}
