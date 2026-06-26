<x-ui.page
    :title="__('Edit Automation')"
    :subtitle="$automation->name"
>
    @include('livewire.automations.partials.form', [
        'submitLabel' => __('Update Automation'),
    ])
</x-ui.page>
