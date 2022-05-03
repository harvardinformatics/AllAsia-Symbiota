<?php

namespace App\Http\Controllers;

use App\Taxon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxonController extends Controller{
  /**
   * Taxon controller instance.
   * 
   */
  
  //  Needs to implement authentication
  // public function showAllTaxa(){
  //   $taxa = Taxon::paginate(100);
  //   return response()->json($taxa);
  // }
  
  public function showAllPublicTaxa(){
    if(request()->has('sciname')){
      // where sciname like request and securitystats = 0
      $taxa = Taxon::where('SciName', 'like', '%'.request('sciname').'%')
        ->where('SecurityStatus', '=', 0)
        ->paginate(100);
    } else {
      $taxa = Taxon::where('SecurityStatus', '0')->paginate(100);
    }
    return response()->json($taxa);
  }

  public function showOneTaxon($id){
    $taxon = Taxon::find($id);
    return response()->json($taxon);
  }


}