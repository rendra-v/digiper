<?php

namespace App\Http\Controllers;

use App\Models\Perkara;
use App\Imports\PerkaraImport;
use App\Http\Requests\StorePerkara;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
            Excel::import(new PerkaraImport(), $request->file('file'));
            return redirect()->route('perkaras.index')->with('success', 'Data berhasil diunggah!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
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
