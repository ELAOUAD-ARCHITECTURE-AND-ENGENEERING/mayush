<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Attribute;
use App\Models\AttributeTranslation;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attributeName = 'Dimension';
        
        // Check if attribute already exists by name
        $attribute = Attribute::where('name', $attributeName)->first();

        if (!$attribute) {
            // Check if ID 35 is available to keep environments consistent
            $existingById = Attribute::find(35);
            
            $attribute = new Attribute;
            if (!$existingById) {
                $attribute->id = 35;
            }
            $attribute->name = $attributeName;
            $attribute->save();

            // Create translation for default language
            $translation = AttributeTranslation::where('attribute_id', $attribute->id)
                ->where('lang', env('DEFAULT_LANGUAGE', 'en'))
                ->first();

            if (!$translation) {
                $attributeTranslation = new AttributeTranslation;
                $attributeTranslation->attribute_id = $attribute->id;
                $attributeTranslation->name = $attributeName;
                $attributeTranslation->lang = env('DEFAULT_LANGUAGE', 'en');
                $attributeTranslation->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // We probably shouldn't delete the attribute in reverse 
        // as products might be using it, but for a clean rollback:
        // $attribute = Attribute::where('name', 'Dimension')->first();
        // if ($attribute) {
        //     $attribute->attribute_translations()->delete();
        //     $attribute->delete();
        // }
    }
};
