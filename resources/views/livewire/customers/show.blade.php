<x-ui.page
    :title="$customer->name"
    :subtitle="$customer->email"
>
    <x-slot:actions>
        <x-ui.button :href="route('customers.index')" variant="secondary" wire:navigate>
            {{ __('Back') }}
        </x-ui.button>

        <x-ui.button :href="route('customers.edit', $customer)" icon="pencil-square" wire:navigate>
            {{ __('Edit Customer') }}
        </x-ui.button>
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Customer Details') }}</h2>

            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->phone ?: '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Source') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->source ? str($customer->source->value)->headline() : '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Signup Date') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->signup_at?->format('Y-m-d H:i') ?? $customer->created_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Last Login') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->last_login_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                </div>
            </dl>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Subscription') }}</h2>

                @if($customer->latestSubscription)
                    <dl class="mt-5 space-y-4">
                        <div>
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Started') }}</dt>
                            <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->latestSubscription->started_at?->format('Y-m-d H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Expires') }}</dt>
                            <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->latestSubscription->expires_at?->format('Y-m-d H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Cancelled') }}</dt>
                            <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->latestSubscription->cancelled_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">{{ __('This customer has never subscribed.') }}</p>
                @endif
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Tags') }}</h2>

                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse($customer->tags as $tag)
                        <x-ui.badge>{{ $tag->name }}</x-ui.badge>
                    @empty
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No tags assigned.') }}</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-ui.page>
