<?php

namespace App\Services\Product;

use App\Mail\BanMail;
use Illuminate\Support\Facades\Mail;

class ReviewService
{

    public function filterBadWords(string $text): string
    {
        //Get bad words
        $badWords = array_merge(config('badwords.english'), config('badwords.ukrainian'));
        $pattern = array_map(fn($word) => '/\b' . preg_quote($word, '/') . '\b/iu', $badWords);
        return preg_replace($pattern, '****', $text);
    }

    public function hasBadWords(string $text): bool
    {
        $badWords = array_merge(config('badwords.english'), config('badwords.ukrainian'));
        foreach ($badWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/iu', $text)) {
                return true;
            }
        }
        return false;
    }

    public function handleBadContent($user): ?\Illuminate\Http\JsonResponse
    {
        $user->warnings_count += 1;

        if ($user->warnings_count >= 3) {
            if ($user->was_banned_before) {
                $user->is_permanently_banned = true;
                $user->banned_until = null;
                $user->access = 0;
            } else {
                $user->banned_until = now()->addDays(3);
                $user->was_banned_before = true;
                $user->access = 0;
            }

            Mail::to($user->email)->send(new BanMail($user));
            $user->warnings_count = 0;

            if ($user->role !== 'user') {
                $user->role = 'user';
            }

            $user->save();

            // Видалити всі токени
            $user->tokens()->delete();

            return response()->json([
                'message' => $user->is_permanently_banned
                    ? 'Вас заблоковано назавжди за повторне порушення правил.'
                    : 'Вас заблоковано на 3 дні за порушення правил.'
            ], 403);
        }

        $user->save();

        return response()->json([
            'message' => "Ваш коментар містить заборонені слова. Попередження {$user->warnings_count} з 3."
        ], 422);
    }
}
