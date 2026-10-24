<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingArtworkMarqueeTest extends TestCase
{
    /**
     * Test that the landing page renders successfully with symbolic artwork,
     * genuine product visuals, accessible logo marquee controls, and docs CTA.
     */
    public function test_landing_page_renders_symbolic_artwork_and_marquee_controls(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // AC-01: Original symbolic hero illustration
        $response->assertSee('images/landing/hero-symbolic-mesh.svg');
        $response->assertSee('S-cool Knowledge, Community and Proof-of-Work Symbolic Constellation');

        // AC-02: Genuine product visuals from demo content
        $response->assertSee('product-community-feed.png');
        $response->assertSee('product-classroom-outline.png');
        $response->assertSee('product-proof-of-work.png');
        $response->assertSee('Symbolic Knowledge Constellation (original art) with live demo screenshots.');

        // AC-03 & AC-04: Accessible logo marquee with manual pause control and reduced motion
        $response->assertSee(':aria-pressed="manualPaused ? \'true\' : \'false\'"', false);
        $response->assertSee('Pause motion');
        $response->assertSee('Resume motion');
        $response->assertSee('animate-marquee');
        $response->assertSee('animate-marquee-reverse');
        $response->assertSee('marquee-paused');
        $response->assertSee('aria-hidden="true"', false);
        $response->assertSee('tabindex="-1"', false);

        // AC-06: Docs CTA points to help.index
        $response->assertSee(route('help.index'));
    }
}
