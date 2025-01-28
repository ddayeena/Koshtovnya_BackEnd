<?php

namespace App\Services\Product;

class ReviewService
{

    public function filterBadWords(string $text): string
    {
        //Get bad words
        $badWords = array_merge(config('badwords.english'), config('badwords.ukrainian'));
        $pattern = array_map(fn($word) => '/\b' . preg_quote($word, '/') . '\b/iu', $badWords); 
        return preg_replace($pattern, '****', $text);
    }

}
