<?php

namespace Tests\Unit;

use App\Models\User;
use Database\Seeders\HierarchyOtkSeeder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use PHPUnit\Framework\TestCase;

class HierarchyOtkSeederTest extends TestCase
{
    public function test_otk_reporting_lines_match_the_organization_chart(): void
    {
        $tree = HierarchyOtkSeeder::getOtkTree();

        $this->assertContains('FITRI SARI DEWI', $tree['REKTOR']['subordinates']);
        $this->assertContains('SABRI', $tree['REKTOR']['subordinates']);
        $this->assertNotContains('ICE IRAWATI', $tree['REKTOR']['subordinates']);
        $this->assertContains('ICE IRAWATI', $tree['WR_III']['subordinates']);
        $this->assertContains('RONI SAPUTRA', $tree['WR_III']['subordinates']);
        $this->assertContains('ANDI HIDAYATULLAH AZIZAH', $tree['WR_II']['subordinates']);
        $this->assertContains('Ansar', $tree['FST']['subordinates']);
        $this->assertNotContains('Dimas Pradipto', $tree['FST']['subordinates']);
    }

    public function test_each_otk_user_has_only_one_direct_supervisor(): void
    {
        $supervisors = [];

        foreach (HierarchyOtkSeeder::getOtkTree() as $group => $node) {
            foreach ($node['subordinates'] as $subordinate) {
                $this->assertArrayNotHasKey(
                    $subordinate,
                    $supervisors,
                    "{$subordinate} is assigned under multiple OTK groups."
                );

                $supervisors[$subordinate] = $group;
            }
        }
    }

    public function test_uploaded_otk_name_variants_match_the_existing_user_account(): void
    {
        $users = new EloquentCollection([
            new User(['name' => 'OKTAVESA, S.Kom., M.Kom']),
        ]);

        $matchedUser = HierarchyOtkSeeder::resolveUserByOtkName('OKTA VESA, S.Kom., M.Kom', $users);

        $this->assertNotNull($matchedUser);
        $this->assertSame('OKTAVESA, S.Kom., M.Kom', $matchedUser->name);
    }
}
