<x-ui.page
    :title="__('Edit Admin')"
    :subtitle="$user->email"
>
    @include('livewire.users.partials.form', [
        'submitLabel' => __('Update User'),
        'passwordRequired' => false,
        'canEditRole' => $canEditRole,
    ])
</x-ui.page>
