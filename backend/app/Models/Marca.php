<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marca';
    protected $primaryKey = 'idMarca';
    public $timestamps = false;

    protected $fillable = [
        'nombreMarc'
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'idMarca', 'idMarca');
    }
}