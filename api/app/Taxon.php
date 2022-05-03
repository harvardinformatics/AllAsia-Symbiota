<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Taxon extends Model
{

    protected $primaryKey = 'tid';
    public $timestamps = false;
    protected $fillable = [
      'TID',
      'kingdomName',
      'RankId',
      'SciName',
      'UnitInd1',
      'UnitName1',
      'UnitInd2',
      'UnitName2',
      'unitInd3',
      'UnitName3',
      'Author',
      'PhyloSortSequence',
      'reviewStatus',
      'displayStatus',
      'isLegitimate',
      'nomenclaturalStatus',
      'nomenclaturalCode',
      'statusNotes',
      'Source',
      'Notes',
      'Hybrid',
      'SecurityStatus',
      'modifiedUid',
      'modifiedTimeStamp',
      'InitialTimeStamp'
    ];

    protected $hidden = [];
}


