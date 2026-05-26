<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RestaurantTable;
use App\Models\Setting;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'COARSE Admin',
            'email' => 'admin@coarse.test',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $outlet = Outlet::query()->create([
            'name' => 'Main Restaurant',
            'location' => 'Nairobi',
        ]);

        foreach ([
            'business_name' => 'COARSE Restaurant POS',
            'currency' => 'KES',
            'tax_rate' => 0,
            'allow_negative_inventory' => false,
            'mpesa_paybill' => '000000',
            'etims_enabled' => false,
        ] as $key => $value) {
            Setting::query()->create(compact('key', 'value'));
        }

        $mains = Category::query()->create(['name' => 'Mains', 'type' => 'menu']);
        $sides = Category::query()->create(['name' => 'Sides', 'type' => 'menu']);
        $drinks = Category::query()->create(['name' => 'Drinks', 'type' => 'menu']);
        $desserts = Category::query()->create(['name' => 'Desserts', 'type' => 'menu']);
        $breakfast = Category::query()->create(['name' => 'Breakfast', 'type' => 'menu']);
        $barista = Category::query()->create(['name' => 'Barista Corner', 'type' => 'menu']);
        $teaChocolate = Category::query()->create(['name' => 'Tea & Chocolate', 'type' => 'menu']);
        $icedTea = Category::query()->create(['name' => 'Iced Tea', 'type' => 'menu']);
        $icedCoffee = Category::query()->create(['name' => 'Iced Coffee', 'type' => 'menu']);
        $snacks = Category::query()->create(['name' => 'Snacks', 'type' => 'menu']);
        $salads = Category::query()->create(['name' => 'Salads', 'type' => 'menu']);
        $soups = Category::query()->create(['name' => 'Soups', 'type' => 'menu']);
        $burgers = Category::query()->create(['name' => 'Burgers', 'type' => 'menu']);
        $sandwiches = Category::query()->create(['name' => 'Sandwiches', 'type' => 'menu']);
        $pizza = Category::query()->create(['name' => 'Pizza', 'type' => 'menu']);
        $kids = Category::query()->create(['name' => 'Kids Menu', 'type' => 'menu']);
        $lemonades = Category::query()->create(['name' => 'Lemonades & Juices', 'type' => 'menu']);
        $milkshakes = Category::query()->create(['name' => 'Milkshakes', 'type' => 'menu']);
        $mocktails = Category::query()->create(['name' => 'Mocktails', 'type' => 'menu']);
        $cocktails = Category::query()->create(['name' => 'Cocktails', 'type' => 'menu']);
        $bar = Category::query()->create(['name' => 'Bar', 'type' => 'menu']);
        $raw = Category::query()->create(['name' => 'Raw Materials', 'type' => 'inventory']);

        $rice = Product::query()->create([
            'sku' => 'RAW-RICE',
            'name' => 'Rice',
            'category_id' => $raw->id,
            'product_type' => 'raw_material',
            'unit' => 'kg',
            'cost_price' => 180,
            'reorder_level' => 10,
        ]);

        $beef = Product::query()->create([
            'sku' => 'RAW-BEEF',
            'name' => 'Beef',
            'category_id' => $raw->id,
            'product_type' => 'raw_material',
            'unit' => 'kg',
            'cost_price' => 650,
            'reorder_level' => 8,
        ]);

        $oil = Product::query()->create([
            'sku' => 'RAW-OIL',
            'name' => 'Cooking Oil',
            'category_id' => $raw->id,
            'product_type' => 'raw_material',
            'unit' => 'litre',
            'cost_price' => 320,
            'reorder_level' => 5,
        ]);

        $pilau = Product::query()->create([
            'sku' => 'MEAL-PILAU',
            'barcode' => '100001',
            'name' => 'Pilau Plate',
            'category_id' => $mains->id,
            'product_type' => 'finished_product',
            'unit' => 'plate',
            'cost_price' => 135,
            'selling_price' => 450,
        ]);

        Product::query()->create([
            'sku' => 'MEAL-BEEF-STEW',
            'barcode' => '100002',
            'name' => 'Beef Stew',
            'category_id' => $mains->id,
            'product_type' => 'finished_product',
            'unit' => 'plate',
            'cost_price' => 180,
            'selling_price' => 520,
        ]);

        Product::query()->create([
            'sku' => 'SIDE-CHAPATI',
            'barcode' => '200001',
            'name' => 'Chapati',
            'category_id' => $sides->id,
            'product_type' => 'finished_product',
            'unit' => 'pcs',
            'cost_price' => 25,
            'selling_price' => 80,
        ]);

        Product::query()->create([
            'sku' => 'SIDE-FRIES',
            'barcode' => '200002',
            'name' => 'Masala Fries',
            'category_id' => $sides->id,
            'product_type' => 'finished_product',
            'unit' => 'plate',
            'cost_price' => 95,
            'selling_price' => 280,
        ]);

        $soda = Product::query()->create([
            'sku' => 'DRINK-SODA500',
            'barcode' => '300001',
            'name' => 'Soda 500ml',
            'category_id' => $drinks->id,
            'product_type' => 'resale_item',
            'unit' => 'pcs',
            'cost_price' => 55,
            'selling_price' => 120,
            'reorder_level' => 24,
        ]);

        $water = Product::query()->create([
            'sku' => 'DRINK-WATER500',
            'barcode' => '300002',
            'name' => 'Water 500ml',
            'category_id' => $drinks->id,
            'product_type' => 'resale_item',
            'unit' => 'pcs',
            'cost_price' => 30,
            'selling_price' => 80,
            'reorder_level' => 24,
        ]);

        Product::query()->create([
            'sku' => 'DESSERT-CAKE',
            'barcode' => '400001',
            'name' => 'Chocolate Cake Slice',
            'category_id' => $desserts->id,
            'product_type' => 'finished_product',
            'unit' => 'slice',
            'cost_price' => 90,
            'selling_price' => 260,
        ]);

        Product::query()->create([
            'sku' => 'BREAKFAST-TEA',
            'barcode' => '500001',
            'name' => 'Kenyan Tea',
            'description' => 'Classic Kenyan tea served hot.',
            'category_id' => $teaChocolate->id,
            'subcategory' => 'Single',
            'product_type' => 'finished_product',
            'unit' => 'cup',
            'cost_price' => 35,
            'selling_price' => 250,
        ]);

        $pilau->update(['subcategory' => 'House specials', 'description' => 'Spiced rice plate with beef, oil and house seasoning. Recipe-linked test item.']);
        $soda->update(['subcategory' => 'Soft drinks 500ml', 'description' => 'Bottled soda, 500 ml.']);
        $water->update(['subcategory' => 'Mineral water', 'description' => 'Mineral water, 500 ml.']);

        $menuBarcode = 600000;
        $addMenuItem = function (Category $category, string $subcategory, string $name, int $price, string $description = '', string $unit = 'plate') use (&$menuBarcode): Product {
            $menuBarcode++;

            return Product::query()->create([
                'sku' => 'MENU-' . $menuBarcode,
                'barcode' => (string) $menuBarcode,
                'name' => $name,
                'description' => $description,
                'category_id' => $category->id,
                'subcategory' => $subcategory,
                'product_type' => 'finished_product',
                'unit' => $unit,
                'cost_price' => round($price * 0.35, 2),
                'selling_price' => $price,
            ]);
        };

        foreach ([
            ['Single', 'Espresso', 200, 'Classic espresso shot.', 'cup'],
            ['Double', 'Espresso Double', 250, 'Double espresso shot.', 'cup'],
            ['Single', 'Americano', 250, 'Espresso topped with hot water.', 'cup'],
            ['Double', 'Americano Double', 300, 'Double espresso Americano.', 'cup'],
            ['Single', 'Macchiato', 300, 'Espresso marked with milk foam.', 'cup'],
            ['Double', 'Macchiato Double', 350, 'Double macchiato.', 'cup'],
            ['Single', 'Cappuccino', 310, 'Espresso with steamed milk and foam.', 'cup'],
            ['Double', 'Cappuccino Double', 360, 'Double cappuccino.', 'cup'],
            ['Single', 'Caffe Latte', 320, 'Smooth coffee with steamed milk.', 'cup'],
            ['Double', 'Caffe Latte Double', 370, 'Double latte.', 'cup'],
            ['Single', 'Mocha', 350, 'Coffee with chocolate and steamed milk.', 'cup'],
            ['Double', 'Mocha Double', 400, 'Double mocha.', 'cup'],
            ['Single', 'RM Bistro Spicy Macchiato', 350, 'House spicy macchiato.', 'cup'],
            ['Double', 'RM Bistro Spicy Macchiato Double', 420, 'House spicy double macchiato.', 'cup'],
            ['Add-ons', 'Flavoured Syrup', 100, 'Vanilla, caramel or hazelnut syrup.', 'portion'],
            ['Add-ons', 'Shot of Espresso', 150, 'Extra espresso shot.', 'shot'],
        ] as [$sub, $name, $price, $desc, $unit]) {
            $addMenuItem($barista, $sub, $name, $price, $desc, $unit);
        }

        foreach ([
            ['Single', 'Kenyan Tea Double', 300, 'Classic Kenyan tea, double serving.', 'cup'],
            ['Single', 'Masala Tea', 250, 'Spiced masala tea.', 'cup'],
            ['Double', 'Masala Tea Double', 300, 'Double masala tea.', 'cup'],
            ['Single', 'Tangawizi Tea', 250, 'Ginger tea served hot.', 'cup'],
            ['Double', 'Tangawizi Tea Double', 300, 'Double ginger tea.', 'cup'],
            ['House drinks', 'RM Bistro Dawa', 350, 'Hot lemon and ginger with honey.', 'cup'],
            ['Single', 'Herbal Tea', 250, 'Green, chamomile, peppermint or hibiscus.', 'cup'],
            ['Double', 'Herbal Tea Double', 300, 'Double herbal tea.', 'cup'],
            ['Single', 'Hot Chocolate', 350, 'Hot chocolate.', 'cup'],
            ['Double', 'Hot Chocolate Double', 400, 'Double hot chocolate.', 'cup'],
            ['Add-ons', 'Whipped Cream', 100, 'Add whipped cream.', 'portion'],
            ['Milk', 'Glass of Milk', 220, 'Glass of milk.', 'glass'],
        ] as [$sub, $name, $price, $desc, $unit]) {
            $addMenuItem($teaChocolate, $sub, $name, $price, $desc, $unit);
        }

        foreach ([
            ['Iced tea', 'Iced Tea', 360, 'Classic iced tea.', 'glass'],
            ['Iced tea', 'Iced Lemon & Ginger', 360, 'Iced tea with lemon and ginger.', 'glass'],
            ['Iced tea', 'Iced Herbal Tea', 360, 'Iced herbal tea.', 'glass'],
            ['Iced tea', 'Iced Tea with Fresh Juice', 400, 'Iced tea mixed with fresh juice.', 'glass'],
            ['Iced tea', 'Iced Arnold Palmer', 400, 'Half iced tea and half lemonade.', 'glass'],
            ['Iced coffee', 'Iced Coffee', 350, 'Chilled coffee served over ice.', 'glass'],
            ['Iced coffee', 'Iced Latte', 400, 'Chilled latte.', 'glass'],
            ['Iced coffee', 'Iced Mocha', 450, 'Chilled mocha.', 'glass'],
            ['Iced coffee', 'Iced RM Bistro Spicy Macchiato', 450, 'Iced house spicy macchiato.', 'glass'],
        ] as [$sub, $name, $price, $desc, $unit]) {
            $addMenuItem(str_contains($sub, 'coffee') ? $icedCoffee : $icedTea, $sub, $name, $price, $desc, $unit);
        }

        foreach ([
            ['Breakfast', 'Crepes', 250, 'Four delicate crepes drizzled with rich chocolate sauce and powdered sugar.', 'plate'],
            ['Egg station', 'Egg Station', 450, 'Eggs of your style served with toast.', 'plate'],
            ['Breakfast', 'French Toast', 580, 'Artisanal French toast served with maple syrup and butter.', 'plate'],
            ['Breakfast', 'Waffles or Pancakes', 630, 'Waffles or pancakes with whipped cream and syrup of choice.', 'plate'],
            ['Breakfast', 'Full RM Bistro Breakfast', 880, 'Two eggs with bacon, sausage, baked beans, tomato wedge, hash brown and toast.', 'plate'],
            ['Breakfast Add-ons', 'Bacon Add-on', 150, 'Breakfast bacon add-on.', 'portion'],
            ['Breakfast Add-ons', 'Toast Add-on', 100, 'Breakfast toast add-on.', 'portion'],
        ] as [$sub, $name, $price, $desc, $unit]) {
            $addMenuItem($breakfast, $sub, $name, $price, $desc, $unit ?? 'plate');
        }

        foreach ([
            ['Snacks', 'Sausages', 200, 'Two sausages of your choice served with a special sauce.'],
            ['Snacks', 'Samosas', 450, 'Four traditional samosas served with a special sauce.'],
            ['Snacks', 'Mini Shrimp Skewers', 500, 'Grilled shrimp seasoned and served with tartar sauce.'],
            ['Snacks', 'Chicken Wings', 650, 'Crispy tender wings tossed in plain, BBQ or teriyaki sauce.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($snacks, $sub, $name, $price, $desc);
        }

        foreach ([
            ['Salads', 'Moroccan Butternut', 1190, 'Roasted butternut, chickpeas, cherry tomatoes, feta cheese and honey mustard dressing.'],
            ['Salads', 'Caesar Salad', 1390, 'Grilled chicken slices, romaine lettuce, croutons, parmesan cheese and caesar dressing.'],
            ['Soups', 'Butternut Soup', 750, 'Creamy vegetable soup served with bread.'],
            ['Soups', 'Tomato & Basil Soup', 800, 'Roasted tomatoes and basil served with a grilled cheese sandwich.'],
            ['Soups', 'Chicken Soup', 900, 'Slow cooked hearty broth served with bread.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($sub === 'Soups' ? $soups : $salads, $sub, $name, $price, $desc);
        }

        foreach ([
            ['Burgers', 'Fried Chicken Burger', 1100, 'Crispy fried chicken fillet in a soft bun with cheese, tangy mayo and lettuce. Served with fries and coleslaw.'],
            ['Burgers', 'Beef Burger', 1200, 'Grilled beef patty on toasted bun with cheese, lettuce, onions, tomatoes, pickles and special sauce.'],
            ['Sandwiches', 'Cheese & Tomato Sandwich', 700, 'Melted cheese and tomato on bread of choice with fries.'],
            ['Sandwiches', 'Halloumi Sandwich', 710, 'Grilled halloumi slices with bell peppers and pesto served with fries.'],
            ['Sandwiches', 'Bacon, Lettuce & Tomato Sandwich', 800, 'Bacon, lettuce and tomato served with fries.'],
            ['Sandwiches', 'Chicken Curry Sandwich', 950, 'Chicken cooked in curry spices served on bread of choice with fries.'],
            ['Sandwiches', 'Beef Steak Sandwich', 1100, 'Slow roasted steak sandwich accompanied by fries.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($sub === 'Burgers' ? $burgers : $sandwiches, $sub, $name, $price, $desc);
        }

        foreach ([
            ['Pizza', 'Margherita Pizza', 1050, 'Thin crust pizza topped with mozzarella, ripe tomatoes, basil and olive oil.'],
            ['Pizza', 'Vegetarian Pizza', 1150, 'Roasted peppers, mushrooms, mozzarella and marinara sauce.'],
            ['Pizza', 'Chicken Pizza', 1250, 'Grilled chicken with barbecue sauce, red onions and mozzarella.'],
            ['Pizza', 'Ham & Cheese Pizza', 1350, 'Ham and gooey cheese over a classic tomato base.'],
            ['Pizza', 'Meat-Lovers Pizza', 1400, 'Pizza with minced beef, sausage, ham and bacon.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($pizza, $sub, $name, $price, $desc);
        }

        foreach ([
            ['Chicken', '1/4 Chicken', 800, 'Quarter chicken marinated and grilled, served with fries and coleslaw.'],
            ['Curry', 'Vegetable Curry', 800, 'Seasonal vegetables in creamy spiced sauce with noodles or white rice.'],
            ['Meat', 'Goat Meat', 950, 'Tender spice-marinated goat meat slow cooked and served with ugali, fries or chapati.'],
            ['Stir-fry', 'Teriyaki Chicken Stir-Fry', 1000, 'Chicken with vegetables in savory Asian sauce served with fried rice or noodles. Contains nuts.'],
            ['Pasta', 'Pasta Bolognese', 1050, 'Slow-cooked Bolognese sauce with ground beef, pasta and parmesan.'],
            ['Pork', 'Pork Spareribs', 1100, 'Slow-cooked ribs glazed with smoky BBQ sauce and served with a side.'],
            ['Chicken', 'Mushroom Chicken Breast', 1100, 'Grilled chicken breast in creamy mushroom sauce.'],
            ['Stir-fry', 'Peppery Beef Stir-Fry', 1150, 'Pepper steak with bell peppers and onions in bold black pepper sauce.'],
            ['Curry', 'RM Bistro Special Chicken Curry', 1150, 'Spiced creamy chicken curry with rice or raita.'],
            ['Fish', 'Swahili Coconut Fish Curry', 1180, 'Fresh fish in coconut milk with coastal Swahili spices.'],
            ['Seafood', 'Garlic Shrimp Stir-Fry', 1250, 'Shrimp with garlic and vegetables in tangy glaze.'],
            ['Fish', 'Grilled Tilapia & Creamed Spinach', 1320, 'Tilapia fillet with creamy spinach and tartar sauce.'],
            ['Steak', 'Rib Eye', 1380, 'Marbled steak grilled to preferred doneness with sauce and sides.'],
            ['Fish', 'Grilled Salmon with Lemon-Dill Sauce', 1850, 'Grilled salmon fillet with roast potatoes and sauteed vegetables.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($mains, $sub, $name, $price, $desc);
        }

        foreach ([
            ['Kids', 'Chicken Nuggets', 550, 'Crispy chicken coated in breadcrumbs served with fries and sauce.'],
            ['Kids', 'Fish Fingers', 600, 'Breaded tilapia fillet served with fries and tartar sauce.'],
            ['Kids', 'Mac & Cheese', 700, 'Creamy mac and cheese with breadcrumb crust.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($kids, $sub, $name, $price, $desc);
        }

        foreach ([
            ['Sides', 'Creamed Spinach', 150, 'Creamed spinach side.'],
            ['Sides', 'Sauteed Greens', 150, 'Sauteed greens side.'],
            ['Sides', 'Steamed Vegetables', 180, 'Steamed vegetables side.'],
            ['Sides', 'Chapati Side', 180, 'Chapati side.'],
            ['Sides', 'White Rice', 220, 'White rice side.'],
            ['Sides', 'White Ugali', 220, 'White ugali side.'],
            ['Sides', 'Vegetable Fried Rice', 240, 'Vegetable fried rice side.'],
            ['Sides', "RM Bistro Chef's Special Rice", 240, 'House special rice side.'],
            ['Sides', 'Fries', 250, 'Fries side.'],
            ['Sides', 'Side Salad', 250, 'Side salad.'],
            ['Sides', 'Mashed Potatoes', 250, 'Mashed potatoes side.'],
            ['Sides', 'Potato Wedges', 250, 'Potato wedges side.'],
            ['Sides', 'Roast Potatoes', 250, 'Roast potatoes side.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($sides, $sub, $name, $price, $desc, 'portion');
        }

        foreach ([
            ['Lemonades', 'Classic Lemonade', 350, 'Freshly squeezed classic lemonade.', 'glass'],
            ['Lemonades', 'Strawberry Lemonade', 400, 'Freshly squeezed strawberry lemonade.', 'glass'],
            ['Juices', 'Fresh Juice', 400, 'Freshly squeezed tropical, orange, mango, pineapple, pineapple mint or orange mint juice.', 'glass'],
            ['Milkshakes', 'Vanilla Milkshake', 500, 'Vanilla milkshake.', 'glass'],
            ['Milkshakes', 'Chocolate Milkshake', 500, 'Chocolate milkshake.', 'glass'],
            ['Milkshakes', 'Strawberry Milkshake', 500, 'Strawberry milkshake.', 'glass'],
            ['Milkshakes', 'Blueberry Milkshake', 500, 'Blueberry milkshake.', 'glass'],
            ['Milkshakes', 'Oreo Milkshake', 500, 'Oreo milkshake.', 'glass'],
            ['Milkshakes', 'Raspberry Milkshake', 500, 'Raspberry milkshake.', 'glass'],
            ['Milkshakes', 'Peanut Mango Fusion Milkshake', 500, 'Peanut mango fusion milkshake.', 'glass'],
        ] as [$sub, $name, $price, $desc, $unit]) {
            $addMenuItem($sub === 'Milkshakes' ? $milkshakes : $lemonades, $sub, $name, $price, $desc, $unit);
        }

        foreach ([
            ['Ice Cream', 'Ice Cream 1 Scoop', 150, 'Assorted ice cream: vanilla, strawberry or chocolate.'],
            ['Ice Cream', 'Ice Cream 2 Scoops', 300, 'Two scoops of assorted ice cream.'],
            ['Bakery', 'Brownie', 180, 'Chocolate brownie.'],
            ['Bakery', 'Vanilla Muffin', 200, 'Vanilla muffin served with a scoop of ice cream.'],
            ['Bakery', 'Carrot Muffin', 200, 'Carrot muffin with cinnamon and sweet crumb topping.'],
            ['Bakery', 'Lemon Muffin', 200, 'Light lemon muffin with zesty flavour.'],
            ['Bakery', 'Box of Cookies', 250, 'Box of cookies.'],
            ['Fresh', 'Fruit Salad', 350, 'Fresh fruit salad.'],
            ['Dessert', 'Brownie Sundae', 350, 'Brownie topped with vanilla ice cream, chocolate sauce, whipped cream and strawberry.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($desserts, $sub, $name, $price, $desc);
        }

        foreach ([
            ['Mocktails', 'Royal Mile Special', 550, 'Cranberry and orange juice with lime and tonic.'],
            ['Mocktails', 'Raspberry Mojito', 550, 'Raspberry, fresh lime, fragrant mint and sugar syrup.'],
            ['Mocktails', 'Strawberry Limeade', 550, 'Fresh strawberries, sprite and tangy lime.'],
            ['Mocktails', 'Blueberry Splash', 550, 'Blueberries, lemon and fizzy sprite.'],
            ['Mocktails', 'Mixed Berry', 550, 'Strawberries, blueberries and raspberries with ginger.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($mocktails, $sub, $name, $price, $desc, 'glass');
        }

        foreach ([
            ['Wine', 'House Wine Dry Red Glass', 450, 'House dry red wine by the glass.'],
            ['Wine', 'House Wine Red Glass', 450, 'House red wine by the glass.'],
            ['Wine', 'House Wine White Sweet Glass', 450, 'House sweet white wine by the glass.'],
            ['Wine', 'House Wine Dry White Glass', 450, 'House dry white wine by the glass.'],
            ['Beers', 'Smirnoff Black Ice', 350, 'Smirnoff Black Ice.'],
            ['Beers', 'Tusker Cider', 350, 'Tusker cider.'],
            ['Beers', 'Tusker Lager', 400, 'Tusker lager.'],
            ['Beers', 'Tusker Malt', 400, 'Tusker malt.'],
            ['Beers', 'Whitecap Lager', 400, 'Whitecap lager.'],
            ['Beers', 'Heineken', 450, 'Heineken beer.'],
            ['Beers', 'Tusker Lite', 450, 'Tusker Lite beer.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($bar, $sub, $name, $price, $desc, 'bottle');
        }

        foreach ([
            ['Cocktails', 'Azure Dream', 800, 'Vodka, blue curacao and lemonade over ice.'],
            ['Cocktails', 'Apple Orchard Fizz', 800, 'Apple vodka, apple juice and sour mix.'],
            ['Cocktails', 'Classic Elegance', 900, 'Smooth gin or vodka with dry vermouth and lemon twist.'],
            ['Cocktails', 'Minted Bliss', 900, 'Fresh mint, lime and gin shaken to perfection.'],
            ['Cocktails', 'Cuban Refresh', 900, 'Classic Mojito with mint, lime, rum and soda water.'],
            ['Cocktails', 'Diplomat Delight', 950, 'Tequila, orange juice and sugar syrup.'],
            ['Cocktails', 'Homeward Bound', 950, 'Tequila, orange juice and paradise mix.'],
            ['Cocktails', 'Cosmopolitan', 950, 'Vodka, cranberry and lime juice cocktail.'],
            ['Cocktails', 'Sour Harmony', 980, 'Whiskey, lemon juice and sugar.'],
            ['Cocktails', 'Devils Margarita', 1000, 'Margarita with lime juice and sugar syrup.'],
        ] as [$sub, $name, $price, $desc]) {
            $addMenuItem($cocktails, $sub, $name, $price, $desc, 'glass');
        }

        foreach ([
            [$rice, 60],
            [$beef, 35],
            [$oil, 20],
            [$soda, 80],
            [$water, 100],
        ] as [$product, $quantity]) {
            StockLevel::query()->create([
                'product_id' => $product->id,
                'outlet_id' => $outlet->id,
                'quantity' => $quantity,
            ]);

            StockMovement::query()->create([
                'product_id' => $product->id,
                'outlet_id' => $outlet->id,
                'quantity' => $quantity,
                'movement_type' => 'ADJUSTMENT',
                'reference_type' => 'seed',
                'reference_id' => null,
                'before_stock' => 0,
                'after_stock' => $quantity,
                'unit_cost' => $product->cost_price,
                'total_cost' => $quantity * (float) $product->cost_price,
                'notes' => 'Opening balance',
                'created_by' => $admin->id,
            ]);
        }

        $recipe = Recipe::query()->create([
            'product_id' => $pilau->id,
            'yield_quantity' => 1,
            'yield_unit' => 'plate',
            'version' => 1,
            'status' => 'active',
        ]);

        $recipe->items()->createMany([
            [
                'ingredient_product_id' => $rice->id,
                'quantity_required' => 0.25,
                'unit' => 'kg',
                'wastage_percent' => 3,
                'cost_snapshot' => $rice->cost_price,
            ],
            [
                'ingredient_product_id' => $beef->id,
                'quantity_required' => 0.15,
                'unit' => 'kg',
                'wastage_percent' => 5,
                'cost_snapshot' => $beef->cost_price,
            ],
            [
                'ingredient_product_id' => $oil->id,
                'quantity_required' => 0.03,
                'unit' => 'litre',
                'wastage_percent' => 0,
                'cost_snapshot' => $oil->cost_price,
            ],
        ]);

        DB::table('unit_conversions')->insert([
            ['from_unit' => 'g', 'to_unit' => 'kg', 'factor' => 0.001, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'kg', 'to_unit' => 'g', 'factor' => 1000, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'ml', 'to_unit' => 'litre', 'factor' => 0.001, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'litre', 'to_unit' => 'ml', 'factor' => 1000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach ([
            ['T01', 2],
            ['T02', 4],
            ['T03', 4],
            ['VIP 1', 6],
            ['Patio 1', 4],
            ['Takeaway Counter', 1],
        ] as [$name, $capacity]) {
            RestaurantTable::query()->create(compact('name', 'capacity'));
        }

        Supplier::query()->create([
            'name' => 'Nairobi Fresh Foods',
            'phone' => '+254700000001',
            'email' => 'orders@nff.test',
        ]);

        Customer::query()->create([
            'name' => 'Walk-in Credit Customer',
            'phone' => '+254700000002',
            'credit_limit' => 10000,
        ]);
    }
}
