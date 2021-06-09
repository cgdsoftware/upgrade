<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use LaravelEnso\TestUpgrade\Upgrades\Deep\DeepUpgrade;
use LaravelEnso\TestUpgrade\Upgrades\POPO;
use LaravelEnso\TestUpgrade\Upgrades\SimpleUpgrade;
use LaravelEnso\TestUpgrade\Upgrades\StructureUpgrade;
use LaravelEnso\Upgrade\Contracts\MigratesStructure;
use LaravelEnso\Upgrade\Services\Finder;
use LaravelEnso\Upgrade\Services\Structure;
use Tests\TestCase;

class FinderTest extends TestCase
{
    use RefreshDatabase;

    protected MigratesStructure $upgrade;
    protected $defaultRole;
    protected $secondaryRole;

    public function setUp(): void
    {
        parent::setUp();

        File::copyDirectory(__DIR__.'/../stubs', $this->package());
        $this->register();
        Config::set('enso.upgrade.folders', ['vendor/laravel-enso/testUpgrades']);
        Config::set('enso.upgrade.vendors', []);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->package());
    }

    /** @test */
    public function should_not_find_classes_dont_implement_contracts()
    {
        $this->assertEmpty($this->getUpgrade(POPO::class));
    }

    /** @test */
    public function can_find_structure_upgrade()
    {
        $structureUpgrade = $this->getUpgrade(Structure::class)
            ->first()->reflection()->getName();

        $this->assertEquals(StructureUpgrade::class, $structureUpgrade);
    }

    /** @test */
    public function can_find_regular_upgrade()
    {
        $this->assertNotEmpty($this->getUpgrade(SimpleUpgrade::class));
    }

    /** @test */
    public function can_find_deep_upgrade()
    {
        $this->assertNotEmpty($this->getUpgrade(DeepUpgrade::class));
    }

    protected function register(): void
    {
        $loader = require base_path().'/vendor/autoload.php';
        $loader->setPsr4(
            'LaravelEnso\TestUpgrade\\',
            $this->package('src')
        );
    }

    protected function getUpgrade(string $class): Collection
    {
        return (new Finder())->upgrades()
            ->filter(fn ($upgrade) => $upgrade::class === $class);
    }

    private function package(...$folders): string
    {
        return base_path(
            Collection::wrap(['vendor/laravel-enso/testUpgrades', ...$folders])
                ->implode(DIRECTORY_SEPARATOR)
        );
    }
}
