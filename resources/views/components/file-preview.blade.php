@php
    $ext = pathinfo($fileUrl, PATHINFO_EXTENSION);
@endphp

<div class="space-y-3">
    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
        <img src="{{ $fileUrl }}" alt="Preview" class="max-w-full rounded shadow">
    @elseif($ext === 'pdf')
        <iframe src="{{ $fileUrl }}?{{ now() }}" class="w-full h-96 rounded border" frameborder="0"></iframe>
    @else
        <a href="{{ $fileUrl }}" target="_blank" class="text-blue-600 underline">Download file</a>
    @endif
</div>
