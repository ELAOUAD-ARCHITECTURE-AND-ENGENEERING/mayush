<?php
echo "Current Dir Files:<pre>";
print_r(scandir('.'));
echo "</pre>";

echo "Parent Dir Files:<pre>";
print_r(scandir('..'));
echo "</pre>";
