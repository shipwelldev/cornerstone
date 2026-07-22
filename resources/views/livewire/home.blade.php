<div
    x-data
    x-on:expedition-planned.window="$nextTick(() => $refs.expeditionPlan?.focus())"
    x-on:mission-brief-revised.window="$nextTick(() => $refs.callSign?.focus())"
    x-on:mission-brief-reset.window="$nextTick(() => $refs.callSign?.focus())"
>
    <section class="relative overflow-hidden border-b border-white/10">
        <div class="pointer-events-none absolute inset-0 -z-10 opacity-40" aria-hidden="true">
            <div class="absolute top-14 left-[8%] size-1 rounded-full bg-white shadow-[8rem_4rem_0_0_rgba(255,255,255,0.65),22rem_-2rem_0_0_rgba(255,255,255,0.4),42rem_7rem_0_0_rgba(255,255,255,0.55),61rem_1rem_0_0_rgba(255,255,255,0.35)]"></div>
            <div class="absolute top-0 right-0 h-80 w-80 rounded-full bg-orange-400/15 blur-3xl"></div>
        </div>

        <div class="mx-auto grid max-w-6xl gap-12 px-6 py-16 lg:grid-cols-[1fr_22rem] lg:items-end lg:px-8 lg:py-24">
            <div>
                <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-orange-300/20 bg-orange-300/10 px-3 py-1.5 font-mono text-[11px] font-semibold tracking-[0.18em] text-orange-200 uppercase">
                    <span class="size-1.5 rounded-full bg-orange-300"></span>
                    Canonical example
                </div>

                <h1 class="max-w-4xl text-5xl font-semibold tracking-[-0.045em] text-balance text-white sm:text-6xl lg:text-7xl">
                    Plot a route through <span class="text-orange-300">unfamiliar systems.</span>
                </h1>

                <p class="mt-7 max-w-2xl text-lg leading-8 text-stone-400">
                    This fictional expedition planner is disposable reference code. Follow its vertical slice to see how Cornerstone expects Livewire, Alpine, Services, Data objects, Blade components, and tests to collaborate.
                </p>
            </div>

            <aside class="border-l border-white/10 pl-6">
                <p class="font-mono text-[11px] tracking-[0.2em] text-stone-400 uppercase">Example boundary</p>
                <p class="mt-3 text-sm leading-6 text-stone-300">
                    Transient and deterministic. No database, authorization, queue, upload, or external HTTP concern is invented where the domain does not need one.
                </p>
            </aside>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-6 py-16 lg:px-8 lg:py-24" aria-labelledby="planner-heading">
        <div class="grid gap-12 lg:grid-cols-[18rem_1fr]">
            <div>
                <p class="font-mono text-xs font-semibold tracking-[0.2em] text-teal-300 uppercase">Interactive reference</p>
                <h2 id="planner-heading" class="mt-3 text-3xl font-semibold tracking-tight text-white">Expedition planner</h2>
                <p class="mt-4 text-sm leading-7 text-stone-400">
                    Livewire owns the validated workflow. Alpine responds to browser events for focus and owns only local disclosure state.
                </p>

                <ol class="mt-8 grid gap-3" aria-label="Planner progress">
                    <li @class([
                        'flex items-center gap-3 rounded-xl border px-4 py-3 text-sm',
                        'border-orange-300/30 bg-orange-300/10 text-orange-100' => ! $hasPlan,
                        'border-white/10 text-stone-400' => $hasPlan,
                    ])>
                        <span class="grid size-7 place-items-center rounded-full border border-current font-mono text-xs">1</span>
                        Mission brief
                    </li>
                    <li @class([
                        'flex items-center gap-3 rounded-xl border px-4 py-3 text-sm',
                        'border-teal-300/30 bg-teal-300/10 text-teal-100' => $hasPlan,
                        'border-white/10 text-stone-400' => ! $hasPlan,
                    ])>
                        <span class="grid size-7 place-items-center rounded-full border border-current font-mono text-xs">2</span>
                        Expedition plan
                    </li>
                </ol>
            </div>

            <div class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-stone-900/75 shadow-2xl shadow-black/30 backdrop-blur">
                @if (! $hasPlan)
                    <form wire:submit="planExpedition" class="p-6 sm:p-9" novalidate>
                        <div class="flex flex-col justify-between gap-4 border-b border-white/10 pb-7 sm:flex-row sm:items-end">
                            <div>
                                <p class="font-mono text-[11px] tracking-[0.18em] text-stone-400 uppercase">Step 01 / Mission brief</p>
                                <h3 class="mt-2 text-2xl font-semibold text-white">Define the expedition</h3>
                            </div>
                            <p class="text-xs text-stone-400">All fields are required</p>
                        </div>

                        <div class="mt-8 grid gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="call-sign" class="text-sm font-medium text-stone-200">Call sign</label>
                                <input
                                    id="call-sign"
                                    name="callSign"
                                    type="text"
                                    x-ref="callSign"
                                    wire:model.live.blur="callSign"
                                    aria-describedby="call-sign-help @error('callSign') call-sign-error @enderror"
                                    aria-invalid="{{ $errors->has('callSign') ? 'true' : 'false' }}"
                                    placeholder="Aurora Seven"
                                    autocomplete="off"
                                    class="mt-2 block w-full rounded-xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-white placeholder:text-stone-600 focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-300/20"
                                >
                                <p id="call-sign-help" class="mt-2 text-xs text-stone-400">A memorable name between 3 and 40 characters.</p>
                                @error('callSign')
                                    <p id="call-sign-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="destination" class="text-sm font-medium text-stone-200">Destination</label>
                                <select
                                    id="destination"
                                    name="destination"
                                    wire:model.live.blur="destination"
                                    @error('destination') aria-describedby="destination-error" @enderror
                                    aria-invalid="{{ $errors->has('destination') ? 'true' : 'false' }}"
                                    class="mt-2 block w-full rounded-xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-white focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-300/20"
                                >
                                    <option value="">Choose a destination</option>
                                    @foreach ($destinations as $destinationOption)
                                        <option wire:key="destination-{{ $destinationOption->value }}" value="{{ $destinationOption->value }}">
                                            {{ $destinationOption->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('destination')
                                    <p id="destination-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="mission-purpose" class="text-sm font-medium text-stone-200">Mission purpose</label>
                                <select
                                    id="mission-purpose"
                                    name="missionPurpose"
                                    wire:model.live.blur="missionPurpose"
                                    @error('missionPurpose') aria-describedby="mission-purpose-error" @enderror
                                    aria-invalid="{{ $errors->has('missionPurpose') ? 'true' : 'false' }}"
                                    class="mt-2 block w-full rounded-xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-white focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-300/20"
                                >
                                    <option value="">Choose a purpose</option>
                                    @foreach ($missionPurposes as $missionPurposeOption)
                                        <option wire:key="purpose-{{ $missionPurposeOption->value }}" value="{{ $missionPurposeOption->value }}">
                                            {{ $missionPurposeOption->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('missionPurpose')
                                    <p id="mission-purpose-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="crew-size" class="text-sm font-medium text-stone-200">Crew size</label>
                                <input
                                    id="crew-size"
                                    name="crewSize"
                                    type="number"
                                    min="1"
                                    max="12"
                                    wire:model.live.blur.number="crewSize"
                                    aria-describedby="crew-size-help @error('crewSize') crew-size-error @enderror"
                                    aria-invalid="{{ $errors->has('crewSize') ? 'true' : 'false' }}"
                                    placeholder="4"
                                    class="mt-2 block w-full rounded-xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-white placeholder:text-stone-600 focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-300/20"
                                >
                                <p id="crew-size-help" class="mt-2 text-xs text-stone-400">Between 1 and 12 crew members.</p>
                                @error('crewSize')
                                    <p id="crew-size-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="duration-in-days" class="text-sm font-medium text-stone-200">Duration in days</label>
                                <input
                                    id="duration-in-days"
                                    name="durationInDays"
                                    type="number"
                                    min="1"
                                    max="180"
                                    wire:model.live.blur.number="durationInDays"
                                    aria-describedby="duration-help @error('durationInDays') duration-error @enderror"
                                    aria-invalid="{{ $errors->has('durationInDays') ? 'true' : 'false' }}"
                                    placeholder="45"
                                    class="mt-2 block w-full rounded-xl border border-white/10 bg-stone-950/70 px-4 py-3 text-sm text-white placeholder:text-stone-600 focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-300/20"
                                >
                                <p id="duration-help" class="mt-2 text-xs text-stone-400">Between 1 and 180 days.</p>
                                @error('durationInDays')
                                    <p id="duration-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-9 flex flex-col-reverse gap-3 border-t border-white/10 pt-6 sm:flex-row sm:justify-end">
                            <button
                                type="button"
                                wire:click="resetMissionBrief"
                                class="inline-flex items-center justify-center rounded-xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-300 transition hover:border-white/20 hover:bg-white/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-300"
                            >
                                Reset
                            </button>
                            <button
                                type="submit"
                                data-test="plan-expedition"
                                class="data-loading:pointer-events-none data-loading:opacity-60 inline-flex items-center justify-center rounded-xl bg-orange-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300"
                            >
                                <span wire:loading.remove wire:target="planExpedition">Plan expedition</span>
                                <span wire:loading wire:target="planExpedition">Calculating route...</span>
                            </button>
                        </div>
                    </form>
                @elseif ($this->expeditionPlan !== null)
                    <article class="p-6 sm:p-9" aria-labelledby="expedition-plan-heading" aria-live="polite">
                        <div class="flex flex-col justify-between gap-5 border-b border-white/10 pb-7 sm:flex-row sm:items-start">
                            <div>
                                <p class="font-mono text-[11px] tracking-[0.18em] text-teal-300 uppercase">Step 02 / Expedition plan</p>
                                <h3 id="expedition-plan-heading" x-ref="expeditionPlan" tabindex="-1" class="mt-2 text-3xl font-semibold tracking-tight text-white focus:outline-none">
                                    {{ $this->expeditionPlan->callSign }}
                                </h3>
                                <p class="mt-2 text-sm text-stone-400">
                                    {{ $this->expeditionPlan->destination->label() }} / {{ $this->expeditionPlan->missionPurpose->label() }} / {{ $this->expeditionPlan->durationInDays }} days
                                </p>
                            </div>
                            <span @class([
                                'w-fit rounded-full border px-3 py-1.5 font-mono text-[11px] font-semibold tracking-wider uppercase',
                                'border-teal-300/20 bg-teal-300/10 text-teal-200' => $this->expeditionPlan->riskClassification === 'Routine',
                                'border-amber-300/20 bg-amber-300/10 text-amber-200' => $this->expeditionPlan->riskClassification === 'Elevated',
                                'border-red-300/20 bg-red-300/10 text-red-200' => $this->expeditionPlan->riskClassification === 'Extreme',
                            ])>
                                {{ $this->expeditionPlan->riskClassification }} risk
                            </span>
                        </div>

                        <dl class="mt-7 grid gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10 sm:grid-cols-3">
                            <div class="bg-stone-950/80 p-5">
                                <dt class="text-xs text-stone-400">Crew</dt>
                                <dd class="mt-1 text-2xl font-semibold text-white">{{ $this->expeditionPlan->crewSize }}</dd>
                            </div>
                            <div class="bg-stone-950/80 p-5">
                                <dt class="text-xs text-stone-400">Ration packs</dt>
                                <dd class="mt-1 text-2xl font-semibold text-white">{{ $this->expeditionPlan->rationPacks }}</dd>
                            </div>
                            <div class="bg-stone-950/80 p-5">
                                <dt class="text-xs text-stone-400">Water liters</dt>
                                <dd class="mt-1 text-2xl font-semibold text-white">{{ $this->expeditionPlan->waterLiters }}</dd>
                            </div>
                        </dl>

                        <div class="mt-8 grid gap-4">
                            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                                <p class="font-mono text-[10px] tracking-[0.18em] text-orange-300 uppercase">Navigation</p>
                                <p class="mt-2 text-sm leading-6 text-stone-300">{{ $this->expeditionPlan->navigationRecommendation }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                                <p class="font-mono text-[10px] tracking-[0.18em] text-orange-300 uppercase">Survival</p>
                                <p class="mt-2 text-sm leading-6 text-stone-300">{{ $this->expeditionPlan->survivalRecommendation }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                                <p class="font-mono text-[10px] tracking-[0.18em] text-orange-300 uppercase">Mission specialist</p>
                                <p class="mt-2 text-sm leading-6 text-stone-300">{{ $this->expeditionPlan->missionSpecialistRecommendation }}</p>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-teal-300/15 bg-teal-300/[0.06] p-5">
                            <p class="text-xs font-semibold text-teal-200">Mission advisory</p>
                            <p class="mt-2 text-sm leading-6 text-stone-300">{{ $this->expeditionPlan->advisory }}</p>
                        </div>

                        <div class="mt-8 flex justify-end border-t border-white/10 pt-6">
                            <button
                                type="button"
                                wire:click="reviseMissionBrief"
                                class="inline-flex items-center justify-center rounded-xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-200 transition hover:border-white/20 hover:bg-white/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-300"
                            >
                                Revise mission brief
                            </button>
                        </div>
                    </article>
                @endif
            </div>
        </div>
    </section>

    <section class="border-y border-white/10 bg-stone-900/40" aria-labelledby="slice-heading">
        <div class="mx-auto max-w-6xl px-6 py-16 lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <p class="font-mono text-xs font-semibold tracking-[0.2em] text-orange-300 uppercase">Follow the vertical slice</p>
                <h2 id="slice-heading" class="mt-3 text-3xl font-semibold tracking-tight text-white">One behavior, clear ownership</h2>
                <p class="mt-4 text-sm leading-7 text-stone-400">Each file exists because it owns a distinct boundary. Remove the seam when your real application does not need it.</p>
            </div>

            <div class="mt-10 grid gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10 md:grid-cols-2 lg:grid-cols-3">
                <div class="bg-stone-950/90 p-6">
                    <p class="text-xs font-semibold text-teal-300">Entry boundary</p>
                    <code class="mt-3 block text-xs text-stone-300">app/Livewire/Home.php</code>
                    <p class="mt-3 text-sm leading-6 text-stone-400">Validates browser input and owns workflow state.</p>
                </div>
                <div class="bg-stone-950/90 p-6">
                    <p class="text-xs font-semibold text-teal-300">Orchestration</p>
                    <code class="mt-3 block text-xs text-stone-300">app/Services/ExpeditionPlanningService.php</code>
                    <p class="mt-3 text-sm leading-6 text-stone-400">Applies deterministic planning rules behind a real-time facade.</p>
                </div>
                <div class="bg-stone-950/90 p-6">
                    <p class="text-xs font-semibold text-teal-300">Boundary data</p>
                    <code class="mt-3 block text-xs text-stone-300">app/Data/ExpeditionPlanData.php</code>
                    <p class="mt-3 text-sm leading-6 text-stone-400">Carries one immutable plan into the interface.</p>
                </div>
                <div class="bg-stone-950/90 p-6">
                    <p class="text-xs font-semibold text-teal-300">Shared presentation</p>
                    <code class="mt-3 block text-xs text-stone-300">resources/views/components/faq-item.blade.php</code>
                    <p class="mt-3 text-sm leading-6 text-stone-400">Owns reusable FAQ markup and local Alpine state.</p>
                </div>
                <div class="bg-stone-950/90 p-6">
                    <p class="text-xs font-semibold text-teal-300">Domain language</p>
                    <code class="mt-3 block text-xs text-stone-300">CONTEXT.md</code>
                    <p class="mt-3 text-sm leading-6 text-stone-400">Keeps developers and agents aligned on precise terms.</p>
                </div>
                <div class="bg-stone-950/90 p-6">
                    <p class="text-xs font-semibold text-teal-300">Public seams</p>
                    <code class="mt-3 block text-xs text-stone-300">tests/{Unit,Feature,Browser}</code>
                    <p class="mt-3 text-sm leading-6 text-stone-400">Tests rules, server interaction, and browser behavior at the appropriate layer.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto grid max-w-6xl gap-12 px-6 py-16 lg:grid-cols-[18rem_1fr] lg:px-8 lg:py-24" aria-labelledby="faq-heading">
        <div>
            <p class="font-mono text-xs font-semibold tracking-[0.2em] text-teal-300 uppercase">Developer guidance</p>
            <h2 id="faq-heading" class="mt-3 text-3xl font-semibold tracking-tight text-white">Before you depart</h2>
        </div>

        <div class="border-t border-white/10">
            {{-- FAQ copy belongs to this view; only its repeated interaction and presentation are extracted. --}}
            @foreach ([
                'Why is this fictional planner included?' => 'It gives developers and agents one coherent, executable reference for this starter kit\'s architectural and stylistic conventions. It is not application functionality and should be replaced by your first real vertical slice.',
                'Why do Livewire and Alpine own different state?' => 'Livewire owns validated server workflow state and the derived Expedition Plan. Alpine owns browser-only focus and disclosure behavior, avoiding duplicated sources of truth.',
                'What belongs in CONTEXT.md?' => 'Inspired by Matt Pocock\'s domain-modeling practice, CONTEXT.md gives developers and agents a shared glossary for the application\'s precise domain language. Replace the fictional expedition terms as your real vocabulary emerges, and use it to distinguish concepts that might otherwise be conflated. The /remove-example skill deletes this file only when it contains no custom terms, so it is safe to customize before removing the example.',
                'How do I remove the canonical example?' => 'Once a real vertical slice can teach these conventions, ask your agent to remove the example or invoke /remove-example. The skill inventories dependencies, protects reused pieces, asks what should own the home route, and verifies the cleanup.',
            ] as $question => $answer)
                <x-faq-item :$question wire:key="{{ $question }}">
                    {{ $answer }}
                </x-faq-item>
            @endforeach
        </div>
    </section>
</div>
