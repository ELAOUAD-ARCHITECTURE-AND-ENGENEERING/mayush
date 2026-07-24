<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class CustomAlert extends Model
{
    use HasFactory, PreventDemoModeChanges;

    protected $guarded = ['id'];

    protected $fillable = [
        'status', 'type', 'banner', 'link', 'description', 'background_color', 'text_color'
    ];

    /**
     * Get translated description based on current active locale.
     */
    public function getTranslation($field = 'description', $lang = false)
    {
        $lang = $lang ?: App::getLocale();

        if ($this->id == 1 && $field === 'description') {
            if (in_array($lang, ['ar', 'ma'])) {
                return '<p>نحن نستخدم ملفات تعريف الارتباط لتحسين تجربتك، تصفح سياسة الخصوصية <a href="/privacy-policy" class="text-reset underline">هنا</a></p>';
            } elseif ($lang === 'en') {
                return '<p>We use cookies to improve your shopping experience. Read our privacy policy <a href="/privacy-policy" class="text-reset underline">here</a>.</p>';
            } else {
                return '<p>Nous utilisons des cookies pour améliorer votre expérience d\'achat. Consultez notre politique de confidentialité <a href="/privacy-policy" class="text-reset underline">ici</a>.</p>';
            }
        }

        return $this->$field;
    }
}
