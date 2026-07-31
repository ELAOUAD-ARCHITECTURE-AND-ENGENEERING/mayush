<?php

namespace App\Services\Bot;

class MessageBuilder
{
    /**
     * Formats a bot response translated to the user's active session language.
     * Implements Section 13 rules (Short, Friendly, Non-jargon, Max 6-10 choices).
     */
    public function build(string $englishTemplate, array $options = [], ?string $language = null): string
    {
        $language = $language ?: app()->getLocale();
        
        $translated = translate($englishTemplate, $language);
        
        if (!empty($options)) {
            $translated .= "\n\n";
            foreach ($options as $index => $opt) {
                // E.g., "1. Find a product"
                $translated .= ($index + 1) . ". " . translate($opt, $language) . "\n";
            }
        }
        
        return $translated;
    }
}
