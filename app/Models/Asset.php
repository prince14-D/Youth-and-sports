<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Asset extends Model { use SoftDeletes; protected $fillable=['asset_tag','name','category','serial_number','condition','status','purchase_date','purchase_value','notes']; protected $casts=['purchase_date'=>'date','purchase_value'=>'decimal:2']; public function assignments(): HasMany { return $this->hasMany(AssetAssignment::class); } }
