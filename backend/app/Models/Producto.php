<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'idProducto';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'unidad',
        'descripcion',
        'stock',
        'precioCosto',
        'precioVenta',
        'imagen',
        'idCategoria',
        'idMarca'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'idMarca', 'idMarca');
    }
}