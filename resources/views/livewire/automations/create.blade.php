<x-ui.page
    :title="__('Create Automation')"
    :subtitle="__('Build a targeted email workflow with schedule, template, and repeat rules.')"
>
    @include('livewire.automations.partials.form', [
        'submitLabel' => __('Save Automation'),
    ])
</x-ui.page>
