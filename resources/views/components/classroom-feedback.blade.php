@if (session('status'))
    <p role="status" class="rounded bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</p>
@endif
@if ($errors->any())
    <div role="alert" class="rounded bg-red-50 p-4 text-sm text-red-800">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
