<div>
    <x-slot:title>
        Shared Variables | Coolify
    </x-slot>

    <x-shared-variables.layout>

    <div class="w-full">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <a class="group flex min-h-32 flex-col rounded-2xl p-4 hover:no-underline bg-surface ring-1 ring-inset ring-hairline transition-[background-color,box-shadow] duration-150 hover:bg-raised hover:ring-control"
            href="{{ route('shared-variables.team.index') }}" {{ wireNavigate() }}>
            <div
                class="flex size-8 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-fg-dim">
                <x-reicon name="teams" class="size-4" />
            </div>
            <div class="mt-auto pt-5">
                <h2 class="text-[13px]! leading-4! font-semibold! text-black dark:text-fg">Team wide</h2>
                <p class="mt-1 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                    Available to every resource owned by this team.
                </p>
            </div>
        </a>

        <a class="group flex min-h-32 flex-col rounded-2xl p-4 hover:no-underline bg-surface ring-1 ring-inset ring-hairline transition-[background-color,box-shadow] duration-150 hover:bg-raised hover:ring-control"
            href="{{ route('shared-variables.project.index') }}" {{ wireNavigate() }}>
            <div
                class="flex size-8 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-fg-dim">
                <x-reicon name="projects" class="size-4" />
            </div>
            <div class="mt-auto pt-5">
                <h2 class="text-[13px]! leading-4! font-semibold! text-black dark:text-fg">Project wide</h2>
                <p class="mt-1 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                    Shared by every environment inside a project.
                </p>
            </div>
        </a>

        <a class="group flex min-h-32 flex-col rounded-2xl p-4 hover:no-underline bg-surface ring-1 ring-inset ring-hairline transition-[background-color,box-shadow] duration-150 hover:bg-raised hover:ring-control"
            href="{{ route('shared-variables.environment.index') }}" {{ wireNavigate() }}>
            <div
                class="flex size-8 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-fg-dim">
                <x-reicon name="layers" class="size-4" />
            </div>
            <div class="mt-auto pt-5">
                <h2 class="text-[13px]! leading-4! font-semibold! text-black dark:text-fg">Environment wide</h2>
                <p class="mt-1 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                    Reused by resources in one environment.
                </p>
            </div>
        </a>

        <a class="group flex min-h-32 flex-col rounded-2xl p-4 hover:no-underline bg-surface ring-1 ring-inset ring-hairline transition-[background-color,box-shadow] duration-150 hover:bg-raised hover:ring-control"
            href="{{ route('shared-variables.server.index') }}" {{ wireNavigate() }}>
            <div
                class="flex size-8 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-fg-dim">
                <x-reicon name="servers" class="size-4" />
            </div>
            <div class="mt-auto pt-5">
                <h2 class="text-[13px]! leading-4! font-semibold! text-black dark:text-fg">Server wide</h2>
                <p class="mt-1 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                    Available to resources deployed on one server.
                </p>
            </div>
        </a>
    </div>
    </div>
    </x-shared-variables.layout>
</div>
