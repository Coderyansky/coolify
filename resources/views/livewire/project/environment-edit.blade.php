<div>
    <x-slot:title>{{ data_get_str($environment, 'name')->limit(10) }} > Edit | Coolify</x-slot>
    <div class="w-full max-w-none">
        <header class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="truncate text-[28px]! leading-[1.1]! font-semibold! tracking-[-0.03em]!">{{ $environment->name }}</h1>
                <p class="mt-2 max-w-2xl text-[14px] leading-relaxed text-fg-faint">
                    Environment settings in {{ $project->name }}
                </p>
            </div>
            @can('createAnyResource')
                <div class="flex w-fit shrink-0 items-center gap-2">
                    <a class="button whitespace-nowrap" {{ wireNavigate() }}
                        href="{{ route('project.clone-me', ['project_uuid' => $project->uuid, 'environment_uuid' => $environment->uuid]) }}">
                        <x-reicon name="layers" class="size-3.5 opacity-70" />
                        Clone environment
                    </a>
                </div>
            @endcan
        </header>

        <div class="flex flex-col gap-6">
        <form wire:submit="submit">
            <x-unsaved-bar action="submit" />
            <section class="application-settings-section">
                <div class="application-settings-section-header">
                    <div>
                        <h2>Environment details</h2>
                        <p>Name and describe this environment inside {{ $project->name }}.</p>
                    </div>
                </div>
                <div class="application-settings-section-body grid gap-4 sm:grid-cols-2">
                    <x-forms.input label="Name" id="name" canGate="update" :canResource="$environment" />
                    <x-forms.input label="Description" id="description" canGate="update"
                        :canResource="$environment" />
                </div>
            </section>
        </form>

        @can('delete', $environment)
            <section
                class="overflow-hidden rounded-[10px] border border-error/30 bg-error/10 dark:border-error/30 dark:bg-error/10">
                <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-error dark:text-error">Delete environment</h2>
                        <p class="mt-1 max-w-2xl text-sm text-error/80 dark:text-error/70">
                            Remove every resource before permanently deleting this environment.
                        </p>
                    </div>
                    <div class="shrink-0 sm:pt-0.5">
                        <livewire:project.delete-environment :disabled="! $environment->isEmpty()"
                            :environment_id="$environment->id" />
                    </div>
                </div>
            </section>
        @endcan
        </div>
    </div>
</div>
