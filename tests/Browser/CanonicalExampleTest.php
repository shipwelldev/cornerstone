<?php

declare(strict_types=1);

test('the planner moves between its server-owned steps', function (): void {
    $page = visit(route('home'));

    $page->assertSee('Define the expedition')
        ->assertNoJavaScriptErrors()
        ->assertNoAccessibilityIssues()
        ->fill('callSign', 'Aurora Seven')
        ->wait(0.2)
        ->select('destination', 'glass-nebula')
        ->wait(0.2)
        ->fill('crewSize', '4')
        ->wait(0.2)
        ->fill('durationInDays', '45')
        ->wait(0.2)
        ->select('missionPurpose', 'research')
        ->wait(0.2)
        ->click('Plan expedition')
        ->assertSee('Aurora Seven')
        ->assertSee('Elevated risk')
        ->assertSee('Mission advisory')
        ->wait(0.1)
        ->assertScript('document.activeElement.id', 'expedition-plan-heading')
        ->click('Revise mission brief')
        ->assertSee('Define the expedition')
        ->assertValue('callSign', 'Aurora Seven')
        ->wait(0.1)
        ->assertScript('document.activeElement.id', 'call-sign');
});

test('the faq uses accessible browser-owned disclosure state', function (): void {
    $page = visit(route('home'));

    $faqToggle = '[data-test="faq-toggle"]:has-text("Why is this fictional planner included?")';

    $page->assertAriaAttribute($faqToggle, 'expanded', 'false')
        ->click('Why is this fictional planner included?')
        ->assertAriaAttribute($faqToggle, 'expanded', 'true')
        ->assertVisible('#faq-panel-1')
        ->assertNoJavaScriptErrors();
});

test('the canonical example remains usable on mobile', function (): void {
    visit(route('home'))
        ->on()->iPhone14Pro()
        ->assertSee('Expedition planner')
        ->assertSee('Define the expedition')
        ->assertNoJavaScriptErrors();
});
