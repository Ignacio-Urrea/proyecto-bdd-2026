<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    #[Test]
    public function managers_and_reports_are_each_configured_as_a_single_menu_group(): void
    {
        $groups = collect(config('imaj.navigation'))
            ->filter(static fn (array $item): bool => isset($item['children']))
            ->keyBy('label');

        $this->assertSame(['Gestores', 'Reportes'], $groups->keys()->all());
        $this->assertCount(5, $groups->get('Gestores')['children']);
        $this->assertCount(3, $groups->get('Reportes')['children']);
    }
}
