<?php

namespace Tests\Unit;

use App\Services\Search\SearchNormalizationService;
use PHPUnit\Framework\TestCase;

class SearchNormalizationServiceTest extends TestCase
{
    public function test_removes_vietnamese_accents_accurately(): void
    {
        $this->assertSame('Khoa hoc Lap trinh Web', SearchNormalizationService::removeVietnameseAccents('Khóa học Lập trình Web'));
        $this->assertSame('Da Nang', SearchNormalizationService::removeVietnameseAccents('Đà Nẵng'));
        $this->assertSame('Toi yeu Viet Nam', SearchNormalizationService::removeVietnameseAccents('Tôi yêu Việt Nam'));
    }

    public function test_normalizes_case_punctuation_and_whitespace(): void
    {
        $this->assertSame('basic docker course', SearchNormalizationService::normalize('  Basic   Docker: Course!  '));
        $this->assertSame('lap trinh python', SearchNormalizationService::normalize('Lập Trình - Python!'));
        $this->assertSame('lap trinh python 1', SearchNormalizationService::normalize('Lập Trình - Python #1'));
    }

    public function test_tokenizes_text_into_clean_words(): void
    {
        $this->assertSame(['basic', 'docker', 'course'], SearchNormalizationService::tokenize('Basic Docker Course'));
        $this->assertSame(['khoa', 'hoc'], SearchNormalizationService::tokenize('Khóa học'));
        $this->assertSame([], SearchNormalizationService::tokenize('   '));
    }

    public function test_matches_token_with_bounded_typo_and_transposition(): void
    {
        // Exact
        $this->assertTrue(SearchNormalizationService::matchesTokenWithTypo('docker', 'docker'));

        // Prefix
        $this->assertTrue(SearchNormalizationService::matchesTokenWithTypo('dock', 'docker'));

        // Transposition (dokcer -> docker)
        $this->assertTrue(SearchNormalizationService::matchesTokenWithTypo('dokcer', 'docker'));

        // Levenshtein distance = 1 (deletion/insertion)
        $this->assertTrue(SearchNormalizationService::matchesTokenWithTypo('doker', 'docker'));
        $this->assertTrue(SearchNormalizationService::matchesTokenWithTypo('dockerr', 'docker'));

        // Distance > 1 should fail
        $this->assertFalse(SearchNormalizationService::matchesTokenWithTypo('dokr', 'docker'));
        $this->assertFalse(SearchNormalizationService::matchesTokenWithTypo('kubernetes', 'docker'));

        // Short words (< 4 chars) require exact match or prefix, reject typo
        $this->assertTrue(SearchNormalizationService::matchesTokenWithTypo('ai', 'ai'));
        $this->assertTrue(SearchNormalizationService::matchesTokenWithTypo('py', 'python'));
        $this->assertFalse(SearchNormalizationService::matchesTokenWithTypo('ia', 'ai'));
    }

    public function test_calculates_relevance_scores_and_prioritizes_exact_over_approximate(): void
    {
        $target = 'Basic Docker Course';

        // 1. Exact match (Score 100)
        $exactScore = SearchNormalizationService::calculateScore($target, 'Basic Docker Course');
        $this->assertSame(100, $exactScore);

        // 2. Starts with (Score 85)
        $prefixScore = SearchNormalizationService::calculateScore($target, 'Basic Docker');
        $this->assertSame(85, $prefixScore);

        // 3. Substring (Score 75)
        $subScore = SearchNormalizationService::calculateScore($target, 'Docker Course');
        $this->assertSame(75, $subScore);

        // 4. Reordered tokens (Score 60)
        $reorderedScore = SearchNormalizationService::calculateScore($target, 'course basic');
        $this->assertSame(60, $reorderedScore);

        // 5. Typo match (Score 40)
        $typoScore = SearchNormalizationService::calculateScore($target, 'dokcer basic');
        $this->assertSame(40, $typoScore);

        // Priority assertion: Exact > Prefix > Substring > Reordered > Typo
        $this->assertGreaterThan($prefixScore, $exactScore);
        $this->assertGreaterThan($subScore, $prefixScore);
        $this->assertGreaterThan($reorderedScore, $subScore);
        $this->assertGreaterThan($typoScore, $reorderedScore);

        // 6. Completely unrelated query returns 0 (No noise)
        $unrelatedScore = SearchNormalizationService::calculateScore($target, 'Machine Learning Python');
        $this->assertSame(0, $unrelatedScore);
    }

    public function test_handles_vietnamese_accent_and_no_accent_equivalence(): void
    {
        $target = 'Khóa học Lập trình Cơ bản';

        // Same score whether user types accents or not
        $scoreWithAccents = SearchNormalizationService::calculateScore($target, 'Khóa học Lập trình');
        $scoreWithoutAccents = SearchNormalizationService::calculateScore($target, 'Khoa hoc Lap trinh');

        $this->assertSame(85, $scoreWithAccents);
        $this->assertSame(85, $scoreWithoutAccents);
    }
}
