<x-ui.page
    :title="__('Create Admin')"
    :subtitle="__('Create a new administrator account.')"
>
    @include('livewire.users.partials.form', [
        'submitLabel' => __('Create User'),
        'passwordRequired' => true,
        'canEditRole' => true,
    ])
</x-ui.page>
