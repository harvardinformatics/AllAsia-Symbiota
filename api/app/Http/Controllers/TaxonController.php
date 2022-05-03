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

    /**
    * @OA\Get(
    *	 path="/api/v2/taxa",
    *	 operationId="/api/v2/taxa",
    *  summary="Retrieves taxa information for the current portal (installation).",
    *	 tags={""},
    *	 @OA\Parameter(
    *		 name="id",
    *		 in="query",
    *		 description="taxon id",
    *		 required=false,
    *		 @OA\Schema(type="string")
    *	 ),
    *	 @OA\Parameter(
    *		 name="sciname",
    *		 in="query",
    *		 description="Scientific name, can be partial. Returns all taxa that match the partial name.",
    *		 required=false,
    *		 @OA\Schema(type="string")
    *	 ),
    *	 @OA\Response(
    *		 response="200",
    *		 description="Retrieves taxa information for the current portal (installation).",
    *		 @OA\JsonContent()
    *	 ),
    *	 @OA\Response(
    *		 response="400",
    *		 description="Error: Bad request. ",
    *	 ),
    * )
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