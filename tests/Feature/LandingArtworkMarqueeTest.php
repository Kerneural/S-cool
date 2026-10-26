<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingArtworkMarqueeTest extends TestCase
{
    /**
     * Test that the landing page renders successfully with symbolic artwork,
     * genuine product visuals, an automatic logo marquee without a manual pause control, and docs CTA.
     */
    public function test_landing_page_renders_symbolic_artwork_and_marquee_controls(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // AC-01: Original symbolic hero illustration
        $response->assertSee('images/landing/hero-symbolic-mesh.svg');
        $response->assertSee('Build a community where');

        // AC-02: Genuine product visuals from demo content
        $response->assertSee('product-community-feed.png');
        $response->assertSee('product-classroom-outline.png');
        $response->assertSee('product-proof-of-work.png');
        $response->assertSee('Symbolic Knowledge Constellation (original art) with live demo screenshots.');
        $response->assertSee('Member Leaderboard — community engagement points, not practical-work verification.');

        // AC-03 & AC-04: Automatic marquee; owner-requested removal of the manual pause control
        $response->assertDontSee('Pause motion');
        $response->assertDontSee('Resume motion');
        $response->assertDontSee('manualPaused');
        $response->assertSee('animate-marquee');
        $response->assertSee('animate-marquee-reverse');
        $response->assertSee('aria-hidden="true"', false);
        $response->assertSee('tabindex="-1"', false);

        // Learning journey stages and anchor
        $response->assertSee('id="features"', false);
        foreach (['Discover', 'Discuss', 'Learn', 'Practice', 'Review'] as $stage) {
            $response->assertSee($stage);
        }

        // AC-06: Docs CTA points to help.index
        $response->assertSee(route('help.index'));
    }
}
