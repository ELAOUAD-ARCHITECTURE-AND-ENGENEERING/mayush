<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Upload;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\File;

$img1_source = 'C:\\Users\\benjk\\.gemini\\antigravity\\brain\\1d97ed86-caa9-4204-9877-83f6cda86040\\interior_split_banner_1_1779366190596.png';
$img2_source = 'C:\\Users\\benjk\\.gemini\\antigravity\\brain\\1d97ed86-caa9-4204-9877-83f6cda86040\\interior_split_banner_2_1779366523143.png';

$img1_dest = 'public/uploads/all/interior_split_banner_1.png';
$img2_dest = 'public/uploads/all/interior_split_banner_2.png';

File::copy($img1_source, $img1_dest);
File::copy($img2_source, $img2_dest);

$upload1 = new Upload();
$upload1->file_original_name = 'interior_split_banner_1';
$upload1->file_name = 'uploads/all/interior_split_banner_1.png';
$upload1->user_id = 1;
$upload1->extension = 'png';
$upload1->type = 'image';
$upload1->file_size = filesize($img1_dest);
$upload1->save();

$upload2 = new Upload();
$upload2->file_original_name = 'interior_split_banner_2';
$upload2->file_name = 'uploads/all/interior_split_banner_2.png';
$upload2->user_id = 1;
$upload2->extension = 'png';
$upload2->type = 'image';
$upload2->file_size = filesize($img2_dest);
$upload2->save();

$titles = ['Découvrez la collection Automne', 'Des pièces uniques pour votre salon'];
$descriptions = ['Transformez votre intérieur avec nos meubles au design contemporain et matériaux nobles.', 'Créez une atmosphère chaleureuse avec notre sélection exclusive d\'articles de décoration.'];
$ctas = ['Acheter maintenant', 'Explorer la collection'];
$links = ['/search', '/search'];
$images = [$upload1->id, $upload2->id];

BusinessSetting::updateOrCreate(['type' => 'home_banner4_images'], ['value' => json_encode($images)]);
BusinessSetting::updateOrCreate(['type' => 'home_banner4_titles'], ['value' => json_encode($titles)]);
BusinessSetting::updateOrCreate(['type' => 'home_banner4_descriptions'], ['value' => json_encode($descriptions)]);
BusinessSetting::updateOrCreate(['type' => 'home_banner4_cta_texts'], ['value' => json_encode($ctas)]);
BusinessSetting::updateOrCreate(['type' => 'home_banner4_links'], ['value' => json_encode($links)]);

echo "Successfully updated database\n";
