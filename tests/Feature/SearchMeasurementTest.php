<?php

namespace Tests\Feature;

use App\Livewire\CommunitySearchTypeahead;
use App\Models\Community;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SearchMeasurementTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_uses_bounded_sql_candidates_without_per_result_queries(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $user->id, 'name' => 'Basic Docker Academy']);
        Course::factory()->create(['community_id' => $community->id, 'title' => 'Basic Docker Course', 'status' => 'PUBLISHED', 'order' => 0]);
        $this->actingAs($user);
        $counts = [];
        foreach ([10, 200] as $size) {
            Course::factory()->count($size - Course::count())->create(['community_id' => $community->id, 'title' => 'Docker practice fixture', 'status' => 'PUBLISHED', 'order' => 1]);
            foreach (['communities', 'classroom', 'content', 'typeahead'] as $surface) {
                DB::flushQueryLog();
                DB::enableQueryLog();
                $start = hrtime(true);
                match ($surface) {
                    'communities' => $this->get(route('communities.index', ['q' => 'dokcer']))->assertOk()->assertSee('Basic Docker Academy'),
                    'classroom' => $this->get(route('communities.classroom.index', [$community, 'q' => 'dokcer']))->assertOk()->assertSee('Basic Docker Course'),
                    'content' => $this->get(route('communities.search', [$community, 'type' => 'courses', 'q' => 'dokcer']))->assertOk()->assertSee('Basic Docker Course'),
                    'typeahead' => Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])->set('query', 'dokcer')->assertSee('Basic Docker Course'),
                };
                $elapsed = (hrtime(true) - $start) / 1e6;
                $queries = DB::getQueryLog();
                DB::disableQueryLog();
                $counts[$size][$surface] = count($queries);
                $candidateSql = array_values(array_filter(array_column($queries, 'query'), fn ($sql) => str_contains(strtolower($sql), 'limit')));
                $this->assertNotEmpty($candidateSql);
                fwrite(STDOUT, sprintf("\nSEARCH_OBSERVATION dataset=scool_test communities=1 courses=%d surface=%s queries=%d elapsed_ms=%.2f sql_limits=%s\n", $size, $surface, count($queries), $elapsed, json_encode(array_values(array_unique(array_filter(array_map(function ($sql) {
                    preg_match('/limit (\d+)/i', $sql, $match);

                    return $match[1] ?? null;
                }, $candidateSql)))))));
            }
        }
        foreach ($counts[10] as $surface => $smallCount) {
            $this->assertLessThanOrEqual($smallCount + 2, $counts[200][$surface], 'Query count must not grow per result on '.$surface);
        }
    }
}
