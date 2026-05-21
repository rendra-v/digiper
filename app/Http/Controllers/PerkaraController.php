<?php

namespace App\Http\Controllers;

use App\Models\Perkara;
use App\Http\Requests\StorePerkara;
use Illuminate\Http\Request;

class PerkaraController extends Controller
{
    public function index()
    {
        $perkaras = Perkara::paginate(10);
        return view('perkaras.index', compact('perkaras'));
    }

    public function create()
    {
        return view('perkaras.create');
    }

    public function store(StorePerkara $request)
    {
        try {
            // Handle file upload if present
            if ($request->hasFile('file')) {
                // For now, just skip file handling
                return redirect()->back()->with('error', 'File import belum diimplementasikan. Gunakan form manual.');
            }

            // Handle manual form submission
            $data = $request->validated();
            Perkara::create($data);
            return redirect()->route('perkaras.index')->with('success', 'Perkara berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function show(Perkara $perkara)
    {
        return view('perkaras.show', compact('perkara'));
    }

    public function edit(Perkara $perkara)
    {
        //
    }

    public function update(Request $request, Perkara $perkara)
    {
        //
    }

    public function destroy(Perkara $perkara)
    {
        $perkara->delete();
        return redirect()->route('perkaras.index')->with('success', 'Data berhasil dihapus!');
    }
}
