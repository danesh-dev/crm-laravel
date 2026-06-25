<div class="space-y-4">

    <div>
        <label>Name</label>

        <input
            wire:model="name"
            class="border p-2 w-full"
        >
    </div>

    <div>
        <label>Subject</label>

        <input
            wire:model="subject"
            class="border p-2 w-full"
        >
    </div>

    <div>
        <label>Body</label>

        <textarea
            wire:model="body_html"
            rows="15"
            class="border p-2 w-full"
        ></textarea>
    </div>

    <div class="bg-gray-600 p-3">
        <p>Available Variables:</p>

        <ul class="list-disc ml-5">
            <li>@{{name}}</li>
            <li>@{{email}}</li>
            <li>@{{signup_date}}</li>
        </ul>
    </div>

        
    <button
        type="button"
        wire:click="save"
        class="px-4 py-2 bg-green-600 text-white rounded border"
    >
        Edit Template
    </button>

    <div class="space-x-2">
        <div>

            <label class="block mb-1">
                Preview Customer
            </label>

            <select
                wire:model="previewCustomerId"
                class="border p-2 w-full"
            >

                @foreach($customers as $customer)

                    <option value="{{ $customer->id }}">
                        {{ $customer->name }}
                        ({{ $customer->email }})
                    </option>

                @endforeach

            </select>

        </div>
        <button
            type="button"
            wire:click="generatePreview"
            class="px-4 py-2 bg-blue-600 text-white rounded border"
        >
            Generate Preview
        </button>

    </div>

    @if($preview)

        <div class="border p-4">

            {!! $preview !!}

        </div>

    @endif

</div>