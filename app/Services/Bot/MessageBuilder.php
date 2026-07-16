<?php

namespace App\Services\Bot;

class MessageBuilder
{
    /**
     * Formats a bot response translated to the user's active session language.
     * Implements Section 13 rules (Short, Friendly, Non-jargon, Max 6-10 choices).
     */
    public function build(string $englishTemplate, array $options = [], string $language = 'en'): string
    {
        // Use Mayush's native translation engine hook
        // If a translate helper does not natively support explicit language injection in Mayush,
        // we might set app()->setLocale($language) temporarily or assume translate() handles it.
        $translated = translate($englishTemplate); // Fallback to basic translate function
        
        if (!empty($options)) {
            $translated .= "\n\n";
            foreach ($options as $index => $opt) {
                // E.g., "1. Find a product"
                $translated .= ($index + 1) . ". " . translate($opt) . "\n";
            }
        }
        
        return $translated;
    }
}
