<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleado';
    protected $primaryKey = 'usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'usuario',
        'nombre',
        'apellido',
        'rol',
        'contrasena'
    ];

    protected $hidden = [
        'contrasena'
    ];
}