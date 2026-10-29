<x-layout title="Edit inventoris">
    <h1 class="h3 mb-4">Edit inventoris</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('inventoris.update', $inventoris) }}" method="POST">
                @csrf
                @method('PUT')
                @include('inventoris._form')
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('inventoris.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
</x-layout>
