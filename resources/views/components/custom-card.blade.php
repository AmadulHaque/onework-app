
<style>
    .dark .bg-gray-50 {

    }
</style>
<div class="p-6 bg-white border rounded-lg shadow-md {{ $color }}-200">
    <h2 class="text-lg font-semibold dark:text-gray-800">{{ $title }}</h2>
    <p class="mt-2 text-2xl font-bold dark:text-gray-800">{{ $value }}</p>
    @if ($description)
        <p class="mt-1  dark:text-gray-600">{{ $description }}</p>
    @endif
</div>
