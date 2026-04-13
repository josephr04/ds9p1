<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Tarjeta extends Model
{
    protected $table = 'tarjeta';
    protected $primaryKey = 'idTarjeta';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'digitos',
        'fechaVence',
        'codSeguridad',
        'saldo',
        'saldoMaximo'
    ];

    protected $hidden = [
        'codSeguridad'
    ];

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'idTarjeta', 'idTarjeta');
    }
}