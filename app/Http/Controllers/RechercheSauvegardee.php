<?php
// app/Models/RechercheSauvegardee.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechercheSauvegardee extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'nom',
        'criteres',
        'notifications_activees'
    ];

    protected $casts = [
        'criteres' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
