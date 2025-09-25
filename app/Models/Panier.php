<?php
// app/Models/Panier.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'piece_id',
        'quantite'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function piece()
    {
        return $this->belongsTo(Piece::class);
    }
}
