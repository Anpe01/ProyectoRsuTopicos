<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Brandmodel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandmodelUniqueRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_compuesto_brand_y_name(): void
    {
        $brand = Brand::create(['name' => 'MarcaU', 'description' => 'Prueba']);
        Brandmodel::create(['brand_id' => $brand->id, 'name' => 'M1', 'description' => 'Desc']);

        $exists = Brandmodel::where('brand_id', $brand->id)->where('name', 'M1')->exists();
        $this->assertTrue($exists);
    }
}














