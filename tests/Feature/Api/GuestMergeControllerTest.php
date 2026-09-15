<?php

namespace Tests\Feature\Api;

use App\Models\Hoarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestMergeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_merge_blocks_items_from_different_vendor_when_vendor_id_is_provided(): void
    {
        $user = User::factory()->create();
        $sameVendorHoarding = Hoarding::factory()->create(['vendor_id' => 10]);
        $differentVendorHoarding = Hoarding::factory()->create(['vendor_id' => 20]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('guest.merge'), [
                'wishlist' => [$sameVendorHoarding->id, $differentVendorHoarding->id],
                'cart' => [$differentVendorHoarding->id],
                'vendor_id' => 10,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('message', 'Some hoardings belong to a different vendor and were not merged.')
            ->assertJsonPath('different_vendor_hoardings.wishlist.0', $differentVendorHoarding->id)
            ->assertJsonPath('different_vendor_hoardings.cart.0', $differentVendorHoarding->id);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'hoarding_id' => $sameVendorHoarding->id,
        ]);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'hoarding_id' => $differentVendorHoarding->id,
        ]);

        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id,
            'hoarding_id' => $differentVendorHoarding->id,
        ]);
    }
}
