<x-ui.page
    :title="__('Delivery #:id', ['id' => $delivery->id])"
    :subtitle="$delivery->customer?->email ?? __('Email delivery details.')"
>
    @inject('statusPresenter', 'App\Services\Email\DeliveryStatusPresenter')

    <x-slot:actions>
        <div class="flex flex-wrap justify-end gap-2">
            <x-ui.button :href="route('deliveries.index')" variant="secondary" wire:navigate>
                {{ __('Back') }}
            </x-ui.button>

            @if($delivery->status === \App\Enums\EmailDeliveryStatus::FAILED)
                <x-ui.button variant="secondary" icon="arrow-path" wire:click="retry">
                    {{ __('Retry') }}
                </x-ui.button>
            @endif

            @if($delivery->status === \App\Enums\EmailDeliveryStatus::PENDING)
                <x-ui.button
                    variant="danger"
                    icon="x-mark"
                    wire:click="cancel"
                    wire:confirm="{{ __('Cancel this pending delivery?') }}"
                >
                    {{ __('Cancel') }}
                </x-ui.button>
            @endif
        </div>
    </x-slot:actions>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if($message)
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
            {{ $message }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Delivery') }}</h2>

                    <x-ui.badge :variant="$statusPresenter->badgeVariant($delivery->status)">
                        {{ str($delivery->status->value)->headline() }}
                    </x-ui.badge>
                </div>

                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('ID') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ str($delivery->status->value)->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Customer ID') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->customer_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Automation ID') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->automation_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email Template ID') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->email_template_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Scheduled At') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->scheduled_for?->format('Y-m-d H:i:s') ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Processing At') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->processing_at?->format('Y-m-d H:i:s') ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sent At') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->sent_at?->format('Y-m-d H:i:s') ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Failed At') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->failed_at?->format('Y-m-d H:i:s') ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Provider Message ID') }}</dt>
                        <dd class="mt-1 break-words font-medium text-zinc-950 dark:text-white">{{ $delivery->provider_message_id ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Created At') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->created_at?->format('Y-m-d H:i:s') ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Updated At') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->updated_at?->format('Y-m-d H:i:s') ?? '---' }}</dd>
                    </div>
                </dl>

                <div class="mt-5">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Error Message') }}</dt>
                    <dd class="mt-2 rounded-md border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">
                        {{ $delivery->error_message ?? '---' }}
                    </dd>
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Customer Information') }}</h2>

                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->customer?->name ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->customer?->email ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->customer?->phone ?: '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Source') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">
                            {{ $delivery->customer?->source ? str($delivery->customer->source->value)->headline() : '---' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Signup At') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->customer?->signup_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Last Login At') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->customer?->last_login_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Automation and Template') }}</h2>

                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Automation') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->automation?->name ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Template') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->emailTemplate?->name ?? '---' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Template Subject') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->emailTemplate?->subject ?? '---' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Status Timeline') }}</h2>

            <div class="mt-5 space-y-4">
                @foreach([
                    __('Created') => $delivery->created_at,
                    __('Scheduled') => $delivery->scheduled_for,
                    __('Processing') => $delivery->processing_at,
                    __('Sent') => $delivery->sent_at,
                    __('Failed') => $delivery->failed_at,
                    __('Last Updated') => $delivery->updated_at,
                ] as $label => $date)
                    <div class="flex items-start gap-3">
                        <div class="mt-1 h-2.5 w-2.5 rounded-full {{ $date ? 'bg-zinc-900 dark:bg-white' : 'bg-zinc-300 dark:bg-zinc-700' }}"></div>
                        <div>
                            <div class="text-sm font-medium text-zinc-950 dark:text-white">{{ $label }}</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $date?->format('Y-m-d H:i:s') ?? '---' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-ui.page>
