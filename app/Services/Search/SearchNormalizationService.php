<?php

namespace App\Services\Search;

class SearchNormalizationService
{
    /**
     * Map of Vietnamese accented characters to ASCII equivalents.
     *
     * @var array<string, string>
     */
    private const VIETNAMESE_MAP = [
        'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
        'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
        'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
        'đ' => 'd',
        'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
        'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
        'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
        'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
        'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
        'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
        'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A',
        'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A',
        'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A',
        'Đ' => 'D',
        'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E',
        'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I',
        'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O',
        'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O',
        'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U',
        'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U',
        'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y',
    ];

    /**
     * Remove Vietnamese accents and convert to ASCII representation.
     */
    public static function removeVietnameseAccents(string $text): string
    {
        return strtr($text, self::VIETNAMESE_MAP);
    }

    /**
     * Normalize text for search comparison:
     * - Strips accents
     * - Lowercases
     * - Strips non-alphanumeric characters (replaces with space)
     * - Collapses whitespace
     */
    public static function normalize(string $text): string
    {
        $ascii = self::removeVietnameseAccents($text);
        $lower = mb_strtolower($ascii, 'UTF-8');
        $cleaned = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $lower) ?? '';

        return trim(preg_replace('/\s+/', ' ', $cleaned) ?? '');
    }

    /**
     * Tokenize text into distinct keywords.
     *
     * @return list<string>
     */
    public static function tokenize(string $text): array
    {
        $normalized = self::normalize($text);
        if ($normalized === '') {
            return [];
        }

        $tokens = explode(' ', $normalized);

        return array_values(array_filter($tokens, fn (string $t): bool => mb_strlen($t) > 0));
    }

    /**
     * Check if a candidate token matches a target token with bounded typo distance.
     *
     * Rules:
     * - If query token length < 4: exact prefix or match required.
     * - If query token length >= 4: allow Levenshtein distance <= 1 OR adjacent character transposition.
     */
    public static function matchesTokenWithTypo(string $queryToken, string $targetToken): bool
    {
        if ($queryToken === $targetToken) {
            return true;
        }

        // Prefix match always passes
        if (str_starts_with($targetToken, $queryToken)) {
            return true;
        }

        $qLen = mb_strlen($queryToken);
        $tLen = mb_strlen($targetToken);

        // Don't allow typos for short words (2-3 chars) to avoid noisy approximate matches
        if ($qLen < 4) {
            return false;
        }

        // Check Levenshtein distance (insertion, deletion, substitution) <= 1
        if (abs($qLen - $tLen) <= 1 && levenshtein($queryToken, $targetToken) <= 1) {
            return true;
        }

        // Check adjacent character transposition (e.g. dokcer -> docker)
        if ($qLen === $tLen && self::isAdjacentTransposition($queryToken, $targetToken)) {
            return true;
        }

        // Substring / prefix typo check for compound target tokens
        if ($tLen > $qLen) {
            $targetSub = mb_substr($targetToken, 0, $qLen);
            if (levenshtein($queryToken, $targetSub) <= 1 || self::isAdjacentTransposition($queryToken, $targetSub)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if two strings differ only by a single adjacent character swap.
     */
    public static function isAdjacentTransposition(string $s1, string $s2): bool
    {
        $len = strlen($s1);
        if ($len !== strlen($s2) || $len < 2) {
            return false;
        }

        $diffs = [];
        for ($i = 0; $i < $len; $i++) {
            if ($s1[$i] !== $s2[$i]) {
                $diffs[] = $i;
            }
        }

        if (count($diffs) === 2) {
            [$i, $j] = $diffs;

            return $j === $i + 1 && $s1[$i] === $s2[$j] && $s1[$j] === $s2[$i];
        }

        return false;
    }

    /**
     * Calculate a relevance match score between candidate text and search query:
     *
     * Scores:
     * - 100: Exact normalized match
     * - 85: Text starts with query (Prefix match)
     * - 75: Text contains exact full query as substring
     * - 60: All query tokens matched exactly or as prefix in any order (Reordered tokens)
     * - 40: All query tokens matched with typo tolerance
     * - 0: No match
     *
     * @param  string  $text  The candidate text (e.g. title or description)
     * @param  string  $query  The user input query
     * @return int Match score from 0 to 100
     */
    public static function calculateScore(string $text, string $query): int
    {
        $normText = self::normalize($text);
        $normQuery = self::normalize($query);

        if ($normText === '' || $normQuery === '') {
            return 0;
        }

        // 1. Exact match
        if ($normText === $normQuery) {
            return 100;
        }

        // 2. Starts with query
        if (str_starts_with($normText, $normQuery)) {
            return 85;
        }

        // 3. Contains query
        if (str_contains($normText, $normQuery)) {
            return 75;
        }

        $queryTokens = self::tokenize($normQuery);
        $textTokens = self::tokenize($normText);

        if (empty($queryTokens) || empty($textTokens)) {
            return 0;
        }

        // 4. All query tokens matched in target tokens (exact or prefix, any order)
        $allTokensExactOrPrefix = true;
        foreach ($queryTokens as $qToken) {
            $tokenMatched = false;
            foreach ($textTokens as $tToken) {
                if ($qToken === $tToken || str_starts_with($tToken, $qToken)) {
                    $tokenMatched = true;
                    break;
                }
            }
            if (! $tokenMatched) {
                $allTokensExactOrPrefix = false;
                break;
            }
        }

        if ($allTokensExactOrPrefix) {
            return 60;
        }

        // 5. All query tokens matched with bounded typo tolerance
        $allTokensTypoMatched = true;
        $hasAtLeastOneTypo = false;

        foreach ($queryTokens as $qToken) {
            $tokenMatched = false;
            foreach ($textTokens as $tToken) {
                if (self::matchesTokenWithTypo($qToken, $tToken)) {
                    $tokenMatched = true;
                    if ($qToken !== $tToken && ! str_starts_with($tToken, $qToken)) {
                        $hasAtLeastOneTypo = true;
                    }
                    break;
                }
            }
            if (! $tokenMatched) {
                $allTokensTypoMatched = false;
                break;
            }
        }

        if ($allTokensTypoMatched && $hasAtLeastOneTypo) {
            return 40;
        }

        return 0;
    }

    /**
     * Escape SQL wildcards and format candidate query for bounded database fetching.
     */
    public static function escapeSqlWildcards(string $search): string
    {
        return addcslashes($search, '%_\\');
    }
}
