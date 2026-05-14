<?php

if (!function_exists('renderBioMarkdown')) {
    /**
     * Safely render a user bio with limited markdown:
     *   **bold**, *italic*, ~~strikethrough~~, newlines → <br>
     * All HTML is stripped first (no XSS), no links allowed.
     */
    function renderBioMarkdown(string $text): string
    {
        // 1. Strip all HTML to neutralise any injection attempt
        $text = strip_tags($text);

        // 2. Escape HTML entities so raw characters are safe
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // 3. Bold: **text** → <strong>
        $text = preg_replace('/\*\*(.+?)\*\*/su', '<strong>$1</strong>', $text);

        // 4. Italic: *text* (not part of **) → <em>
        $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/su', '<em>$1</em>', $text);

        // 5. Strikethrough: ~~text~~ → <s>
        $text = preg_replace('/~~(.+?)~~/su', '<s>$1</s>', $text);

        // 6. Convert newlines to <br> for display
        $text = nl2br($text);

        return $text;
    }
}
