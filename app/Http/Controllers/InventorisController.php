<?php

namespace App\Http\Controllers;

use App\Models\inventoris;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class inventorisController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => 'nullable|string|max:255', 'kategori' => 'nullable|integer|exists:kategoris,id']);
        $search = trim($filters['search'] ?? ($request->has('search') ? '' : $request->session()->get('last_search', '')));
        $request->session()->put('last_search', $search);
        $bukus = Buku::with('kategori')->withCount('peminjamans')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('nim', 'like', '%'.$search.'%')
                    ->orWhere('nama', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->when($filters['kategori'] ?? null, fn ($query, $kategori) => $query->where('kategori_id', $kategori))
            ->latest()->paginate(10)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $statistik = [
            'Total inventoris' => DB::table('inventoris')->count(),
            'Total Stok Tersedia' => DB::table('inventoriss')->sum('stok'),
            'Total inventoris Sedang Dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'Total Dikembalikan' => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        return view('inventoris.index', compact('inventoriss', 'kategoris', 'statistik', 'search'));
    }

    public function create(): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.create', compact('kategoris'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inventoris' => ['required'],
            'nama barang' => ['required', 'string', 'min:5', 'max:255'],
            'kategori_id' => ['required', 'integer', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        Buku::create($validated);

        return redirect()->route('inventoris.index')->with('success', 'inventoris berhasil ditambahkan.');
    }

    public function show(Inventoris $inventoris): View
    {
        $inventoris->load('kategori');

        return view('inventoris.show', compact('inventoris'));
    }

    public function edit(Buku $inventoris): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('inventoris.edit', compact('inventoris', 'kategoris'));
    }

    public function update(Request $request, inventoris $inventoris): RedirectResponse
    {
        $validated = $request->validate([
            'inventoris' => ['required'],
            'nama barang' => ['required', 'string', 'min:5', 'max:255'],
            'kategori_id' => ['required', 'integer', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        $buku->update($validated);

        return redirect()->route('inventoris.index')->with('success', 'inventoris berhasil diperbarui.');
    }

    public function destroy(Inventoris $inventoris): RedirectResponse
    {
        $inventoris->delete();

        return redirect()->route('inventoris.index')->with('success', 'inventoris berhasil dihapus.');
    }
}
