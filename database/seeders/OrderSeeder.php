<?php
namespace Database\Seeders;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = ['pendente', 'entregue', 'cancelado'];

        for ($i = 1; $i <= 20; $i++) {
            Order::create([
                'customer_name' => fake()->name(),
                'order_date'    => Carbon::now()->subDays(rand(0, 15)),
                'delivery_date' => rand(0, 1) ? Carbon::now()->addDays(rand(1, 10)) : null,
                'status'        => $statuses[array_rand($statuses)],
            ]);

        }
    }
}
