<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FacturaDetalle extends Model
{
    protected $table = 'factura_detalle';
    protected $primaryKey = 'idFacDet';
    public $timestamps = false;

    protected $fillable = [
        'idFactura',
        'idProducto',
        'cantidad',
        'precio_unitario'
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'idFactura', 'idFactura');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }
}