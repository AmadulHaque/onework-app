<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-custom-card
            title="Total Candidates"
            value="{{ $this->candidate::count() }}"
            description=""
            color="blue"
        />
        <x-custom-card
            title="Total Enterprises"
            value="{{ $this->enterprise::count() }}"
            description=""
            color="green"
        />
    </div>
</x-filament::page>
