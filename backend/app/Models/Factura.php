<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'factura';
    protected $primaryKey = 'idFactura';
    public $timestamps = false;

    protected $fillable = [
        'idTarjeta',
        'subtotal',
        'itbms',
        'total'
    ];

    public function tarjeta()
    {
        return $this->belongsTo(Tarjeta::class, 'idTarjeta', 'idTarjeta');
    }

    public function detalles()
    {
        return $this->hasMany(FacturaDetalle::class, 'idFactura', 'idFactura');
    }
}