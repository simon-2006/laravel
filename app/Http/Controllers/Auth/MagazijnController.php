<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MagazijnController extends Controller
{
    // Overzicht: alle producten in het magazijn, gesorteerd op barcode (oplopend)
    public function index()
    {
        $producten = DB::table('Product as p')
            ->leftJoin('ProductPerLeverancier as ppl', 'ppl.ProductId', '=', 'p.Id')
            ->select([
                'p.Id',
                'p.Barcode',
                'p.Naam',
                DB::raw('MAX(ppl.DatumLevering) AS LaatsteLevering'),
            ])
            ->groupBy('p.Id', 'p.Barcode', 'p.Naam')
            ->orderBy('p.Barcode', 'asc')
            ->paginate(20);

        return view('magazijn.index', compact('producten'));
    }

    // Detailscherm: leveringsinformatie voor een gekozen product
    public function leveringInfo(int $id)
    {
        $product = DB::table('Product')->where('Id', $id)->first();

        // Alle leveringen van dit product, oplopend op datum
        $leveringen = DB::table('ProductPerLeverancier')
            ->where('ProductId', $id)
            ->orderBy('DatumLevering', 'asc')
            ->get();

        // Bepaal de bezorger op basis van de laatste levering
        $laatste = DB::table('ProductPerLeverancier')
            ->where('ProductId', $id)
            ->orderBy('DatumLevering', 'desc')
            ->first();

        $bezorger = null;
        if ($laatste) {
            $bezorger = DB::table('Leverancier')->where('Id', $laatste->LeverancierId)->first();
        }

        return view('magazijn.levering-info', compact('product', 'leveringen', 'bezorger'));
    }
}
