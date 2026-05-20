<?php

namespace App\Http\Controllers;

use App\Models\Perkara;
use Illuminate\Support\Collection;

class PerkaraRecapController extends Controller
{
    public function index()
    {
        $perkaras = Perkara::all();
        $recapData = collect();
        $grandTotal = [
            'diputus' => 0,
            'prodeo' => 0,
            'klasifikasi_0_400' => 0,
            'klasifikasi_400_2jt' => 0,
            'klasifikasi_2jt_plus' => 0,
            'total_biaya' => 0,
            'jumlah' => 0,
        ];

        $grouped = $perkaras->groupBy('kamar');

        foreach ($grouped as $kamar => $items) {
            $diputus = $items->count();
            $prodeo = $items->filter(fn ($p) => is_null($p->biaya) || $p->biaya == 0)->count();
            $klasifikasi_0_400 = $items->filter(fn ($p) => $p->biaya > 0 && $p->biaya < 400000)->count();
            $klasifikasi_400_2jt = $items->filter(fn ($p) => $p->biaya >= 400000 && $p->biaya < 2000000)->count();
            $klasifikasi_2jt_plus = $items->filter(fn ($p) => $p->biaya >= 2000000)->count();
            $total_biaya = $items->sum('biaya');
            $jumlah = $items->count();

            $recapData->push([
                'kamar' => $kamar,
                'diputus' => $diputus,
                'prodeo' => $prodeo,
                'klasifikasi_0_400' => $klasifikasi_0_400,
                'klasifikasi_400_2jt' => $klasifikasi_400_2jt,
                'klasifikasi_2jt_plus' => $klasifikasi_2jt_plus,
                'total_biaya' => $total_biaya,
                'jumlah' => $jumlah,
            ]);

            $grandTotal['diputus'] += $diputus;
            $grandTotal['prodeo'] += $prodeo;
            $grandTotal['klasifikasi_0_400'] += $klasifikasi_0_400;
            $grandTotal['klasifikasi_400_2jt'] += $klasifikasi_400_2jt;
            $grandTotal['klasifikasi_2jt_plus'] += $klasifikasi_2jt_plus;
            $grandTotal['total_biaya'] += $total_biaya;
            $grandTotal['jumlah'] += $jumlah;
        }

        return view('perkaras.recap', compact('recapData', 'grandTotal'));
    }
}
