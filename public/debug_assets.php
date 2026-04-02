<?php
echo file_exists('build/assets/analytics-tracker-BglyRnQE.js') ? 'YES' : 'NO';
echo '<br>';
echo 'Current Dir: ' . getcwd();
echo '<br>';
echo 'Manifest: ' . (file_exists('build/manifest.json') ? 'YES' : 'NO');
